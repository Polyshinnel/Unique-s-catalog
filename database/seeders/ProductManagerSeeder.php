<?php

namespace Database\Seeders;

use App\Models\ProductManager;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductManagerSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        
        $managers = [
            [
                'manager' => 'Петр Иванов',
                'phone' => '8 (931) 854-54-15',
            ],
            [
                'manager' => 'Анна Смирнова',
                'phone' => '8 (920) 123-45-67',
            ],
            [
                'manager' => 'Михаил Петров',
                'phone' => '8 (910) 987-65-43',
            ],
            [
                'manager' => 'Елена Козлова',
                'phone' => '8 (905) 555-12-34',
            ],
        ];

        foreach ($products as $index => $product) {
            $managerData = $managers[$index % count($managers)];
            ProductManager::create([
                'product_id' => $product->id,
                'manager' => $managerData['manager'],
                'phone' => $managerData['phone'],
            ]);
        }
    }
}

