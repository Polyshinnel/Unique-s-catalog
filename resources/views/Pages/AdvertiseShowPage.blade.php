@extends('Layer.MainLayer')

@php
    // Получаем главное изображение товара
    $mainImage = $product->mainImage ?: ($product->productImages->count() > 0 ? $product->productImages->first() : null);
    $imageUrl = $mainImage ? asset('storage/' . $mainImage->image) : url('/assets/img/stanok.webp');
    
    // Формируем описание
    $description = '';
    if ($product->productCharacteristics && $product->productCharacteristics->main_information) {
        // Берем первые 200 символов основной информации, очищая HTML
        $description = strip_tags($product->productCharacteristics->main_information);
        $description = mb_substr($description, 0, 200);
        if (mb_strlen($product->productCharacteristics->main_information) > 200) {
            $description .= '...';
        }
    } else {
        // Если основной информации нет, формируем описание из доступных данных
        $descriptionParts = [];
        if ($product->category) {
            $descriptionParts[] = $product->category->name;
        }
        if ($product->productState) {
            $descriptionParts[] = 'Состояние: ' . $product->productState->name;
        }
        if ($product->productAvailable) {
            $descriptionParts[] = 'Наличие: ' . $product->productAvailable->name;
        }
        $description = !empty($descriptionParts) ? implode('. ', $descriptionParts) : $product->name;
    }
    
    // Получаем цену
    $price = '';
    if (!$product->productStatus || !$product->productStatus->show) {
        $price = 'Снят с продажи';
    } elseif ($product->productStatus && $product->productStatus->name === 'Резерв') {
        $price = 'Станок в резерве';
    } elseif ($product->productPriceAll) {
        if ($product->productPriceAll->show) {
            $price = number_format($product->productPriceAll->price, 0, ',', ' ') . ' ₽';
        } else {
            $price = 'По запросу';
        }
    }
    
    // Формируем полный URL страницы
    $pageUrl = route('advertise', ['id' => $product->id]);
@endphp

@section('title', $product->name . ' - Каталог ЮНИК С')

@section('meta')
<meta name="description" content="{{ $description }}">

<!-- Open Graph -->
<meta property="og:type" content="product">
<meta property="og:title" content="{{ $product->name }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $imageUrl }}">
<meta property="og:url" content="{{ $pageUrl }}">
@if($product->productStatus && $product->productStatus->show && $product->productPriceAll && $product->productPriceAll->show)
<meta property="product:price:amount" content="{{ $product->productPriceAll->price }}">
<meta property="product:price:currency" content="RUB">
@endif

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $product->name }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $imageUrl }}">
@endsection

