<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Admin Sekolah',
            'email'    => 'admin@gmail.com',
            'password' => '!@#$%^&*()_+-=[]{}|;:,.<>?',
            'nisn'     => '0000000000',
            'kelas'    => 'Admin',
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Pembina Ekskul',
            'email'    => 'pembina@gmail.com',
            'password' => 'pembina123',
            'nisn'     => null,
            'kelas'    => null,
            'role'     => 'pembina',
        ]);

        User::create([
            'name'     => 'Siswa test',
            'email'    => 'siswa@gmail.com',
            'password' => 'siswa123',
            'nisn'     => '1234567890',
            'kelas'    => 'XII PPLG',
            'role'     => 'siswa',
        ]);

        User::create([
            'name'     => 'Jimny Al Ramadhan Syahnendi',
            'email'    => 'syahrama31dhan@gmail.com',
            'password' => 'JIMNY318',
            'nisn'     => '2526.010.050',
            'kelas'    => 'XI PPLG',
            'role'     => 'siswa',
        ]);
    }
}