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

        $ekskul = Ekstrakurikuler::create([
            'nama_ekstrakurikuler' => 'Rohani Islam',
            'pembina'   => 'Pak Angga Abdul Latief',
            'deskripsi' => 'Membimbing siswa dalam kegiatan keagamaan dan pengembangan spiritual.',
            'status'    => 'aktif',
        ]);

        $ekskul->syncJadwal([
            ['hari' => 'selasa', 'jam_mulai' => '15:20', 'jam_selesai' => '16:30'],
            ['hari' => 'rabu', 'jam_mulai' => '15:20', 'jam_selesai' => '16:30'],
        ]);

        $ekskul = Ekstrakurikuler::create([
            'nama_ekstrakurikuler' => 'english club',
            'pembina'   => 'Mrs Dila',
            'deskripsi' => 'Meningkatkan kemampuan berbahasa Inggris siswa melalui berbagai kegiatan dan latihan.',
            'status'    => 'aktif',
        ]);

        $ekskul->syncJadwal([
            ['hari' => 'selasa', 'jam_mulai' => '15:20', 'jam_selesai' => '16:30'],
        ]);

        $ekskul = Ekstrakurikuler::create([
            'nama_ekstrakurikuler' => 'Paskibra',
            'pembina'   => 'Pak Eman',
            'deskripsi' => 'Melatih siswa dalam baris-berbaris dan persiapan upacara bendera.',
            'status'    => 'aktif',
        ]);

        $ekskul->syncJadwal([
            ['hari' => 'sabtu', 'jam_mulai' => '10:00', 'jam_selesai' => '13:00'],
        ]);

        $ekskul = Ekstrakurikuler::create([
            'nama_ekstrakurikuler' => 'Futsal',
            'pembina'   => 'Coach Reza',
            'deskripsi' => 'Melatih teknik dasar futsal dan persiapan kompetisi.',
            'status'    => 'aktif',
        ]);
        
        $ekskul->syncJadwal([
            ['hari' => 'rabu', 'jam_mulai' => '15:20', 'jam_selesai' => '17:30'],
            ['hari' => 'sabtu', 'jam_mulai' => '10:00', 'jam_selesai' => '13:00'],
        ]);

        
    }
}
