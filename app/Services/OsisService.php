<?php

namespace App\Services;

use App\Models\CasisPendaftaran;
use App\Models\Ekstrakurikuler;
use App\Models\OsisPeriode;
use App\Models\PendaftaranEkskul;
use App\Models\SeleksiHasil;
use App\Models\SeleksiTahap;
use App\Models\User;
use App\Support\Pemberitahuan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// Aturan alur seleksi (CaSis/calon anggota) dikumpulkan di sini supaya konsisten.
// Alur ini berlaku untuk SEMUA ekskul bertipe Ekskul Seleksi atau Organisasi; tiap ekskul punya periode, tahap, dan penilainya sendiri.
// Nama "OsisService" dipertahankan dari versi OSIS-saja.
class OsisService
{
    // Status kepengurusan yang boleh dicatat manual
    public const STATUS_PENGURUS = ['aktif' => 'Masih menjabat', 'selesai' => 'Purna tugas'];

    private ?Ekstrakurikuler $osisCache = null;
    private bool $osisDimuat = false;

    // "Organisasi utama" = organisasi pertama (id terkecil). Dipakai alat Kelola User untuk OSIS
    // (masukkan ke jalur CaSis, catat angkatan susulan); seleksi tiap ekskul lain dikelola dari halaman seleksi ekskulnya.
    public function osis(): ?Ekstrakurikuler
    {
        if (!$this->osisDimuat) {
            $this->osisCache = Ekstrakurikuler::organisasi()->orderBy('id')->first();
            $this->osisDimuat = true;
        }

        return $this->osisCache;
    }

    // Semua ekskul berseleksi (organisasi dulu, lalu ekskul seleksi), untuk pilihan di Kelola User
    public function daftarJalur(): Collection
    {
        return Ekstrakurikuler::alurCasis()->with('jenisEkskul')->get()
            ->sortBy(fn ($e) => [$e->isOrganisasi() ? 0 : 1, mb_strtolower($e->nama_ekstrakurikuler)])
            ->values();
    }

    // Jalur yang dituju alat Kelola User: pilihan ($id) kalau valid, selain itu organisasi utama, lalu ekskul berseleksi pertama
    public function jalurTerpilih(?int $id = null): ?Ekstrakurikuler
    {
        $daftar = $this->daftarJalur();

        return ($id ? $daftar->firstWhere('id', $id) : null) ?? $this->osis() ?? $daftar->first();
    }

    // Periode yang sedang menerima pendaftaran di ekskul ini (default: organisasi utama)
    public function periodeBuka(?Ekstrakurikuler $ekskul = null): ?OsisPeriode
    {
        $ekskul ??= $this->osis();

        return $ekskul ? OsisPeriode::terbuka()->where('ekstrakurikuler_id', $ekskul->id)->latest()->first() : null;
    }

    // Pembina salah satu organisasi di atas ekskul ini (mis. pembina MPK terhadap OSIS yang berada di bawah MPK)
    public function pembinaInduk(User $user, Ekstrakurikuler $ekskul): bool
    {
        if (! $user->isPembina()) {
            return false;
        }

        $leluhur = $ekskul->idLeluhur();

        return $leluhur !== [] && array_intersect($leluhur, $user->idEkskulDikelola()) !== [];
    }

    // Admin, pembina ekskul itu, atau pembina organisasi induknya: boleh mengatur periode, tahap, senior, memutuskan hasil,
    // dan memberi status ke siswa di ekskul ini
    public function bolehMemutuskan(User $user, ?Ekstrakurikuler $ekskul = null): bool
    {
        $ekskul ??= $this->osis();

        return $ekskul && ($user->can('kelola', $ekskul) || $this->pembinaInduk($user, $ekskul));
    }

    // Id ekskul tempat calon penilai (senior) boleh berasal: ekskul itu sendiri dan organisasi di atasnya
    public function idAsalPenilai(Ekstrakurikuler $ekskul): array
    {
        return array_merge([(int) $ekskul->id], $ekskul->idLeluhur());
    }

