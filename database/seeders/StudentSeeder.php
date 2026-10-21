<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Student;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Student=Student::create([
            'std_name' => 'John Doe',
            'std_nis' => '123456789',
            'std_gender' => 'Male',
            'std_birthplace' => 'New York',
            'std_birthdate' => '2005-01-01',
            'std_address' => '123 Main St, New York, NY',
            'std_phone' => '555-1234',
            'std_email' => 'johndoe@example.com'
        ]);
    }
}
