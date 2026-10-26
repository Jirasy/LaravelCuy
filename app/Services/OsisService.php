<?php

namespace App\Services;

use App\Models\CasisPendaftaran;
use App\Models\Ekstrakurikuler;
use App\Models\OsisPeriode;
use App\Models\PendaftaranEkskul;
use App\Models\User;

// Aturan alur OSIS & CaSis dikumpulkan di sini supaya konsisten.
class OsisService
{
    private ?Ekstrakurikuler $osisCache = null;
    private bool $osisDimuat = false;

    // Satu-satunya ekskul bertipe OSIS (null kalau belum ditandai)
    public function osis(): ?Ekstrakurikuler
    {
        if (!$this->osisDimuat) {
            $this->osisCache = Ekstrakurikuler::where('jenis', 'osis')->first();
            $this->osisDimuat = true;
        }

        return $this->osisCache;
    }

    // Periode yang sedang menerima pendaftaran CaSis
    public function periodeBuka(): ?OsisPeriode
    {
        $osis = $this->osis();

        return $osis ? OsisPeriode::terbuka()->where('ekstrakurikuler_id', $osis->id)->latest()->first() : null;
    }

    // Admin atau pembina OSIS: boleh mengatur periode, memutuskan, dan memberi status OSIS ke siswa
    public function bolehMemutuskan(User $user): bool
    {
        $osis = $this->osis();

        return $osis && $user->can('kelola', $osis);
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

        return $periode->seniors()->where('users.id', $user->id)->exists() ? 'senior' : null;
    }

    // Anggota aktif OSIS saat ini (pengurus)
    public function pengurusAktif(User $user): bool
    {
        $osis = $this->osis();

        return $osis && PendaftaranEkskul::anggotaAktif()
            ->where('ekstrakurikuler_id', $osis->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    // Unsur apa saja yang WAJIB sudah menilai sebelum keputusan, dan apakah sudah terpenuhi
    // Admin selalu wajib; pembina OSIS wajib kalau ada; senior wajib kalau periode punya senior.
    public function syarat(CasisPendaftaran $casis): array
    {
        $periode = $casis->periode;
        $sudah   = $casis->reviews->pluck('tipe')->unique()->all();

        $perlu = ['admin'];
        if ($periode->ekstrakurikuler->pembinaAktif()->exists()) {
            $perlu[] = 'pembina';
        }
        if ($periode->seniors()->exists()) {
            $perlu[] = 'senior';
        }

        return collect($perlu)->mapWithKeys(fn ($tipe) => [$tipe => in_array($tipe, $sudah, true)])->all();
    }

    // Diterima -> otomatis menjadi anggota (pengurus) ekskul OSIS
    public function jadikanPengurus(User $user, string $alasan, ?string $angkatan = null): PendaftaranEkskul
    {
        $osis = $this->osis();

        return PendaftaranEkskul::create([
            'user_id'            => $user->id,
            'ekstrakurikuler_id' => $osis->id,
            'nama_pendaftar'     => $user->name,
            'peran'              => 'siswa',
            'kelas'              => $user->kelas,
            'nisn'               => $user->nisn,
            'alasan_mendaftar'   => $alasan,
            'status'             => 'diterima',
            'status_anggota'     => 'aktif',
            'tahun_mulai'        => now()->year,
            'angkatan_ekskul'    => $angkatan,
        ]);
    }
}