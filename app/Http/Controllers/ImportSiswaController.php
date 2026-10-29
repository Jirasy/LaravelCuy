<?php

namespace App\Http\Controllers;

use App\Exports\AkunSiswaBaruExport;
use App\Models\KredensialSementara;
use App\Models\User;
use App\Notifications\RekapImportSiswa;
use App\Services\DaftarSiswaPembaca;
use App\Services\ImportSiswaService;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

// Hanya admin & pembina (dijaga di routes/web.php). Rekap kredensial hanya bisa dibuka oleh pengunggahnya sendiri.
class ImportSiswaController extends Controller
{
    public function create()
    {
        return view('import_siswa.create', [
            'ekstensi' => DaftarSiswaPembaca::ekstensiDidukung(),
            'maks' => DaftarSiswaPembaca::MAKS_BARIS,
            'domainDefault' => ImportSiswaService::DOMAIN_EMAIL_DEFAULT,
        ]);
    }

    public function proses(Request $request, DaftarSiswaPembaca $pembaca, ImportSiswaService $layanan)
    {
        $request->validate([
            'berkas' => ['required', 'file', 'max:5120', 'extensions:'.implode(',', DaftarSiswaPembaca::ekstensiDidukung())],
            'domain_email' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9]([a-z0-9\-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9\-]*[a-z0-9])?)+$/i'],
        ], [
            'berkas.required' => 'Pilih berkas daftar siswa dulu.',
            'berkas.max' => 'Ukuran berkas maksimal 5 MB.',
            'berkas.extensions' => 'Format berkas harus salah satu dari: '.implode(', ', DaftarSiswaPembaca::ekstensiDidukung()).'.',
            'domain_email.regex' => 'Domain email tidak valid, contoh: siswa.sekolahku.sch.id',
        ]);

        // Hash password (bcrypt) per akun butuh waktu; 500 siswa bisa melewati batas waktu bawaan PHP
        set_time_limit(300);

        $berkas = $request->file('berkas');

        try {
            $terbaca = $pembaca->baca($berkas);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            throw ValidationException::withMessages([
                'berkas' => 'Berkas tidak bisa dibaca. Pastikan formatnya benar dan tidak dikunci password.',
            ]);
        }

        if ($terbaca['siswa'] === []) {
            throw ValidationException::withMessages([
                'berkas' => 'Tidak ada nama siswa yang terbaca. Pastikan ada kolom "Nama" atau satu nama per baris.',
            ]);
        }

        $hasil = $layanan->impor(
            $terbaca['siswa'],
            $request->user(),
            $berkas->getClientOriginalName(),
            $terbaca['diabaikan'],
            strtolower($request->input('domain_email') ?: ImportSiswaService::DOMAIN_EMAIL_DEFAULT),
            $request->boolean('izinkan_nama_sama'),
        );

        $jumlah = count($hasil['dibuat']);
        $dilewati = count($hasil['dilewati']);

        if ($jumlah === 0) {
            return back()
                ->with('error', 'Tidak ada akun baru yang dibuat: semua nama di berkas sudah terdaftar atau kembar.')
                ->with('dilewati', $hasil['dilewati']);
        }

        return redirect()
            ->route('import-siswa.hasil', $hasil['rekap_id'])
            ->with('success', "Berhasil mengimpor {$jumlah} siswa baru."
                .($dilewati > 0 ? " {$dilewati} nama dilewati." : ''));
    }

    public function hasil(Request $request, string $rekap)
    {
        $notif = $this->rekapMilik($request, $rekap);
        $idSiswa = $notif->data['id_siswa'] ?? [];

        $siswa = User::whereIn('id', $idSiswa)->orderBy('name')->get();
        $kredensial = KredensialSementara::whereIn('user_id', $idSiswa)->get()->keyBy('user_id');

        // Password baru didekripsi setelah tombol "Tampilkan password" ditekan (flash, sekali tampil)
        $password = [];
        if (session('rekap_dibuka') === $rekap) {
            foreach ($kredensial as $idUser => $k) {
                if (! $k->sudahKedaluwarsa()) {
                    $password[$idUser] = $k->passwordAsli();
                }
            }
        }

        return view('import_siswa.hasil', [
            'rekap' => $rekap,
            'data' => $notif->data,
            'siswa' => $siswa,
            'kredensial' => $kredensial,
            'password' => $password,
            'dibuka' => session('rekap_dibuka') === $rekap,
        ]);
    }

    public function tampilkan(Request $request, string $rekap)
    {
        $notif = $this->rekapMilik($request, $rekap);
        $this->catatDilihat($request, $notif);

        return redirect()->route('import-siswa.hasil', $rekap)->with('rekap_dibuka', $rekap);
    }

    // File .xlsx (bukan CSV): CSV berpemisah koma terbuka berantakan di Excel berpengaturan regional Indonesia (pemisahnya titik koma)
    public function unduh(Request $request, string $rekap): BinaryFileResponse
    {
        $notif = $this->rekapMilik($request, $rekap);
        $idSiswa = $notif->data['id_siswa'] ?? [];

        $this->catatDilihat($request, $notif);

        $siswa = User::whereIn('id', $idSiswa)->orderBy('name')->get();
        $kredensial = KredensialSementara::whereIn('user_id', $idSiswa)->get()->keyBy('user_id');

        $baris = $siswa->map(function (User $s) use ($kredensial): array {
            $k = $kredensial->get($s->id);

            // Wording sama dengan halaman hasil supaya kolom password tidak pernah kosong tanpa penjelasan
            $password = match (true) {
                $k === null => 'Sudah diganti siswa',
                $k->sudahKedaluwarsa() => 'Kedaluwarsa, reset lewat Kelola User',
                default => $k->passwordAsli() ?? '',
            };

            return [$s->name, $s->kelas ?? '', $s->email, $password];
        })->all();

        return Excel::download(
            new AkunSiswaBaruExport($baris),
            'akun-siswa-baru-'.now()->format('Ymd-His').'.xlsx',
        );
    }

    // Hanya notifikasi milik user yang login, jadi admin/pembina lain tidak bisa membuka rekap orang lain (404)
    private function rekapMilik(Request $request, string $rekap): DatabaseNotification
    {
        return $request->user()->notifications()
            ->where('id', $rekap)
            ->where('type', RekapImportSiswa::class)
            ->firstOrFail();
    }

    private function catatDilihat(Request $request, DatabaseNotification $notif): void
    {
        KredensialSementara::whereIn('user_id', $notif->data['id_siswa'] ?? [])->update([
            'terakhir_dilihat_pada' => now(),
            'terakhir_dilihat_oleh' => $request->user()->id,
        ]);
    }
}
