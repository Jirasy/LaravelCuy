<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Violation;

class ViolationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $violation = Violation::create([
            'vlt_name' => "telat",
            'vlt_point' => 2,
            'vlt_punishment' => "pilah sampah"
        ]);

        $violation = Violation::create([
            'vlt_name' => "membuang sampah sembarangan",
            'vlt_point' => 10,
            'vlt_punishment' => "membersihkan lingkungan"
        ]);
    }
}
