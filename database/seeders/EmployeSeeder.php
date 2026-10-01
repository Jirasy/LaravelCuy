<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employe;

class EmployeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $employe=Employe::create([
            'emp_number' => 92380,
            'emp_name' => 'Aliful Daffa Imtiyaz munawar',
            'emp_position' => 'Software Engineer',
            'emp_status' => 'Active'
        ]);
    }
}
