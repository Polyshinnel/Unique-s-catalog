<?php

namespace Database\Seeders;

use App\Models\ProductLocation;
use Illuminate\Database\Seeder;

class ProductLocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Калужская область',
                'active' => true,
                'panel_location_id' => 1,
            ],
            [
                'name' => 'Московская область',
                'active' => true,
                'panel_location_id' => 2,
            ],
            [
                'name' => 'Орловская область',
                'active' => true,
                'panel_location_id' => 3,
            ],
        ];

        foreach ($locations as $location) {
            ProductLocation::create($location);
        }
    }
}
