<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAvailable;
use App\Models\ProductLocation;
use App\Models\ProductState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MainPageController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = Product::with(['category', 'productState', 'productAvailable', 'productLocation', 'productPrice', 'productPriceAll', 'mainImage', 'productStatus']);

        // Фильтр по статусу - показываем только товары со статусом, у которого show = true
        $query->whereHas('productStatus', function($q) {
            $q->where('show', true);
        });

        // Фильтр по поиску
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhereHas('productTags', function($tagQuery) use ($search) {
                      $tagQuery->where('tag', 'like', "%{$search}%");
                  });
            });
        }

        // Фильтр по цене
        if ($request->has('price_min') || $request->has('price_max')) {
            $query->where(function($q) use ($request) {
                // Используем подзапрос для фильтрации по цене, чтобы не исключать товары без цен
                if ($request->has('price_min') && $request->price_min) {
                    $q->whereExists(function($subQuery) use ($request) {
                        $subQuery->select(DB::raw(1))
                                 ->from('product_prices')
                                 ->whereColumn('product_prices.product_id', 'products.id')
                                 ->where('product_prices.price', '>=', (int)$request->price_min);
                    });
                }
                if ($request->has('price_max') && $request->price_max) {
                    $q->whereExists(function($subQuery) use ($request) {
                        $subQuery->select(DB::raw(1))
                                 ->from('product_prices')
                                 ->whereColumn('product_prices.product_id', 'products.id')
                                 ->where('product_prices.price', '<=', (int)$request->price_max);
                    });
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

        // Сортировка
        $sort = $request->get('sort', 'default');
        switch ($sort) {
            case 'price_desc':
                $query->orderByRaw('(SELECT MAX(price) FROM product_prices WHERE product_prices.product_id = products.id) DESC');
                break;
            case 'price_asc':
                $query->orderByRaw('(SELECT MIN(price) FROM product_prices WHERE product_prices.product_id = products.id) ASC');
                break;
            case 'newest':
                $query->orderBy('last_system_update', 'desc');
                break;
            case 'oldest':
                $query->orderBy('last_system_update', 'asc');
                break;
            case 'default':
            default:
                $query->orderBy('last_system_update', 'desc');
                break;
        }

        // Пагинация
        $perPage = 12; // Количество товаров на странице
        // Добавляем distinct для устранения возможных дубликатов при использовании whereHas и eager loading
        $products = $query->distinct()->paginate($perPage)->withQueryString();

        // Ограничиваем длину названий товаров
        $products->getCollection()->transform(function ($product) {
            if (mb_strlen($product->name) > 50) {
                $product->name = mb_substr($product->name, 0, 50) . '...';
            }
            return $product;
        });

        // Получаем данные для фильтров
        $categories = Category::where('active', true)->get();
        $locations = ProductLocation::where('active', true)->get();
        $availabilities = ProductAvailable::all();
        $states = ProductState::all();

        // Подсчет товаров по регионам с учетом других фильтров
        // Создаем базовый запрос с теми же условиями, что и основной запрос
        $locationCounts = [];
        foreach ($locations as $location) {
            // Клонируем основной запрос для подсчета, но без пагинации и сортировки
            $countQuery = Product::query();
            
            // Применяем все фильтры кроме фильтра по региону (точно так же, как в основном запросе)
            $countQuery->whereHas('productStatus', function($q) {
                $q->where('show', true);
            });
            
            // Фильтр по поиску
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $countQuery->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhereHas('productTags', function($tagQuery) use ($search) {
                          $tagQuery->where('tag', 'like', "%{$search}%");
                      });
                });
            }
            
            // Фильтр по цене
            if ($request->has('price_min') || $request->has('price_max')) {
                $countQuery->where(function($q) use ($request) {
                    // Используем подзапрос для фильтрации по цене, чтобы не исключать товары без цен
                    if ($request->has('price_min') && $request->price_min) {
                        $q->whereExists(function($subQuery) use ($request) {
                            $subQuery->select(DB::raw(1))
                                     ->from('product_prices')
                                     ->whereColumn('product_prices.product_id', 'products.id')
                                     ->where('product_prices.price', '>=', (int)$request->price_min);
                        });
                    }
                    if ($request->has('price_max') && $request->price_max) {
                        $q->whereExists(function($subQuery) use ($request) {
                            $subQuery->select(DB::raw(1))
                                     ->from('product_prices')
                                     ->whereColumn('product_prices.product_id', 'products.id')
                                     ->where('product_prices.price', '<=', (int)$request->price_max);
                        });
                    }
                });
            }
            
            // Фильтр по категории
            if ($request->has('category') && $request->category) {
                $categoryId = $request->category;
                $childCategories = Category::where('parent_id', $categoryId)->pluck('id')->toArray();
                if (count($childCategories) > 0) {
                    $countQuery->whereIn('category_id', array_merge([$categoryId], $childCategories));
                } else {
                    $countQuery->where('category_id', $categoryId);
                }
            }
            
            // Фильтр по доступности
            if ($request->has('availability') && is_array($request->availability) && count($request->availability) > 0) {
                $countQuery->whereIn('product_availability_id', $request->availability);
            }
            
            // Фильтр по состоянию
            if ($request->has('condition') && is_array($request->condition) && count($request->condition) > 0) {
                $countQuery->whereIn('product_state_id', $request->condition);
            }
            
            // Фильтруем по конкретному региону
            $countQuery->where('product_location_id', $location->id);
            
            $locationCounts[$location->id] = $countQuery->count();
        }

        // Получаем выбранную категорию для отображения в заголовке и хлебных крошках
        $selectedCategory = null;
        if ($request->has('category') && $request->category) {
            $selectedCategory = Category::find($request->category);
        }

        return view('Pages.MainPage', [
            'products' => $products,
            'categories' => $categories,
            'locations' => $locations,
            'availabilities' => $availabilities,
            'states' => $states,
            'selectedCategory' => $selectedCategory,
            'locationCounts' => $locationCounts,
        ]);
    }
}
