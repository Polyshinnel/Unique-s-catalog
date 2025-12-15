const priceSliderMin = document.getElementById('priceSliderMin');
const priceSliderMax = document.getElementById('priceSliderMax');
const priceInputMinField = document.getElementById('priceInputMin');
const priceInputMaxField = document.getElementById('priceInputMax');
const priceMinInput = document.getElementById('priceMinInput');
const priceMaxInput = document.getElementById('priceMaxInput');
const priceSliderContainer = document.querySelector('.price-slider-container');

function updatePriceValues(minValue, maxValue) {
    const minLimit = parseInt(priceSliderMin.min);
    const maxLimit = parseInt(priceSliderMax.max);
    
    // Убеждаемся, что значения в пределах допустимого диапазона
    minValue = Math.max(minLimit, Math.min(maxLimit, minValue));
    maxValue = Math.max(minLimit, Math.min(maxLimit, maxValue));
    
    // Убеждаемся, что минимальное значение не превышает максимальное
    if (minValue > maxValue) {
        const temp = minValue;
        minValue = maxValue;
        maxValue = temp;
    }
    
    // Обновляем все элементы
    if (priceSliderMin) priceSliderMin.value = minValue;
    if (priceSliderMax) priceSliderMax.value = maxValue;
    if (priceInputMinField) priceInputMinField.value = minValue;
    if (priceInputMaxField) priceInputMaxField.value = maxValue;
    if (priceMinInput) priceMinInput.value = minValue;
    if (priceMaxInput) priceMaxInput.value = maxValue;
    
    // Обновляем визуальное отображение диапазона
    if (priceSliderContainer) {
        const range = maxLimit - minLimit;
        const minPercent = ((minValue - minLimit) / range) * 100;
        const maxPercent = ((maxValue - minLimit) / range) * 100;
        
        // Создаем градиент для визуального отображения выбранного диапазона
        const sliderBg = `linear-gradient(to right, 
            #e0e0e0 0%, 
            #e0e0e0 ${minPercent}%, 
            #133E71 ${minPercent}%, 
            #133E71 ${maxPercent}%, 
            #e0e0e0 ${maxPercent}%, 
            #e0e0e0 100%)`;
        
        priceSliderContainer.style.background = sliderBg;
    }
}

function updatePriceSlider() {
    if (!priceSliderMin || !priceSliderMax) return;
    
    const minValue = parseInt(priceSliderMin.value);
    const maxValue = parseInt(priceSliderMax.value);
    
    updatePriceValues(minValue, maxValue);
}

if (priceSliderMin && priceSliderMax) {
    priceSliderMin.addEventListener('input', updatePriceSlider);
    priceSliderMax.addEventListener('input', updatePriceSlider);
    
    // Инициализация при загрузке страницы
    updatePriceSlider();
}

// Обработчики для инпутов
if (priceInputMinField && priceInputMaxField) {
    // Автоматическое выделение текста при фокусе
    priceInputMinField.addEventListener('focus', function() {
        this.select();
    });
    
    priceInputMaxField.addEventListener('focus', function() {
        this.select();
    });
    
    // Выделение при клике
    priceInputMinField.addEventListener('click', function() {
        this.select();
    });
    
    priceInputMaxField.addEventListener('click', function() {
        this.select();
    });
    
    priceInputMinField.addEventListener('input', function() {
        const minValue = parseInt(this.value) || parseInt(priceSliderMin.min);
        const maxValue = parseInt(priceInputMaxField.value) || parseInt(priceSliderMax.max);
        updatePriceValues(minValue, maxValue);
    });
    
    priceInputMaxField.addEventListener('input', function() {
        const minValue = parseInt(priceInputMinField.value) || parseInt(priceSliderMin.min);
        const maxValue = parseInt(this.value) || parseInt(priceSliderMax.max);
        updatePriceValues(minValue, maxValue);
    });
    
    // Обработка Enter в инпутах
    priceInputMinField.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            this.blur();
        }
    });
    
    priceInputMaxField.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            this.blur();
        }
    });
}

