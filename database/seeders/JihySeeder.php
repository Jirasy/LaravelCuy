<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Jihy;

class JihySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Jihy=Jihy::create([
            'jihy_model_name' => 'FoxR31',
            'jihy_model_type' => 'Muscle Car',
            'jihy_fuel_type' => 'Gasoline',
            'jihy_transmission' => 'Automatic',
            'jihy_body_type' => '2-door',
            'jihy_seating_capacity' => '5',
            'jihy_engine_capacity' => '2.5L',
            'jihy_price' => '$25,000',
            'jihy_status' => true,
            'jihy_origin' => 'Malaysia',
            'jihy_founded_year' => 2027,
            'jihy_founder' => 'Jimny',
            'jihy_headquarters' => 'Bandung, Indonesia',
            'jihy_description' => 'The FoxR31 is a muscle car that has been manufactured by Jimny since 2027. It is known for its performance, design, and driving dynamics.'
        ]);
    }
}
