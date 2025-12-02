<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAvailable;
use App\Models\ProductLocation;
use App\Models\ProductState;
use Illuminate\Http\Request;

class MainPageController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = Product::with(['category', 'productState', 'productAvailable', 'productLocation', 'productPrice', 'mainImage'])
            ->orderBy('last_system_update', 'desc');

        // Фильтр по поиску
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Фильтр по цене
        if ($request->has('price_min') || $request->has('price_max')) {
            $query->whereHas('productPrice', function($q) use ($request) {
                $q->where('show', true);
                if ($request->has('price_min') && $request->price_min) {
                    $q->where('price', '>=', (int)$request->price_min);
                }
                if ($request->has('price_max') && $request->price_max) {
                    $q->where('price', '<=', (int)$request->price_max);
                }
            });
        }

        // Фильтр по региону
        if ($request->has('region') && is_array($request->region) && count($request->region) > 0) {
            $query->whereIn('product_location_id', $request->region);
        }

        // Фильтр по категории
        if ($request->has('category') && $request->category) {
            $categoryId = $request->category;
            // Проверяем, есть ли дочерние категории
            $childCategories = Category::where('parent_id', $categoryId)->pluck('id')->toArray();
            if (count($childCategories) > 0) {
                // Если есть дочерние категории, фильтруем по родительской и дочерним
                $query->whereIn('category_id', array_merge([$categoryId], $childCategories));
            } else {
                // Если дочерних нет, фильтруем только по выбранной категории
                $query->where('category_id', $categoryId);
            }
        }

        // Фильтр по доступности
        if ($request->has('availability') && is_array($request->availability) && count($request->availability) > 0) {
            $query->whereIn('product_availability_id', $request->availability);
        }

        // Фильтр по состоянию
        if ($request->has('condition') && is_array($request->condition) && count($request->condition) > 0) {
            $query->whereIn('product_state_id', $request->condition);
        }

        // Пагинация
        $perPage = 12; // Количество товаров на странице
        $products = $query->paginate($perPage)->withQueryString();

        // Получаем данные для фильтров
        $categories = Category::where('active', true)->get();
        $locations = ProductLocation::where('active', true)->get();
        $availabilities = ProductAvailable::all();
        $states = ProductState::all();

        return view('Pages.MainPage', [
            'products' => $products,
            'categories' => $categories,
            'locations' => $locations,
            'availabilities' => $availabilities,
            'states' => $states,
        ]);
    }
}
