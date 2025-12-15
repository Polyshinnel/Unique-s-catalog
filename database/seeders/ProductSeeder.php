<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ProductImages;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Токарно-винторезный 16к20 РМЦ1500 Рязань 1972',
                'sku' => '16K20-001',
                'category_id' => 1,
                'product_status_id' => 1,
                'product_state_id' => 2,
                'product_availability_id' => 1,
                'product_location_id' => 1,
                'last_system_update' => now(),
                'panel_adv_id' => 1001,
            ],
            [
                'name' => 'Токарный станок ДИП300',
                'sku' => 'ДИП300-015',
                'category_id' => 1,
                'product_status_id' => 1,
                'product_state_id' => 3,
                'product_availability_id' => 2,
                'product_location_id' => 1,
                'last_system_update' => now(),
                'panel_adv_id' => 1002,
            ],
            [
                'name' => 'Вертикально-фрезерный станок 6Р82',
                'sku' => '6Р82-023',
                'category_id' => 2,
                'product_status_id' => 1,
                'product_state_id' => 1,
                'product_availability_id' => 1,
                'product_location_id' => 1,
                'last_system_update' => now(),
                'panel_adv_id' => 1003,
            ],
            [
                'name' => 'Горизонтально-фрезерный станок 6М82',
                'sku' => '6М82-008',
                'category_id' => 2,
                'product_status_id' => 1,
                'product_state_id' => 2,
                'product_availability_id' => 1,
                'product_location_id' => 1,
                'last_system_update' => now(),
                'panel_adv_id' => 1004,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);
            
            // Добавляем цену
            ProductPrice::create([
                'product_id' => $product->id,
                'price' => rand(500000, 2000000),
            ]);
            
            // Добавляем изображение
            ProductImages::create([
                'product_id' => $product->id,
                'image' => 'products/stanok.webp',
                'main_image' => true,
            ]);
        }
    }
}