const categoryHeaders = document.querySelectorAll('.category-header');
categoryHeaders.forEach(header => {
    const toggleIcon = header.querySelector('.category-toggle');
    const categoryName = header.querySelector('.category-name');
    const subcategories = header.nextElementSibling;
    const categoryId = header.dataset.category;
    
    // Проверяем, есть ли подкатегории (элемент с классом category-subcategories)
    const hasSubcategories = subcategories && subcategories.classList.contains('category-subcategories');
    
    // Если есть подкатегории, обрабатываем раскрытие/сворачивание
    if (hasSubcategories && toggleIcon) {
        toggleIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            subcategories.classList.toggle('active');
            toggleIcon.textContent = subcategories.classList.contains('active') ? '−' : '+';
        });
        
        // Клик по названию категории - только раскрытие/сворачивание
        if (categoryName) {
            categoryName.addEventListener('click', function(e) {
                e.stopPropagation();
                subcategories.classList.toggle('active');
                toggleIcon.textContent = subcategories.classList.contains('active') ? '−' : '+';
            });
        }
        
        // Клик по всей области заголовка категории - раскрытие/сворачивание
        header.addEventListener('click', function(e) {
            if (e.target === header || (!e.target.classList.contains('category-toggle') && !e.target.classList.contains('category-name'))) {
                subcategories.classList.toggle('active');
                toggleIcon.textContent = subcategories.classList.contains('active') ? '−' : '+';
            }
        });
    } else {
        // Если нет подкатегорий, обрабатываем выбор категории
        // При загрузке страницы проверяем, выбрана ли эта категория
        const urlParams = new URLSearchParams(window.location.search);
        const selectedCategoryId = urlParams.get('category');
        if (selectedCategoryId == categoryId) {
            header.classList.add('selected');
        }
        
        header.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Снимаем выбор со всех других категорий и подкатегорий
            document.querySelectorAll('.category-header').forEach(h => {
                h.classList.remove('selected');
            });
            document.querySelectorAll('.subcategory-link').forEach(sub => {
                sub.classList.remove('selected');
                const radioInput = sub.querySelector('input[type="radio"]');
                if (radioInput) {
                    radioInput.checked = false;
                }
            });
            
            // Удаляем все скрытые поля категорий
            document.querySelectorAll('input[name="category"][type="hidden"]').forEach(input => {
                input.remove();
            });
            
            if (header.classList.contains('selected')) {
                // Если уже выбрана, снимаем выбор
                header.classList.remove('selected');
            } else {
                // Выбираем текущую категорию
                header.classList.add('selected');
                
                // Создаем скрытое поле для категории
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'category';
                hiddenInput.value = categoryId;
                document.getElementById('filterForm').appendChild(hiddenInput);
            }
        });
    }
});

const subcategoryLinks = document.querySelectorAll('.subcategory-link');
subcategoryLinks.forEach(link => {
    const radioInput = link.querySelector('input[type="radio"]');
    
    // При загрузке страницы, если подкатегория выбрана, отмечаем её и раскрываем родительскую категорию
    if (radioInput && radioInput.checked) {
        link.classList.add('selected');
        // Находим родительскую категорию и раскрываем её
        const categoryItem = link.closest('.category-item');
        if (categoryItem) {
            const subcategories = categoryItem.querySelector('.category-subcategories');
            const toggleIcon = categoryItem.querySelector('.category-toggle');
            if (subcategories) {
                subcategories.classList.add('active');
                if (toggleIcon) {
                    toggleIcon.textContent = '−';
                }
            }
        }
    }
    
    link.addEventListener('click', function(e) {
        if (e.target.tagName === 'INPUT') return;
        
        // Снимаем выбор со всех категорий без подкатегорий
        document.querySelectorAll('.category-header').forEach(h => {
            const subcats = h.nextElementSibling;
            if (!subcats || !subcats.classList.contains('category-subcategories')) {
                h.classList.remove('selected');
            }
        });
        
        // Удаляем все скрытые поля категорий
        document.querySelectorAll('input[name="category"][type="hidden"]').forEach(input => {
            input.remove();
        });
        
        if (radioInput && radioInput.checked) {
            // Если уже выбрана, снимаем выбор
            radioInput.checked = false;
            link.classList.remove('selected');
        } else {
            // Снимаем выбор со всех других подкатегорий
            document.querySelectorAll('.subcategory-link input[type="radio"]').forEach(input => {
                input.checked = false;
            });
            document.querySelectorAll('.subcategory-link').forEach(sub => {
                sub.classList.remove('selected');
            });
            
            // Выбираем текущую подкатегорию
            if (radioInput) {
                radioInput.checked = true;
            }
            link.classList.add('selected');
            
            // Раскрываем родительскую категорию, если она свернута
            const categoryItem = link.closest('.category-item');
            if (categoryItem) {
                const subcategories = categoryItem.querySelector('.category-subcategories');
                const toggleIcon = categoryItem.querySelector('.category-toggle');
                if (subcategories && !subcategories.classList.contains('active')) {
                    subcategories.classList.add('active');
                    if (toggleIcon) {
                        toggleIcon.textContent = '−';
                    }
                }
            }
        }
    });
    
    // Обработка изменения radio input
    if (radioInput) {
        radioInput.addEventListener('change', function() {
            if (this.checked) {
                // Снимаем выбор со всех категорий без подкатегорий
                document.querySelectorAll('.category-header').forEach(h => {
                    const subcats = h.nextElementSibling;
                    if (!subcats || !subcats.classList.contains('category-subcategories')) {
                        h.classList.remove('selected');
                    }
                });
                
                document.querySelectorAll('.subcategory-link').forEach(sub => {
                    sub.classList.remove('selected');
                });
                link.classList.add('selected');
            }
        });
    }
});

const searchButton = document.getElementById('searchButton');
const searchInput = document.getElementById('searchInput');

if (searchButton && searchInput) {
    searchButton.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('filterForm').submit();
    });

    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('filterForm').submit();
        }
    });
}

// Мобильные фильтры
const mobileFiltersToggle = document.getElementById('mobileFiltersToggle');
const mobileFiltersContainer = document.getElementById('mobileFiltersContainer');

if (mobileFiltersToggle && mobileFiltersContainer) {
    mobileFiltersToggle.addEventListener('click', function() {
        mobileFiltersContainer.classList.toggle('active');
        this.classList.toggle('active');
        this.textContent = mobileFiltersContainer.classList.contains('active') ? 'Скрыть фильтры' : 'Фильтр';
    });
}

// Сортировка
const sortSelect = document.getElementById('sortSelect');
if (sortSelect) {
    sortSelect.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
}
