@extends('Layer.MainLayer')

@section('content')
<div class="box-container">
    <div class="breadcrumbs">
        <a href="/">Главная</a>
        <span class="separator">></span>
        <a href="/">Каталог</a>
        @if($product->category)
        <span class="separator">></span>
        <a href="/">{{ $product->category->name }}</a>
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

            @if($product->productPrice)
            <div class="advertise-price">
                <div class="price-label">ЦЕНА</div>
                <div class="price-value">{{ number_format($product->productPrice->price, 0, ',', ' ') }} ₽</div>
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

                @if($product->productPrice)
                <div class="info-block">
                    <h2 class="info-block-title">Условия продажи</h2>
                    <div class="info-block-content">
                        <p><strong>ЦЕНА: {{ number_format($product->productPrice->price, 0, ',', ' ') }} ₽</strong></p>
                        @if($product->productPrice->comment)
                        <div class="success-text">
                            {!! $product->productPrice->comment !!}
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
        </div>
    </div>
</div>


@push('scripts')
    <script src="/assets/js/catalog-item.js"></script>
@endpush
@endsection