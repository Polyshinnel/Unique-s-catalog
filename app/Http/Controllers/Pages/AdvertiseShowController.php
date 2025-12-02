<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class AdvertiseShowController extends Controller
{
    public function __invoke(Request $request)
    {
        $productId = $request->get('id');
        
        if (!$productId) {
            abort(404, 'Объявление не найдено');
        }

        $product = Product::with([
            'category',
            'productState',
            'productAvailable',
            'productLocation',
            'productPrice',
            'productImages',
            'mainImage',
            'productManager',
            'productCharacteristics'
        ])->findOrFail($productId);

        return view('Pages.AdvertiseShowPage', compact('product'));
    }
}
