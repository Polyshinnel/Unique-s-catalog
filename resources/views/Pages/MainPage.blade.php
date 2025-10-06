@extends('Layer.MainLayer')

@section('content')
<div class="box-container">
    <div class="breadcrumbs">
        <a href="/">Главная</a>
        <span class="separator">></span>
        <span class="current">Каталог</span>
    </div>

    <h1 class="catalog-title">Каталог</h1>

    <div class="catalog-page-block">
        <aside class="filters-sidebar">
            <div class="filter-block">
                <h3 class="filter-title">Поиск</h3>
                <div class="filter-options">
                    <div class="search-box">
                        <input type="text" class="search-input" placeholder="Введите запрос..." id="searchInput">
                        <button class="search-button" id="searchButton">Найти</button>
                    </div>
                </div>
            </div>

            <div class="filter-block">
                <h3 class="filter-title">По цене</h3>
                <div class="filter-options">
                    <div class="price-range">
                        <input type="range" min="0" max="5000000" step="10000" value="2500000" class="price-slider" id="priceSlider">
                        <div class="price-values">
                            <span>0 ₽</span>
                            <span id="priceValue">2 500 000 ₽</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="filter-block">
                <h3 class="filter-title">По региону</h3>
                <div class="filter-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="region" value="kaluga">
                        <span>Калужская область</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="region" value="moscow">
                        <span>Московская область</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="region" value="orel">
                        <span>Орловская область</span>
                    </label>
                </div>
            </div>

            <div class="filter-block">
                <h3 class="filter-title">По категориям</h3>
                <div class="filter-options">
                    <div class="category-item">
                        <div class="category-header" data-category="tokarnie">
                            <span class="category-toggle">+</span>
                            <span class="category-name">Токарные станки</span>
                        </div>
                        <div class="category-subcategories">
                            <div class="subcategory-link" data-category="16k20">16К20 и аналоги</div>
                            <div class="subcategory-link" data-category="dip300">ДИП300 и аналоги</div>
                            <div class="subcategory-link" data-category="dip500">ДИП500 и аналоги</div>
                        </div>
                    </div>

                    <div class="category-item">
                        <div class="category-header" data-category="frezerni">
                            <span class="category-toggle">+</span>
                            <span class="category-name">Фрезерные станки</span>
                        </div>
                        <div class="category-subcategories">
                            <div class="subcategory-link" data-category="vertical">Вертикально-фрезерные</div>
                            <div class="subcategory-link" data-category="horizontal">Горизонтально-фрезерные</div>
                            <div class="subcategory-link" data-category="longitudinal">Продольно-фрезерные</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="filter-block">
                <h3 class="filter-title">По доступности</h3>
                <div class="filter-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="availability" value="in_stock">
                        <span>В наличии</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="availability" value="on_order">
                        <span>Под заказ</span>
                    </label>
                </div>
            </div>

            <div class="filter-block">
                <h3 class="filter-title">По состоянию</h3>
                <div class="filter-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="condition" value="new">
                        <span>Новые</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="condition" value="used">
                        <span>Б.У</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="condition" value="restored">
                        <span>Восстановленные</span>
                    </label>
                </div>
            </div>
        </aside>

        <div class="catalog-main">
            <div class="product-card">
                <div class="product-image">
                    <img src="/assets/img/stanok.webp" alt="Станок">
                </div>
                <div class="product-info">
                    <div class="product-article">Арт: 16K20-001</div>
                    <h4 class="product-name">Токарно-винторезный станок 16К20</h4>
                    <div class="product-category">Токарные станки</div>
                    <div class="product-status-line">
                        <span class="product-condition">Б.У</span>
                        <span class="product-availability">В наличии</span>
                    </div>
                    <div class="product-price">850 000 ₽</div>
                    <a href="/advertise" class="product-button">Подробнее</a>
                </div>
            </div>

            <div class="product-card">
                <div class="product-image">
                    <img src="/assets/img/stanok.webp" alt="Станок">
                </div>
                <div class="product-info">
                    <div class="product-article">Арт: ДИП300-015</div>
                    <h4 class="product-name">Токарный станок ДИП300</h4>
                    <div class="product-category">Токарные станки</div>
                    <div class="product-status-line">
                        <span class="product-condition">Восстановленный</span>
                        <span class="product-availability">Под заказ</span>
                    </div>
                    <div class="product-price">1 200 000 ₽</div>
                    <a href="/advertise" class="product-button">Подробнее</a>
                </div>
            </div>

            <div class="product-card">
                <div class="product-image">
                    <img src="/assets/img/stanok.webp" alt="Станок">
                </div>
                <div class="product-info">
                    <div class="product-article">Арт: 6Р82-023</div>
                    <h4 class="product-name">Вертикально-фрезерный станок 6Р82</h4>
                    <div class="product-category">Фрезерные станки</div>
                    <div class="product-status-line">
                        <span class="product-condition">Новый</span>
                        <span class="product-availability">В наличии</span>
                    </div>
                    <div class="product-price">1 500 000 ₽</div>
                    <a href="/advertise" class="product-button">Подробнее</a>
                </div>
            </div>

            <div class="product-card">
                <div class="product-image">
                    <img src="/assets/img/stanok.webp" alt="Станок">
                </div>
                <div class="product-info">
                    <div class="product-article">Арт: 6М82-008</div>
                    <h4 class="product-name">Горизонтально-фрезерный станок 6М82</h4>
                    <div class="product-category">Фрезерные станки</div>
                    <div class="product-status-line">
                        <span class="product-condition">Б.У</span>
                        <span class="product-availability">В наличии</span>
                    </div>
                    <div class="product-price">950 000 ₽</div>
                    <a href="/advertise" class="product-button">Подробнее</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="/assets/js/catalog.js"></script>
@endpush
@endsection
