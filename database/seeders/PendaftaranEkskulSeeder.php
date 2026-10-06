<?php

namespace Database\Seeders;

use App\Models\Ekstrakurikuler;
use App\Models\PendaftaranEkskul;
use App\Models\User;
use Illuminate\Database\Seeder;

class PendaftaranEkskulSeeder extends Seeder
{
    public function run(): void
    {
        $siswa  = User::where('role', 'siswa')->first();
        $ekskul = Ekstrakurikuler::first();

        if ($siswa && $ekskul) {
            PendaftaranEkskul::create([
                'user_id'            => $siswa->id,
                'ekstrakurikuler_id' => $ekskul->id,
                'nama_pendaftar'     => $siswa->name,
                'peran'              => 'siswa',
                'kelas'              => $siswa->kelas,
                'nisn'               => $siswa->nisn,
                'alasan_mendaftar'   => 'Ingin mengembangkan hobi.',
                'status'             => 'pending',
            ]);
        }
    }
}