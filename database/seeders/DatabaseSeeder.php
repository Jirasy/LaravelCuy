<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\EkstrakurikulerSeeder;
use Database\Seeders\PendaftaranEkskulSeeder;
use Database\Seeders\UserSeeder;
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
        // User::factory(10)->create();

        // $this->call(MajorSeeder::class);
        // $this->call(TreeSeeder::class);
        // $this->call(HealthSeeder::class);
        // // $this->call(EmployeSeeder::class);
        // $this->call(ViolationSeeder::class);
        // $this->call(CarSeeder::class);
        $this->call([
            UserSeeder::class,
            EkstrakurikulerSeeder::class,
            PendaftaranEkskulSeeder::class,
        ]);// $this->call(JIHYSeeder::class);
      
    }
}
