<?php

namespace App\Service\Products;

use App\Models\Product;
use App\Models\ProductAvailable;
use App\Models\ProductManager;
use App\Models\ProductCharacteristics;
use App\Models\ProductPrice;
use App\Models\ProductImages;
use App\Models\ProductState;
use App\Models\ProductStatus;

class ProductCreateService
{
    public function createProduct($productData)
    {
        $status = ProductStatus::where('name', $productData['status'])->first();
        $state = ProductState::where('name', $productData['state'])->first();
        $availability = ProductAvailable::where('name', $productData['available'])->first();
        $product = Product::create([
            'name' => $productData['name'],
            'sku' => $productData['sku'],
            'category_id' => $productData['category_id'],
            'product_status_id' => $status->id,
            'product_state_id' => $state->id,
            'product_availability_id' => $availability->id,
            'product_location_id' => $productData['product_location_id'],
            'last_system_update' => $productData['last_system_update'] ?? now(),
            'panel_adv_id' => $productData['panel_adv_id'],
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

        return $product;
    }
}
