@extends('Layer.MainLayer')

@section('title', 'Каталог ЮНИК С')

@section('meta')
<meta name="description" content="Продажа бывшего в употреблении промышленного оборудования: металлорежущих и деревообрабатывающих станков, прессового и кузнечного оборудования, спецтехники и оборудования для погрузочно-разгрузочных работ">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:title" content="Каталог ЮНИК С">
<meta property="og:description" content="Продажа бывшего в употреблении промышленного оборудования: металлорежущих и деревообрабатывающих станков, прессового и кузнечного оборудования, спецтехники и оборудования для погрузочно-разгрузочных работ">
<meta property="og:image" content="{{ url('/assets/img/catalog.jpeg') }}">
<meta property="og:url" content="{{ url()->current() }}">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Каталог ЮНИК С">
<meta name="twitter:description" content="Продажа бывшего в употреблении промышленного оборудования: металлорежущих и деревообрабатывающих станков, прессового и кузнечного оборудования, спецтехники и оборудования для погрузочно-разгрузочных работ">
<meta name="twitter:image" content="{{ url('/assets/img/catalog.jpeg') }}">
@endsection

@section('content')
<div class="box-container">
    <div class="breadcrumbs">
        <a href="/">Главная</a>
        <span class="separator">></span>
        @if($selectedCategory)
        <a href="/">Каталог</a>
        @else
        <span class="current">Каталог</span>
        @endif
        @if($selectedCategory)
        <span class="separator">></span>
        <span class="current">{{ $selectedCategory->name }}</span>
        @endif
    </div>

    <h1 class="catalog-title">{{ $selectedCategory ? $selectedCategory->name : 'Каталог' }}</h1>

    <form method="GET" action="{{ route('home') }}" id="filterForm">
        <div class="catalog-page-block">
            <aside class="filters-sidebar">
                <div class="filter-block">
                    <h3 class="filter-title">Поиск</h3>
                    <div class="filter-options">
                        <div class="search-box">
                            <input type="text" class="search-input" placeholder="Введите запрос..." id="searchInput" name="search" value="{{ request('search') }}">
                            <button type="submit" class="search-button" id="searchButton">Найти</button>
                        </div>
                    </div>
                </div>

                <button type="button" class="mobile-filters-toggle" id="mobileFiltersToggle">Фильтр</button>

                <div class="mobile-filters-container" id="mobileFiltersContainer">
                <div class="filter-block">
                    <h3 class="filter-title">По цене</h3>
                    <div class="filter-options">
                        <div class="price-range">
                            @php
                                $maxPrice = \App\Models\ProductPrice::where('show', true)->max('price') ?? 5000000;
                                $minPrice = \App\Models\ProductPrice::where('show', true)->min('price') ?? 0;
                                $currentPriceMin = request('price_min', $minPrice);
                                $currentPriceMax = request('price_max', $maxPrice);
                            @endphp
                            <input type="hidden" name="price_min" id="priceMinInput" value="{{ $currentPriceMin }}">
                            <input type="hidden" name="price_max" id="priceMaxInput" value="{{ $currentPriceMax }}">
                            <div class="price-inputs">
                                <div class="price-input-group">
                                    <label for="priceInputMin" class="price-input-label">От:</label>
                                    <input type="number" id="priceInputMin" class="price-input" min="{{ $minPrice }}" max="{{ $maxPrice }}" step="1" value="{{ $currentPriceMin }}" placeholder="От">
                                    <span class="price-currency">₽</span>
                                </div>
                                <div class="price-input-group">
                                    <label for="priceInputMax" class="price-input-label">До:</label>
                                    <input type="number" id="priceInputMax" class="price-input" min="{{ $minPrice }}" max="{{ $maxPrice }}" step="1" value="{{ $currentPriceMax }}" placeholder="До">
                                    <span class="price-currency">₽</span>
                                </div>
                            </div>
                            <div class="price-slider-container">
                                <input type="range" min="{{ $minPrice }}" max="{{ $maxPrice }}" step="10000" value="{{ $currentPriceMin }}" class="price-slider price-slider-min" id="priceSliderMin">
                                <input type="range" min="{{ $minPrice }}" max="{{ $maxPrice }}" step="10000" value="{{ $currentPriceMax }}" class="price-slider price-slider-max" id="priceSliderMax">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-block">
                    <h3 class="filter-title">По региону</h3>
                    <div class="filter-options">
                        @foreach($locations as $location)
                            <label class="checkbox-label">
                                <input type="checkbox" name="region[]" value="{{ $location->id }}" {{ in_array($location->id, (array)request('region', [])) ? 'checked' : '' }}>
                                <span>{{ $location->name }} ({{ $locationCounts[$location->id] ?? 0 }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="filter-block">
                    <h3 class="filter-title">По категориям</h3>
                    <div class="filter-options">
                        @php
                            $parentCategories = $categories->where('parent_id', 0);
                        @endphp
                        @foreach($parentCategories as $parentCategory)
                            @php
                                $children = $categories->where('parent_id', $parentCategory->id);
                            @endphp
                            @if($children->count() > 0)
                                <div class="category-item">
                                    <label class="category-header checkbox-label has-subcategories" data-category="{{ $parentCategory->id }}">
                                        <span class="category-toggle">+</span>
                                        <span class="category-name">{{ $parentCategory->name }}</span>
                                    </label>
                                    <div class="category-subcategories">
                                        @foreach($children as $child)
                                            <label class="subcategory-link checkbox-label" data-category="{{ $child->id }}">
                                                <input type="radio" name="category" value="{{ $child->id }}" {{ request('category') == $child->id ? 'checked' : '' }} style="display: none;">
                                                <span>{{ $child->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="category-item">
                                    <label class="category-header checkbox-label" data-category="{{ $parentCategory->id }}">
                                        <span class="category-toggle" style="opacity: 0; pointer-events: none;">+</span>
                                        <span class="category-name">{{ $parentCategory->name }}</span>
                                    </label>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="filter-block">
                    <h3 class="filter-title">По доступности</h3>
                    <div class="filter-options">
                        @foreach($availabilities as $availability)
                            <label class="checkbox-label">
                                <input type="checkbox" name="availability[]" value="{{ $availability->id }}" {{ in_array($availability->id, (array)request('availability', [])) ? 'checked' : '' }}>
                                <span>{{ $availability->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="filter-block">
                    <h3 class="filter-title">По состоянию</h3>
                    <div class="filter-options">
                        @foreach($states as $state)
                            <label class="checkbox-label">
                                <input type="checkbox" name="condition[]" value="{{ $state->id }}" {{ in_array($state->id, (array)request('condition', [])) ? 'checked' : '' }}>
                                <span>{{ $state->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="filter-apply-button">Применить фильтры</button>
                <a href="{{ route('home') }}" class="filter-reset-button">Сбросить фильтры</a>
                </div>
            </aside>

            <div class="catalog-main">
                <div class="sort-container">
                    <label for="sortSelect" class="sort-label">Сортировка:</label>
                    <select name="sort" id="sortSelect" class="sort-select">
                        <option value="default" {{ request('sort') == 'default' || !request('sort') ? 'selected' : '' }}>По умолчанию</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>По убыванию цены</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>По возрастанию цены</option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Самые новые</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Самые ранние</option>
                    </select>
                </div>

                @if($products->count() > 0)
                    <div class="products-wrapper">
                        @foreach($products as $product)
                            <div class="product-card">
                                <div class="product-image">
                                    @if($product->mainImage)
                                        <img src="{{ asset('storage/' . $product->mainImage->image) }}" alt="{{ $product->name }}">
                                    @elseif($product->productImages->count() > 0)
                                        <img src="{{ asset('storage/' . $product->productImages->first()->image) }}" alt="{{ $product->name }}">
                                    @else
                                        <img src="/assets/img/stanok.webp" alt="Станок">
                                    @endif
                                </div>
                                <div class="product-info">
                                    <div class="product-article">Арт: {{ $product->sku }}</div>
                                    <h4 class="product-name">{{ $product->name }}</h4>
                                    @if($product->category)
                                        <div class="product-category">{{ $product->category->name }}</div>
                                    @endif
                                    @if($product->productLocation)
                                        <div class="product-location">{{ $product->productLocation->name }}</div>
                                    @endif
                                    <div class="product-status-line">
                                        @if($product->productState)
                                            <span class="product-condition">{{ $product->productState->name }}</span>
                                        @endif
                                        @if($product->productAvailable)
                                            <span class="product-availability">{{ $product->productAvailable->name }}</span>
                                        @endif
                                    </div>
                                    @if($product->productStatus && $product->productStatus->name === 'Резерв')
                                        <div class="product-price" style="color: #133E71;">Станок в резерве</div>
                                    @elseif($product->productPriceAll)
                                        @if($product->productPriceAll->show)
                                            <div class="product-price">{{ number_format($product->productPriceAll->price, 0, ',', ' ') }} ₽</div>
                                        @else
                                            <div class="product-price">По запросу</div>
                                        @endif
                                    @else
                                        <div class="product-price">Цена не указана</div>
                                    @endif
                                    <a href="{{ route('advertise') }}?id={{ $product->id }}" class="product-button">Подробнее</a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Количество объявлений -->
                    <div style="margin: 20px 0; text-align: center; color: #666; font-size: 14px;">
                        Найдено объявлений: {{ $products->total() }}
                    </div>

                    <!-- Пагинация -->
                    <div class="pagination-wrapper">
                        {{ $products->links() }}
                    </div>
                @else
                    <div style="width: 100%; padding: 40px; text-align: center;">
                        <p style="font-size: 18px; color: #666;">Объявления не найдены</p>
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>

@push('scripts')
    <script src="/assets/js/catalog.js"></script>
@endpush
@endsection
