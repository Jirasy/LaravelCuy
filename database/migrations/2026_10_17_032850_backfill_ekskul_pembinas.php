<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Salin data lama: kalau teks "pembina" di ekskul SAMA PERSIS dengan nama user ber-role pembina,
// hubungkan otomatis. Yang tidak cocok ditugaskan admin lewat halaman Edit Ekskul.
return new class extends Migration
{
    public function up(): void
    {
        $sekarang = now();

        foreach (DB::table('ekstrakurikulers')->get(['id', 'pembina']) as $ekskul) {
            $nama = mb_strtolower(trim((string) $ekskul->pembina));
            if ($nama === '') {
                continue;
            }

            $user = DB::table('users')
                ->where('role', 'pembina')
                ->whereRaw('LOWER(name) = ?', [$nama])
                ->first();

            if ($user) {
                DB::table('ekskul_pembinas')->insert([
                    'ekstrakurikuler_id' => $ekskul->id,
                    'user_id'            => $user->id,
                    'mulai'              => $sekarang->toDateString(),
                    'created_at'         => $sekarang,
                    'updated_at'         => $sekarang,
                ]);
            }
        }
    }

    public function down(): void
    {
        // data hasil salinan dibiarkan
    }
};