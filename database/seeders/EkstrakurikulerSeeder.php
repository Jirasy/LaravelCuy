<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ekstrakurikuler;


class EkstrakurikulerSeeder extends Seeder
{
    public function run(): void
    {
        Ekstrakurikuler::create([
            'nama_ekstrakurikuler' => 'Panahan',
            'pembina'     => 'Pak Hadi',
            'hari'        => 'Senin',
            'jam_mulai'   => '15:30',
            'jam_selesai' => '17:00',
            'deskripsi'   => 'Melatih teknik dasar memanah dan persiapan kompetisi.',
            'status'      => 'aktif',
        ]);
    }
}