    // Penerima kabar seleksi ekskul ini: admin, pembina ekskul, dan pembina organisasi induknya
    public function pengelolaSeleksi(Ekstrakurikuler $ekskul): Collection
    {
        $induk = Ekstrakurikuler::whereIn('id', $ekskul->idLeluhur())->get()
            ->flatMap(fn ($e) => $e->pembinaAktif()->where('users.is_active', true)->get());

        return Pemberitahuan::pengelola($ekskul)->merge($induk)->unique('id')->values();
    }

    // Unsur penilai untuk user ini pada periode tersebut: admin | pembina | senior | null
    public function tipeReviewer(User $user, OsisPeriode $periode): ?string
    {
        if ($user->isAdmin()) {
            return 'admin';
        }

        if ($user->isPembina() && in_array((int) $periode->ekstrakurikuler_id, $user->idEkskulDikelola(), true)) {
            return 'pembina';
        }

        // Pembina organisasi induk (mis. MPK) ikut menilai seleksi organisasi di bawahnya (mis. OSIS)
        if ($periode->ekstrakurikuler && $this->pembinaInduk($user, $periode->ekstrakurikuler)) {
            return 'pembina';
        }

        return $periode->seniors()->where('users.id', $user->id)->exists() ? 'senior' : null;
    }

