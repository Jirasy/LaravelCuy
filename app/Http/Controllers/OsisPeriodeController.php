<?php

namespace App\Http\Controllers;

use App\Models\CasisPendaftaran;
use App\Models\Ekstrakurikuler;
use App\Models\OsisPeriode;
use App\Models\PendaftaranEkskul;
use App\Models\SeleksiTahap;
use App\Models\User;
use App\Services\OsisService;
use App\Support\Pemberitahuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

// Mengatur periode rekrutmen sebuah ekskul berseleksi, senior penilai, dan tahap seleksinya.
// Periode & senior: admin & pembina ekskul itu. Tahap (jumlah bebas, waktu, tempat): admin, pembina, atau senior penilai.
class OsisPeriodeController extends Controller
{
    public function store(Request $request, Ekstrakurikuler $ekskul, OsisService $osis)
    {
        abort_unless($ekskul->memakaiCasis(), 404, 'Ekskul ini tidak memakai seleksi. Ubah tipenya ke Ekskul Seleksi atau Organisasi lewat Edit Ekskul.');
        Gate::authorize('kelola', $ekskul);

        $data = $request->validate([
            'nama'       => 'required|string|max:20',
            'buka_pada'  => 'nullable|date',
            'tutup_pada' => 'nullable|date|after_or_equal:buka_pada',
        ], ['nama.max' => 'Nama periode maksimal 20 karakter, mis. 2026/2027.']);

        if (OsisPeriode::where('ekstrakurikuler_id', $ekskul->id)->where('nama', $data['nama'])->exists()) {
            return back()->withInput()->with('error', 'Periode dengan nama itu sudah ada.');
        }

        if (OsisPeriode::where('ekstrakurikuler_id', $ekskul->id)->where('status', 'rekrutmen')->exists()) {
            return back()->withInput()->with('error', 'Masih ada periode yang dalam tahap pendaftaran. Selesaikan tahapnya dulu sebelum membuat periode baru.');
        }

        $periode = OsisPeriode::create($data + ['ekstrakurikuler_id' => $ekskul->id, 'status' => 'rekrutmen']);

        // Periode baru langsung punya tahap 1; tahap lain ditambah pengelola/senior sesuai kebutuhan
        $osis->tahapPeriode($periode);

        return redirect()->route('casis.index', [$ekskul->id, 'periode' => $periode->id])
            ->with('success', "Periode {$periode->nama} dibuat. Pendaftaran {$ekskul->label_calon} dibuka.");
    }

    // Maju satu tahap: rekrutmen -> seleksi -> berjalan -> selesai
    public function status(Request $request, OsisPeriode $periode)
    {
        Gate::authorize('kelola', $periode->ekstrakurikuler);

        $data = $request->validate(['aksi' => ['required', Rule::in(['seleksi', 'berjalan', 'selesai'])]]);

        $urutan = ['rekrutmen' => 0, 'seleksi' => 1, 'berjalan' => 2, 'selesai' => 3];
        $tujuan = $data['aksi'];

        if ($urutan[$tujuan] !== $urutan[$periode->status] + 1) {
            return back()->with('error', 'Tahap harus berurutan: pendaftaran, seleksi, berjalan, lalu selesai.');
        }

        if ($tujuan === 'berjalan') {
            $belum = $periode->casis()->where('status', 'diajukan')->count();

            if ($belum > 0) {
                return back()->with('error', "Masih ada {$belum} pendaftar yang belum diputuskan.");
            }
        }

        $periode->update(['status' => $tujuan]);

        if ($tujuan === 'seleksi') {
            $jumlah  = $periode->casis()->where('status', 'diajukan')->count();
            $penilai = Pemberitahuan::pengelola($periode->ekstrakurikuler)->merge($periode->seniors);

            $ekskul = $periode->ekstrakurikuler;

            Pemberitahuan::kirim(
                $penilai,
                "Seleksi {$ekskul->nama_ekstrakurikuler} dimulai",
                "Seleksi {$ekskul->label_calon} {$periode->nama} dimulai: {$jumlah} pendaftar menunggu penilaian.",
                route('casis.index', [$ekskul->id, 'periode' => $periode->id], false),
                '🗳️',
                $request->user()->id
            );
        }

        return back()->with('success', 'Periode ' . $periode->nama . ' sekarang: ' . OsisPeriode::STATUS[$tujuan] . '.');
    }

