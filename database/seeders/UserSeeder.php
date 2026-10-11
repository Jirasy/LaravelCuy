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
            'password' => '@dm1nP@ssw0rd',
            'nisn'     => '0000000000',
            'kelas'    => 'Admin',
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Pembina Ekskul',
            'email'    => 'pembina@gmail.com',
            'password' => 'password123',
            'nisn'     => null,
            'kelas'    => null,
            'role'     => 'pembina',
        ]);

        User::create([
            'name'     => 'Siswa Utama',
            'email'    => 'siswa@gmail.com',
            'password' => 'password123',
            'nisn'     => '1234567890',
            'kelas'    => 'XII PPLG',
            'role'     => 'siswa',
        ]);
    }
}