<?php

namespace App\Services;

use App\Models\KredensialSementara;
use App\Models\User;
use App\Notifications\RekapImportSiswa;
use App\Support\PasswordAcak;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportSiswaService
{
    public const DOMAIN_EMAIL_DEFAULT = 'siswa.local';

    /**
     * Membuat akun role 'siswa' untuk tiap baris. Semua dalam satu transaksi: kalau ada yang gagal, tidak ada akun yang tersisa.
     *
     * Password acak disimpan HASH di users, dan salinan TERENKRIPSI (berlaku 7 hari, terhapus saat siswa mengganti password)
     * di kredensial_sementara. Isi notifikasi rekap tidak memuat password, hanya tautan ke halaman rekapnya.
     *
     * @param  list<array{nama: string, nisn: ?string, kelas: ?string, email: ?string}>  $daftar
     * @return array{dibuat: list<User>, dilewati: list<array{nama: string, alasan: string}>, rekap_id: ?string}
     */
    public function impor(
        array $daftar,
        User $pengunggah,
        string $namaBerkas,
        int $diabaikan = 0,
        string $domain = self::DOMAIN_EMAIL_DEFAULT,
        bool $izinkanNamaSama = false,
    ): array {
        return DB::transaction(function () use ($daftar, $pengunggah, $namaBerkas, $diabaikan, $domain, $izinkanNamaSama) {
            $namaTerdaftar = $izinkanNamaSama ? [] : User::where('role', 'siswa')->pluck('name')
                ->mapWithKeys(fn ($n) => [mb_strtolower(trim($n)) => true])->all();
            $nisnTerdaftar = User::whereNotNull('nisn')->pluck('nisn')
                ->mapWithKeys(fn ($n) => [(string) $n => true])->all();
            $emailTerpakai = User::pluck('email')
                ->mapWithKeys(fn ($e) => [mb_strtolower($e) => true])->all();

            $dibuat = [];
            $dilewati = [];
            $terlihat = [];

            foreach ($daftar as $baris) {
                $nama = $baris['nama'];
                $nisn = $baris['nisn'];
                $email = $baris['email'];

                $kunci = $nisn ?? mb_strtolower($nama);
                if (isset($terlihat[$kunci])) {
                    $dilewati[] = ['nama' => $nama, 'alasan' => 'Muncul lebih dari sekali di berkas'];

                    continue;
                }
                $terlihat[$kunci] = true;

                if ($nisn !== null && isset($nisnTerdaftar[$nisn])) {
                    $dilewati[] = ['nama' => $nama, 'alasan' => "NISN {$nisn} sudah terdaftar"];

                    continue;
                }

                if ($email !== null && isset($emailTerpakai[$email])) {
                    $dilewati[] = ['nama' => $nama, 'alasan' => "Email {$email} sudah dipakai"];

                    continue;
                }

                // Nama sama hanya dianggap duplikat kalau berkas tidak membawa NISN pembeda
                if ($nisn === null && isset($namaTerdaftar[mb_strtolower($nama)])) {
                    $dilewati[] = ['nama' => $nama, 'alasan' => 'Siswa dengan nama yang sama sudah terdaftar'];

                    continue;
                }

                $email ??= $this->buatEmail($nama, $domain, $emailTerpakai);
                $emailTerpakai[$email] = true;
                $password = PasswordAcak::buat();

                $user = new User([
                    'name' => $nama,
                    'email' => $email,
                    'password' => $password,      // di-hash otomatis oleh cast 'hashed'
                    'role' => 'siswa',
                    'nisn' => $nisn,              // null kalau belum ada di berkas
                    'kelas' => $baris['kelas'],
                    'must_change_password' => true,
                ]);
                $user->is_active = true;
                $user->save();

                KredensialSementara::catat($user, $password, null, $pengunggah);

                if ($nisn !== null) {
                    $nisnTerdaftar[$nisn] = true;
                }

                $dibuat[] = $user;
            }

            $rekapId = null;

            if ($dibuat !== []) {
                $rekapId = (string) Str::uuid();

                $pengunggah->notify(new RekapImportSiswa(
                    id: $rekapId,
                    namaBerkas: $namaBerkas,
                    idSiswa: array_map(fn (User $u) => $u->id, $dibuat),
                    namaSiswa: array_map(fn (User $u) => $u->name, $dibuat),
                    dilewati: array_slice($dilewati, 0, 200),
                    diabaikan: $diabaikan,
                ));
            }

            return ['dibuat' => $dibuat, 'dilewati' => $dilewati, 'rekap_id' => $rekapId];
        });
    }

    /**
     * "Budi Santoso" -> budi.santoso@siswa.local, kalau sudah dipakai -> budi.santoso2@siswa.local, dst.
     *
     * @param  array<string, true>  $terpakai
     */
    private function buatEmail(string $nama, string $domain, array $terpakai): string
    {
        $dasar = Str::limit(Str::slug($nama, '.'), 40, '');
        $dasar = trim($dasar, '.') ?: 'siswa';

        $email = "{$dasar}@{$domain}";

        for ($i = 2; isset($terpakai[$email]); $i++) {
            $email = "{$dasar}{$i}@{$domain}";
        }

        return $email;
    }
}