    // Menunjuk senior (pengurus sebelumnya) sebagai penilai
    public function seniorTambah(Request $request, OsisPeriode $periode)
    {
        Gate::authorize('kelola', $periode->ekstrakurikuler);

        if ($periode->status === 'selesai') {
            return back()->with('error', 'Periode ini sudah selesai.');
        }

        $data = $request->validate(['user_id' => 'required|integer|exists:users,id'], ['user_id.required' => 'Pilih seniornya.']);
        $user = User::findOrFail($data['user_id']);

        $pengurusAktif = PendaftaranEkskul::anggotaAktif()
            ->where('ekstrakurikuler_id', $periode->ekstrakurikuler_id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$pengurusAktif) {
            return back()->with('error', "Senior harus pengurus {$periode->ekstrakurikuler->nama_ekstrakurikuler} yang masih aktif.");
        }

        if (CasisPendaftaran::where('osis_periode_id', $periode->id)->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Pendaftar seleksi tidak boleh menjadi penilai di periode yang sama.');
        }

        if ($periode->seniors()->where('users.id', $user->id)->exists()) {
            return back()->with('error', "{$user->name} sudah menjadi penilai.");
        }

        $periode->seniors()->attach($user->id);

        Pemberitahuan::kirim(
            [$user],
            'Ditunjuk sebagai penilai seleksi',
            "Kamu ditunjuk sebagai senior penilai seleksi {$periode->ekstrakurikuler->nama_ekstrakurikuler} periode {$periode->nama}.",
            route('casis.index', [$periode->ekstrakurikuler_id, 'periode' => $periode->id], false),
            '🗳️',
            $request->user()->id
        );

        return back()->with('success', "{$user->name} ditunjuk sebagai penilai.");
    }

    public function seniorHapus(OsisPeriode $periode, User $user)
    {
        Gate::authorize('kelola', $periode->ekstrakurikuler);

        $periode->seniors()->detach($user->id);

        return back()->with('success', "{$user->name} tidak lagi menjadi penilai.");
    }

    // ================= Tahap seleksi =================

    private function aturanTahap(): array
    {
        return [
            'nama'         => 'required|string|max:60',
            'mulai_pada'   => 'nullable|date',
            'selesai_pada' => 'nullable|date|required_with:mulai_pada|after_or_equal:mulai_pada',
            'tempat'       => 'nullable|string|max:120',
            'keterangan'   => 'nullable|string|max:1000',
        ];
    }

    private function pesanTahap(): array
    {
        return [
            'nama.required'               => 'Isi nama tahap, mis. Tes tulis atau Wawancara.',
            'nama.max'                    => 'Nama tahap maksimal 60 karakter.',
            'mulai_pada.date'             => 'Waktu mulai tidak valid.',
            'selesai_pada.date'           => 'Waktu selesai tidak valid.',
            'selesai_pada.required_with'  => 'Isi juga waktu selesai kalau waktu mulai diisi.',
            'selesai_pada.after_or_equal' => 'Waktu selesai tidak boleh lebih awal dari waktu mulai.',
            'tempat.max'                  => 'Tempat maksimal 120 karakter.',
            'keterangan.max'              => 'Keterangan maksimal 1000 karakter.',
        ];
    }

    // Admin, pembina ekskul, atau senior penilai periode ini. Tahap hanya bisa diubah sebelum kepengurusan berjalan.
    private function periksaAturTahap(Request $request, OsisPeriode $periode, OsisService $osis): ?string
    {
        abort_unless($osis->tipeReviewer($request->user(), $periode), 403, 'Hanya admin, pembina, atau senior penilai yang boleh mengatur tahap seleksi.');

        return in_array($periode->status, ['rekrutmen', 'seleksi'], true)
            ? null
            : 'Tahap seleksi hanya bisa diatur sebelum kepengurusan berjalan.';
    }

