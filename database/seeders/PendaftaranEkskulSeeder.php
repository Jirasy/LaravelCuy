<?php

namespace Database\Seeders;

use App\Models\PendaftaranEkskul;
use App\Models\User;
use App\Models\Ekstrakurikuler;
use Illuminate\Database\Seeder;

class PendaftaranEkskulSeeder extends Seeder
{
    public function run(): void
    {
        // Cari user bertipe siswa dan ekskul pertama yang ada di database
        $siswa = User::where('role', 'siswa')->first();
        $ekskul = Ekstrakurikuler::first();

        // Hanya buat pendaftaran jika user dan ekskul memang ditemukan
        if ($siswa && $ekskul) {
            PendaftaranEkskul::create([
                'user_id'            => $siswa->id,
                'ekstrakurikuler_id' => $ekskul->id,
                'status'             => 'pending',
            ]);
        }
    }
}