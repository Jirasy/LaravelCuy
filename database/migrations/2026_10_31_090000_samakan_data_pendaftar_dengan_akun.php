<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Tabel pendaftaran menyimpan salinan nama, kelas, dan NISN saat siswa mendaftar. Setelah akun diedit, salinan itu
// tertinggal dan tampil sebagai data lama. Migrasi ini menyamakan semua salinan yang terhubung ke akun satu kali;
// ke depan, User::booted() menjaganya tetap sama setiap akun diubah.
// Kelas/NISN akun yang kosong tidak menimpa salinan yang terisi (mis. NISN yang diketik siswa saat mendaftar).
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->orderBy('id')->chunkById(200, function ($users) {
            foreach ($users as $user) {
                $salin = ['nama_pendaftar' => $user->name];

                if (filled($user->kelas)) {
                    $salin['kelas'] = $user->kelas;
                }

                if (filled($user->nisn)) {
                    $salin['nisn'] = $user->nisn;
                }

                DB::table('pendaftaran_ekskuls')->where('user_id', $user->id)->update($salin);
            }
        });
    }

    public function down(): void
    {
        // Data lama tidak bisa dipulihkan (dan memang salah); tidak ada yang perlu dibatalkan.
    }
};