    // Pendaftar yang masih dalam proses diberi tahu tahap baru / perubahan jadwal
    private function beritahuPendaftar(OsisPeriode $periode, string $judul, string $pesan, int $pelakuId): void
    {
        $penerima = $periode->casis()->where('status', 'diajukan')->with('user')->get()->pluck('user');

        Pemberitahuan::kirim(
            $penerima,
            $judul,
            $pesan,
            route('casis.create', [$periode->ekstrakurikuler_id], false),
            '📅',
            $pelakuId
        );
    }

    public function tahapStore(Request $request, OsisPeriode $periode, OsisService $osis)
    {
        if ($galat = $this->periksaAturTahap($request, $periode, $osis)) {
            return back()->with('error', $galat);
        }

        // Lolos di tahap terakhir = diterima, jadi tahap baru tidak boleh menyusul setelah ada yang diterima
        if ($periode->casis()->where('status', 'diterima')->exists()) {
            return back()->withInput()->with('error', 'Sudah ada pendaftar yang diterima di periode ini, jadi tahap baru tidak bisa ditambahkan lagi.');
        }

        $data   = $request->validate($this->aturanTahap(), $this->pesanTahap());
        $urutan = (int) $osis->tahapPeriode($periode)->max('urutan') + 1;

        $tahap = SeleksiTahap::create($data + [
            'osis_periode_id' => $periode->id,
            'urutan'          => $urutan,
            'dibuat_oleh'     => $request->user()->id,
        ]);

        $this->beritahuPendaftar($periode, 'Ada tahap seleksi baru', "Tahap {$tahap->urutan}: {$tahap->ringkasan}.", $request->user()->id);

        return back()->with('success', "Tahap {$tahap->urutan} ({$tahap->nama}) ditambahkan.");
    }

    public function tahapUpdate(Request $request, SeleksiTahap $tahap, OsisService $osis)
    {
        $periode = $tahap->periode;

        if ($galat = $this->periksaAturTahap($request, $periode, $osis)) {
            return back()->with('error', $galat);
        }

        $data = $request->validate($this->aturanTahap(), $this->pesanTahap());

        $tahap->fill($data);
        $jadwalBerubah = $tahap->isDirty(['mulai_pada', 'selesai_pada', 'tempat']);
        $tahap->save();

        if ($jadwalBerubah) {
            $this->beritahuPendaftar($periode, 'Jadwal seleksi diperbarui', "Tahap {$tahap->urutan}: {$tahap->ringkasan}.", $request->user()->id);
        }

        return back()->with('success', "Tahap {$tahap->urutan} ({$tahap->nama}) diperbarui.");
    }

    public function tahapHapus(Request $request, SeleksiTahap $tahap, OsisService $osis)
    {
        $periode = $tahap->periode;

        if ($galat = $this->periksaAturTahap($request, $periode, $osis)) {
            return back()->with('error', $galat);
        }

        if ($periode->tahaps()->count() <= 1) {
            return back()->with('error', 'Periode harus punya minimal satu tahap seleksi.');
        }

        if ($tahap->hasils()->exists() || $tahap->reviews()->exists()) {
            return back()->with('error', "Tahap {$tahap->nama} sudah punya penilaian atau hasil, jadi tidak bisa dihapus.");
        }

        DB::transaction(function () use ($tahap, $periode) {
            $urutan = $tahap->urutan;
            $tahap->delete();

            // Rapatkan nomor urut supaya tetap 1, 2, 3, ... (naik satu per satu menghindari bentrok indeks unik)
            foreach ($periode->tahaps()->where('urutan', '>', $urutan)->get() as $t) {
                $t->update(['urutan' => $t->urutan - 1]);
            }
        });

        return back()->with('success', "Tahap {$tahap->nama} dihapus.");
    }
}
