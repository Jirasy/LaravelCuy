<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use App\Models\User;
use App\Services\ImportSiswaService;
use App\Services\PembinaAkunService;
use Illuminate\Http\Request;

// Tambah user satu per satu dari Kelola User (pelengkap import massal).
// Siswa: admin & pembina. Pembina: hanya admin (akun pembina punya akses luas dan kredensialnya dibuka lewat halaman Kredensial).
// Password selalu acak; tidak pernah ditulis polos ke tabel notifikasi.
class UserTambahController extends Controller
{
    public function create(Request $request)
    {
        $bolehPembina = $request->user()->isAdmin();
        $tab = $request->query('tab') === 'pembina' && $bolehPembina ? 'pembina' : 'siswa';

        return view('users.create', [
            'tab' => $tab,
            'bolehPembina' => $bolehPembina,
            'ekskuls' => Ekstrakurikuler::orderBy('nama_ekstrakurikuler')->get(['id', 'nama_ekstrakurikuler']),
            'daftarKelas' => User::whereNotNull('kelas')->distinct()->orderBy('kelas')->pluck('kelas'),
            'domainDefault' => ImportSiswaService::DOMAIN_EMAIL_DEFAULT,
        ]);
    }

    // Satu siswa baru. Memakai jalur yang sama dengan import massal (cek NISN/email/nama kembar, password acak,
    // password terenkripsi di kredensial_sementara, rekap di Riwayat Pesan), jadi hasilnya dibuka di halaman rekap.
    public function storeSiswa(Request $request, ImportSiswaService $layanan)
    {
        $request->merge([
            'nama'  => trim((string) preg_replace('/\s+/u', ' ', (string) $request->input('nama'))),
            'kelas' => trim((string) preg_replace('/\s+/u', ' ', mb_strtoupper((string) $request->input('kelas')))),
            'nisn'  => preg_replace('/\s+/', '', (string) $request->input('nisn')),
            'email' => mb_strtolower(trim((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'nama'         => ['required', 'string', 'max:255'],
            'kelas'        => ['nullable', 'string', 'max:50'],
            'nisn'         => ['nullable', 'regex:/^\d{5,20}$/'],
            'email'        => ['nullable', 'email', 'max:255'],
            'domain_email' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9]([a-z0-9\-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9\-]*[a-z0-9])?)+$/i'],
        ], [
            'nama.required'      => 'Isi nama siswa.',
            'nisn.regex'         => 'NISN hanya angka (5-20 digit). Kosongkan kalau belum ada.',
            'email.email'        => 'Format email tidak valid.',
            'domain_email.regex' => 'Domain email tidak valid, contoh: siswa.sekolahku.sch.id',
        ]);

        $hasil = $layanan->impor(
            [[
                'nama'  => $data['nama'],
                'nisn'  => ($data['nisn'] ?? '') !== '' ? $data['nisn'] : null,
                'kelas' => ($data['kelas'] ?? '') !== '' ? $data['kelas'] : null,
                'email' => ($data['email'] ?? '') !== '' ? $data['email'] : null,
            ]],
            $request->user(),
            'Input manual',
            0,
            strtolower($data['domain_email'] ?? '') ?: ImportSiswaService::DOMAIN_EMAIL_DEFAULT,
            $request->boolean('izinkan_nama_sama'),
        );

        if ($hasil['dibuat'] === []) {
            $alasan = $hasil['dilewati'][0]['alasan'] ?? 'data tidak valid';

            return back()->withInput()->with('error', "Akun siswa tidak dibuat: {$alasan}.");
        }

        return redirect()->route('import-siswa.hasil', $hasil['rekap_id'])
            ->with('success', "Akun siswa {$hasil['dibuat'][0]->name} dibuat. Password sementara bisa dibuka lewat tombol di bawah.");
    }

    // Satu pembina baru (khusus admin: rutenya ada di grup admin). Ekskul boleh dikosongkan dan ditugaskan belakangan.
    public function storePembina(Request $request, PembinaAkunService $layanan)
    {
        $request->merge([
            'nama'          => trim((string) preg_replace('/\s+/u', ' ', (string) $request->input('nama'))),
            'email_pembina' => mb_strtolower(trim((string) $request->input('email_pembina'))),
        ]);

        $data = $request->validate([
            'nama'               => ['required', 'string', 'max:255'],
            'email_pembina'      => ['required', 'email', 'max:255'],
            'ekstrakurikuler_id' => ['nullable', 'integer', 'exists:ekstrakurikulers,id'],
        ], [
            'nama.required'          => 'Isi nama pembina.',
            'email_pembina.required' => 'Isi email pembina.',
            'email_pembina.email'    => 'Format email tidak valid.',
        ]);

        $ekskul = ! empty($data['ekstrakurikuler_id']) ? Ekstrakurikuler::find($data['ekstrakurikuler_id']) : null;

        $hasil = $layanan->buatManual($data['nama'], $data['email_pembina'], $request->user(), $ekskul);

        $redirect = redirect()->route('users.index');

        if ($hasil['baru']) {
            // Tampil sekali; setelahnya dibuka lewat halaman Kredensial
            return $redirect
                ->with('success', "Akun pembina {$hasil['user']->name} dibuat.")
                ->with('kredensial', ['nama' => $hasil['user']->name, 'email' => $hasil['user']->email, 'password' => $hasil['password']]);
        }

        return $redirect->with('success', "{$hasil['user']->name} (akun pembina yang sudah ada) ditugaskan ke {$ekskul->nama_ekstrakurikuler}.");
    }
}
