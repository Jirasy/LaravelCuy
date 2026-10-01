<?php

namespace Database\Seeders;

use App\Models\EksPengguna;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(MajorSeeder::class);
        $this->call(TreeSeeder::class);
        $this->call(HealthSeeder::class);
        // $this->call(EmployeSeeder::class);
        $this->call(ViolationSeeder::class);
        // $this->call(JIHYSeeder::class);
        $this->call(EksPenggunaSeeder::class);
    }
}
