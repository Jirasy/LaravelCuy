<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Teacher;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teacher1 = Teacher::create([
            'tch_name' => 'Jimny Al Ramadhan Syahnendi',
            'tch_email' => 'syahrama31dhan@gmail.com',
            'tch_phone' => '0895708291000',
            'tch_address' => 'sitgar',
        ]);
    }
}
