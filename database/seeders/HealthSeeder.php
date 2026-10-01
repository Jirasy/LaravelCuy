<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder; 
use App\Models\health;

class HealthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Health=Health::create([
            'hlt_name' => 'Jimny Al Ramadhan Syahnendi',
            'hlt_blood_type' => 'A=+',
            'hlt_illness' => 'None',
            'hlt_disability' => 'None',
            'hlt_height' => '160 cm',
            'hlt_weight' => '40 kg'
        ]);
    }
}