    // Anggota aktif ekskul ini saat ini (pengurus/anggota); default organisasi utama
    public function pengurusAktif(User $user, ?Ekstrakurikuler $ekskul = null): bool
    {
        $ekskul ??= $this->osis();

        return $ekskul && PendaftaranEkskul::anggotaAktif()
            ->where('ekstrakurikuler_id', $ekskul->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    // Tahap periode ini, berurutan. Periode yang belum punya tahap otomatis diberi tahap 1 ("Seleksi").
    public function tahapPeriode(OsisPeriode $periode): Collection
    {
        return $periode->tahapSemua();
    }

    // Unsur apa saja yang WAJIB sudah menilai di tahap ini sebelum hasil tahap diputuskan, dan apakah sudah terpenuhi.
    // Admin selalu wajib; pembina wajib kalau ada; senior wajib kalau periode punya senior.
    public function syarat(CasisPendaftaran $casis, ?SeleksiTahap $tahap = null): array
    {
        $periode = $casis->periode;
        $tahap ??= $casis->tahapSekarang();
        $sudah   = $casis->reviews
            ->when($tahap, fn ($r) => $r->where('seleksi_tahap_id', $tahap->id))
            ->pluck('tipe')->unique()->all();

        $perlu = ['admin'];
        if ($periode->ekstrakurikuler->pembinaAktif()->exists()) {
            $perlu[] = 'pembina';
        }
        if ($periode->seniors()->exists()) {
            $perlu[] = 'senior';
        }

        return collect($perlu)->mapWithKeys(fn ($tipe) => [$tipe => in_array($tipe, $sudah, true)])->all();
    }

    // Menetapkan hasil seorang pendaftar di satu tahap. Tidak lolos = ditolak; lolos di tahap terakhir = diterima
    // dan langsung tercatat sebagai anggota/pengurus; lolos di tahap lain = lanjut ke tahap berikutnya.
    // Mengembalikan ['status' => diajukan|diterima|ditolak, 'berikut' => SeleksiTahap|null]
    public function tetapkanHasil(CasisPendaftaran $casis, SeleksiTahap $tahap, string $hasil, ?string $catatan, User $pemutus): array
    {
        $periode = $casis->periode;
        $tahaps  = $this->tahapPeriode($periode);
        $terakhir = $tahaps->last();
        $berikut  = $tahaps->first(fn ($t) => $t->urutan > $tahap->urutan);

        return DB::transaction(function () use ($casis, $tahap, $hasil, $catatan, $pemutus, $periode, $terakhir, $berikut) {
            SeleksiHasil::updateOrCreate(
                ['casis_pendaftaran_id' => $casis->id, 'seleksi_tahap_id' => $tahap->id],
                ['hasil' => $hasil, 'catatan' => $catatan, 'diputuskan_oleh' => $pemutus->id]
            );

            if ($hasil === 'tidak_lolos') {
                $casis->update([
                    'status'            => 'ditolak',
                    'diputuskan_oleh'   => $pemutus->id,
                    'catatan_keputusan' => $catatan,
                    'diputuskan_pada'   => now(),
                ]);

                return ['status' => 'ditolak', 'berikut' => null];
            }

            if ($tahap->id !== $terakhir->id) {
                return ['status' => 'diajukan', 'berikut' => $berikut];
            }

            $casis->update([
                'status'            => 'diterima',
                'diputuskan_oleh'   => $pemutus->id,
                'catatan_keputusan' => $catatan,
                'diputuskan_pada'   => now(),
            ]);

            if (! $this->pengurusAktif($casis->user, $periode->ekstrakurikuler)) {
                $this->jadikanAnggota($periode->ekstrakurikuler, $casis->user, "Lolos seleksi {$periode->nama}", $periode->nama);
            }

            return ['status' => 'diterima', 'berikut' => null];
        });
    }

    // Cek aturan sebelum mencatat/mengoreksi kepengurusan secara manual. Mengembalikan pesan galat, atau null kalau boleh.
    // $kecualiId = baris yang sedang dikoreksi (tidak dihitung sebagai duplikat dirinya sendiri).
    public function periksaSusulan(User $siswa, string $angkatan, string $status, ?int $kecualiId = null, ?Ekstrakurikuler $ekskul = null): ?string
    {
        $ekskul ??= $this->osis();

        $baris = PendaftaranEkskul::where('ekstrakurikuler_id', $ekskul->id)
            ->where('user_id', $siswa->id)
            ->where('status', 'diterima')
            ->whereIn('status_anggota', array_keys(self::STATUS_PENGURUS))
            ->when($kecualiId, fn ($q) => $q->where('id', '!=', $kecualiId))
            ->get();

        if ($baris->contains(fn ($b) => $b->angkatan_ekskul === $angkatan)) {
            return "{$siswa->name} sudah tercatat di angkatan {$angkatan}. Gunakan Koreksi untuk mengubahnya.";
        }

        if ($status === 'aktif' && $baris->contains(fn ($b) => $b->status_anggota === 'aktif')) {
            return "{$siswa->name} sudah tercatat sebagai {$ekskul->sebutan_anggota} {$ekskul->nama_ekstrakurikuler} yang masih menjabat. Ubah dulu statusnya lewat Koreksi.";
        }

        return null;
    }

    // Menjadi anggota (pengurus) sebuah ekskul. Dari seleksi (status aktif, angkatan = nama periode) maupun
    // dicatat manual untuk angkatan lain / yang sudah purna tugas ($status = 'selesai').
    public function jadikanAnggota(
        Ekstrakurikuler $ekskul,
        User $user,
        string $alasan,
        ?string $angkatan = null,
        ?int $tahunMulai = null,
        ?int $tahunSelesai = null,
        string $status = 'aktif',
        ?int $susulanOleh = null,
    ): PendaftaranEkskul {
        return PendaftaranEkskul::create([
            'user_id'            => $user->id,
            'ekstrakurikuler_id' => $ekskul->id,
            'nama_pendaftar'     => $user->name,
            'peran'              => 'siswa',
            'kelas'              => $user->kelas,
            'nisn'               => $user->nisn,
            'alasan_mendaftar'   => $alasan,
            'status'             => 'diterima',
            'status_anggota'     => $status,
            'tahun_mulai'        => $tahunMulai ?? now()->year,
            'tahun_selesai'      => $tahunSelesai,
            'angkatan_ekskul'    => $angkatan,
            'dibuat_susulan_oleh' => $susulanOleh,
        ]);
    }

    // Versi lama untuk organisasi utama (alat susulan di Kelola User)
    public function jadikanPengurus(
        User $user,
        string $alasan,
        ?string $angkatan = null,
        ?int $tahunMulai = null,
        ?int $tahunSelesai = null,
        string $status = 'aktif',
        ?int $susulanOleh = null,
    ): PendaftaranEkskul {
        return $this->jadikanAnggota($this->osis(), $user, $alasan, $angkatan, $tahunMulai, $tahunSelesai, $status, $susulanOleh);
    }
}
