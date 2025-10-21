<?php

namespace App\Service\Products;

use App\Models\Product;
use App\Models\ProductManager;
use App\Models\ProductCharacteristics;
use App\Models\ProductPrice;
use App\Models\ProductImages;

class ProductCreateService
{
    public function createProduct($productData)
    {
        $product = Product::create([
            'name' => $productData['name'],
            'sku' => $productData['sku'],
            'category_id' => $productData['category_id'],
            'product_state_id' => $productData['product_state_id'],
            'product_availability_id' => $productData['product_availability_id'],
            'product_location_id' => $productData['product_location_id'],
            'last_system_update' => $productData['last_system_update'] ?? now(),
        ]);

        if (isset($productData['manager'])) {
            ProductManager::create([
                'product_id' => $product->id,
                'manager' => $productData['manager']['manager'],
                'phone' => $productData['manager']['phone'],
            ]);
        }

        if (isset($productData['characteristics'])) {
            ProductCharacteristics::create([
                'product_id' => $product->id,
                'main_characteristic' => $productData['characteristics']['main_characteristic'] ?? null,
                'main_information' => $productData['characteristics']['main_information'] ?? null,
                'equipment' => $productData['characteristics']['equipment'] ?? null,
                'technical_specifications' => $productData['characteristics']['technical_specifications'] ?? null,
                'check_data' => $productData['characteristics']['check_data'] ?? null,
                'disassembling_data' => $productData['characteristics']['disassembling_data'] ?? null,
                'loading_data' => $productData['characteristics']['loading_data'] ?? null,
                'additional_information' => $productData['characteristics']['additional_information'] ?? null,
            ]);
        }

        if (isset($productData['prices'])) {
            foreach ($productData['prices'] as $priceData) {
                ProductPrice::create([
                    'product_id' => $product->id,
                    'price' => $priceData['price'] ?? 0,
                    'comment' => $priceData['comment'] ?? null,
                    'show' => $priceData['show'] ?? true,
                ]);
            }
        }

        if (isset($productData['images'])) {
            foreach ($productData['images'] as $imageData) {
                ProductImages::create([
                    'product_id' => $product->id,
                    'image' => $imageData['image'],
                    'main_image' => $imageData['main_image'] ?? false,
                ]);
            }
        }

        return $product;
    }
}
