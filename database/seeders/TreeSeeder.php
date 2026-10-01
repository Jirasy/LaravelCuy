<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tree;

class TreeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Tree=Tree::create([
            'tre_name' => 'Oak',
            'tre_family' => 'Fagaceae',
            'tre_age' => 100,
            'tre_origin' => 'North America',
            'tre_condition' => 'Healthy'
            
        ]);
    }
}
