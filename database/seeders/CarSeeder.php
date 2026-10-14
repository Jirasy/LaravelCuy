<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\cars;

class CarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        cars::create([
            'car_brand' => 'Toyota',
            'car_model' => 'Camry',
            'car_engine' => '2.5L',
            'car_fuel' => 'Gasoline',
            'car_type' => 'Sedan',
            'car_stock' => 10,
            'car_price' => 25000
        ]);

        cars::create([
            'car_brand' => 'Honda',
            'car_model' => 'Civic',
            'car_engine' => '1.5L',
            'car_fuel' => 'Gasoline',
            'car_type' => 'Sedan',
            'car_stock' => 5,
            'car_price' => 22000
        ]);

        cars::create([
            'car_brand' => 'Ford',
            'car_model' => 'F-150',
            'car_engine' => '3.3L V6',
            'car_fuel' => 'Gasoline',
            'car_type' => 'Truck',
            'car_stock' => 3,
            'car_price' => 30000
        ]);
    }
}
