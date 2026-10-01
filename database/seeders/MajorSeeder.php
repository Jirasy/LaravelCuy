<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Major;

class MajorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $major1 = Major::create([
            'mjr_name' => 'Pemograman Perangkat Lunak dan Game',
            'mjr_abreviation' => 'PPLG',
        ]);

        $major2 = Major::create([
            'mjr_name' => 'Rekayasa Perangkat Lunak',
            'mjr_abreviation' => 'RPL',
        ]);

        $major3 = Major::create([
            'mjr_name' => 'Teknik Komputer dan Jaringan',
            'mjr_abreviation' => 'TKJ',
        ]);
    }
}