@section('content')
<div class="box-container">
    <div class="breadcrumbs">
        <a href="/">Главная</a>
        <span class="separator">></span>
        <a href="/">Каталог</a>
        @if($product->category)
        <span class="separator">></span>
        <a href="{{ route('home') }}?category={{ $product->category->id }}">{{ $product->category->name }}</a>
        @endif
        <span class="separator">></span>
        <span class="current">{{ $product->name }}</span>
    </div>

    <h1 class="catalog-title">{{ $product->name }}</h1>

    <div class="advertise-info">
        <div class="advertise-main">
            <div class="advertise-details">
                <div class="detail-item">
                    <span class="detail-label">Название:</span>
                    <span class="detail-value">{{ $product->name }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Артикул:</span>
                    <span class="detail-value">{{ $product->sku }}</span>
                </div>
                @if($product->productState)
                <div class="detail-item">
                    <span class="detail-label">Состояние:</span>
                    <span class="detail-value">{{ $product->productState->name }}</span>
                </div>
                @endif
                @if($product->productAvailable)
                <div class="detail-item">
                    <span class="detail-label">Наличие:</span>
                    <span class="detail-value">{{ $product->productAvailable->name }}</span>
                </div>
                @endif
                @if($product->productLocation)
                <div class="detail-item">
                    <span class="detail-label">Локация:</span>
                    <span class="detail-value">{{ $product->productLocation->name }}</span>
                </div>
                @endif
                @if($product->category)
                <div class="detail-item">
                    <span class="detail-label">Категория:</span>
                    <span class="detail-value">{{ $product->category->name }}</span>
                </div>
                @endif
            </div>

            @if(!$product->productStatus || !$product->productStatus->show)
            <div class="advertise-price">
                <div class="price-label">СТАТУС</div>
                <div class="price-value" style="color: #133E71;">Снят с продажи</div>
            </div>
            @elseif($product->productStatus && $product->productStatus->name === 'Резерв')
            <div class="advertise-price">
                <div class="price-label">СТАТУС</div>
                <div class="price-value" style="color: #133E71;">Станок в резерве</div>
            </div>
            @elseif($product->productPriceAll)
            <div class="advertise-price">
                <div class="price-label">ЦЕНА</div>
                @if($product->productPriceAll->show)
                    <div class="price-value">{{ number_format($product->productPriceAll->price, 0, ',', ' ') }} ₽</div>
                @else
                    <div class="price-value">По запросу</div>
                @endif
            </div>
            @endif

            @if($product->productManager)
            <div class="advertise-manager">
                <div class="manager-title">Контакты менеджера</div>
                <div class="manager-info">
                    <div class="manager-item">
                        <span class="manager-label">Менеджер:</span>
                        <span class="manager-value">{{ $product->productManager->manager }}</span>
                    </div>
                    <div class="manager-item">
                        <span class="manager-label">Телефон:</span>
                        <span class="manager-value">{{ $product->productManager->phone }}</span>
                    </div>
                </div>
            </div>

            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $product->productManager->phone) }}" class="call-button">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                Позвонить
            </a>
            @endif
        </div>

        <div class="advertise-content">
            <div class="advertise-gallery">
                @if($product->productImages && $product->productImages->count() > 0)
                    @php
                        $mainImage = $product->mainImage ?: $product->productImages->first();
                    @endphp
                    @if($mainImage)
                    <div class="gallery-main-image">
                        <img src="{{ asset('storage/' . $mainImage->image) }}" alt="{{ $product->name }}">
                    </div>
                    @if($product->productImages->count() > 1)
                    <div class="gallery-thumbnails">
                        @foreach($product->productImages as $index => $image)
                        <div class="gallery-thumb {{ $image->id == $mainImage->id ? 'active' : '' }}" data-image="{{ asset('storage/' . $image->image) }}">
                            <img src="{{ asset('storage/' . $image->image) }}" alt="Фото {{ $index + 1 }}">
                        </div>
                        @endforeach
                    </div>
                    @endif
                    @endif
                @else
                    <div class="gallery-main-image">
                        <img src="/assets/img/stanok.webp" alt="{{ $product->name }}">
                    </div>
                @endif
            </div>

            @if($product->productCharacteristics)
                @if($product->productCharacteristics->main_characteristic)
                <div class="info-block">
                    <h2 class="info-block-title">Основные характеристики</h2>
                    <div class="info-block-content">
                        {!! $product->productCharacteristics->main_characteristic !!}
                    </div>
                </div>
                @endif

                @if($product->productCharacteristics->main_information)
                <div class="info-block">
                    <h2 class="info-block-title">Основная информация</h2>
                    <div class="info-block-content">
                        {!! $product->productCharacteristics->main_information !!}
                    </div>
                </div>
                @endif

                @if($product->productCharacteristics->equipment)
                <div class="info-block">
                    <h2 class="info-block-title">Комплектация</h2>
                    <div class="info-block-content">
                        {!! $product->productCharacteristics->equipment !!}
                    </div>
                </div>
                @endif

                @if($product->productCharacteristics->technical_specifications)
                <div class="info-block">
                    <h2 class="info-block-title">Технические характеристики</h2>
                    <div class="info-block-content">
                        {!! $product->productCharacteristics->technical_specifications !!}
                    </div>
                </div>
                @endif

                @if(!$product->productStatus || !$product->productStatus->show)
                <div class="info-block">
                    <h2 class="info-block-title">Условия продажи</h2>
                    <div class="info-block-content">
                        <p><strong style="color: #133E71;">Снят с продажи</strong></p>
                    </div>
                </div>
                @elseif($product->productStatus && $product->productStatus->name === 'Резерв')
                <div class="info-block">
                    <h2 class="info-block-title">Условия продажи</h2>
                    <div class="info-block-content">
                        <p><strong style="color: #133E71;">Станок в резерве</strong></p>
                    </div>
                </div>
                @elseif($product->productPriceAll)
                <div class="info-block">
                    <h2 class="info-block-title">Условия продажи</h2>
                    <div class="info-block-content">
                        @if($product->productPriceAll->show)
                            <p><strong>ЦЕНА: {{ number_format($product->productPriceAll->price, 0, ',', ' ') }} ₽</strong></p>
                        @else
                            <p><strong>ЦЕНА: По запросу</strong></p>
                        @endif
                        @if($product->productPriceAll->comment)
                        <div class="success-text">
                            {!! $product->productPriceAll->comment !!}
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                @if($product->productCharacteristics->check_data)
                <div class="info-block">
                    <h2 class="info-block-title">Проверка</h2>
                    <div class="info-block-content">
                        {!! $product->productCharacteristics->check_data !!}
                    </div>
                </div>
                @endif

                @if($product->productCharacteristics->disassembling_data)
                <div class="info-block">
                    <h2 class="info-block-title">Демонтаж</h2>
                    <div class="info-block-content">
                        {!! $product->productCharacteristics->disassembling_data !!}
                    </div>
                </div>
                @endif

                @if($product->productCharacteristics->loading_data)
                <div class="info-block">
                    <h2 class="info-block-title">Погрузка</h2>
                    <div class="info-block-content">
                        {!! $product->productCharacteristics->loading_data !!}
                    </div>
                </div>
                @endif

                @if($product->productCharacteristics->additional_information)
                <div class="info-block">
                    <h2 class="info-block-title">Дополнительная информация</h2>
                    <div class="info-block-content">
                        {!! $product->productCharacteristics->additional_information !!}
                    </div>
                </div>
                @endif
            @endif

            @if($product->productTags && $product->productTags->count() > 0)
            <div class="info-block">
                <h2 class="info-block-title">Теги товара</h2>
                <div class="product-tags">
                    @foreach($product->productTags as $tag)
                    <a href="{{ route('home') }}?search={{ urlencode($tag->tag) }}" class="product-tag">{{ $tag->tag }}</a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>


@push('scripts')
    <script src="/assets/js/catalog-item.js?ver=1234"></script>
@endpush
@endsection