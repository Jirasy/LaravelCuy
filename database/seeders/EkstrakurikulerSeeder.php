<?php

namespace Database\Seeders;

use App\Models\Ekstrakurikuler;
use Illuminate\Database\Seeder;

class EkstrakurikulerSeeder extends Seeder
{
    public function run(): void
    {
        $ekskul = Ekstrakurikuler::create([
            'nama_ekstrakurikuler' => 'Silat',
            'pembina'   => 'kang Rizky',
            'deskripsi' => 'Melatih teknik dasar silat dan persiapan kompetisi.',
            'status'    => 'aktif',
        ]);

        $ekskul->syncJadwal([
            ['hari' => 'sabtu', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00'],
        ]);
    }
}