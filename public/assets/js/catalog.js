const priceSlider = document.getElementById('priceSlider');
const priceValue = document.getElementById('priceValue');

priceSlider.addEventListener('input', function() {
    const value = parseInt(this.value).toLocaleString('ru-RU');
    priceValue.textContent = value + ' ₽';
});

let selectedCategory = null;

const categoryHeaders = document.querySelectorAll('.category-header');
categoryHeaders.forEach(header => {
    const toggleIcon = header.querySelector('.category-toggle');
    const categoryName = header.querySelector('.category-name');
    const subcategories = header.nextElementSibling;
    
    toggleIcon.addEventListener('click', function(e) {
        e.stopPropagation();
        subcategories.classList.toggle('active');
        toggleIcon.textContent = subcategories.classList.contains('active') ? '−' : '+';
    });
    
    categoryName.addEventListener('click', function(e) {
        e.stopPropagation();
        
        if (selectedCategory === header) {
            header.classList.remove('selected');
            selectedCategory = null;
            console.log('Категория снята');
        } else {
            if (selectedCategory) {
                selectedCategory.classList.remove('selected');
            }
            document.querySelectorAll('.subcategory-link.selected').forEach(sub => {
                sub.classList.remove('selected');
            });
            
            header.classList.add('selected');
            selectedCategory = header;
            console.log('Выбрана категория:', header.dataset.category);
        }
    });
});

const subcategoryLinks = document.querySelectorAll('.subcategory-link');
subcategoryLinks.forEach(link => {
    link.addEventListener('click', function() {
        if (selectedCategory) {
            selectedCategory.classList.remove('selected');
            selectedCategory = null;
        }
        
        if (this.classList.contains('selected')) {
            this.classList.remove('selected');
            console.log('Подкатегория снята');
        } else {
            document.querySelectorAll('.subcategory-link.selected').forEach(sub => {
                sub.classList.remove('selected');
            });
            
            this.classList.add('selected');
            console.log('Выбрана подкатегория:', this.dataset.category);
        }
    });
});

const searchButton = document.getElementById('searchButton');
const searchInput = document.getElementById('searchInput');

searchButton.addEventListener('click', function() {
    const query = searchInput.value.trim();
    if (query) {
        console.log('Поиск:', query);
    }
});

searchInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchButton.click();
    }
});