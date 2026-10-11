<?php

namespace Database\Seeders;

use App\Models\Ekstrakurikuler;
use Illuminate\Database\Seeder;

class EkstrakurikulerSeeder extends Seeder
{
    public function run(): void
    {
        $ekskul = Ekstrakurikuler::create([
            'nama_ekstrakurikuler' => 'Bola Basket',
            'pembina'     => 'Ibu Rina',
            'jam_mulai'   => '15:30',
            'jam_selesai' => '17:00',
            'deskripsi'   => 'Melatih teknik dasar bola basket dan persiapan kompetisi.',
            'status'      => 'aktif',
        ]);

        $ekskul->syncHari(['rabu']);
    }
}