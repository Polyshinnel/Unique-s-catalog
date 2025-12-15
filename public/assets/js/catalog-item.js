document.addEventListener('DOMContentLoaded', function() {
    const gallery = {
        images: [],
        currentIndex: 0,
        touchStartX: 0,
        touchEndX: 0,
        touchStartY: 0,
        touchEndY: 0,
        isSwiping: false,
        
        init() {
            this.collectImages();
            this.createLightbox();
            this.attachEvents();
            this.attachSwipeEvents();
        },
        
        collectImages() {
            const mainImage = document.querySelector('.gallery-main-image img');
            const thumbs = document.querySelectorAll('.gallery-thumb img');
            
            if (mainImage) {
                this.images.push({
                    src: mainImage.src,
                    alt: mainImage.alt
                });
            }
            
            thumbs.forEach((thumb, index) => {
                if (index > 0 || !mainImage) {
                    this.images.push({
                        src: thumb.src,
                        alt: thumb.alt
                    });
                }
            });
        },
        
        createLightbox() {
            const lightbox = document.createElement('div');
            lightbox.className = 'lightbox';
            lightbox.innerHTML = `
                <div class="lightbox-overlay"></div>
                <div class="lightbox-content">
                    <button class="lightbox-close" title="Закрыть (ESC)">&times;</button>
                    <button class="lightbox-prev" title="Предыдущее (←)">‹</button>
                    <button class="lightbox-next" title="Следующее (→)">›</button>
                    <div class="lightbox-image-container">
                        <img class="lightbox-image" src="" alt="">
                    </div>
                    <div class="lightbox-counter"></div>
                </div>
            `;
            document.body.appendChild(lightbox);
            
            this.lightbox = lightbox;
            this.lightboxImage = lightbox.querySelector('.lightbox-image');
            this.lightboxCounter = lightbox.querySelector('.lightbox-counter');
        },
        
        attachEvents() {
            const mainImage = document.querySelector('.gallery-main-image img');
            if (mainImage) {
                mainImage.style.cursor = 'pointer';
                mainImage.addEventListener('click', () => this.open(0));
            }
            
            const thumbs = document.querySelectorAll('.gallery-thumb');
            thumbs.forEach((thumb, index) => {
                thumb.style.cursor = 'pointer';
                thumb.addEventListener('click', (e) => {
                    if (!e.target.closest('.gallery-thumb').classList.contains('active')) {
                        this.changeMainImage(index);
                    }
                    this.open(index);
                });
            });
            
            this.lightbox.querySelector('.lightbox-close').addEventListener('click', () => this.close());
            this.lightbox.querySelector('.lightbox-prev').addEventListener('click', () => this.prev());
            this.lightbox.querySelector('.lightbox-next').addEventListener('click', () => this.next());
            
            document.addEventListener('keydown', (e) => {
                if (this.lightbox.classList.contains('active')) {
                    if (e.key === 'Escape') this.close();
                    if (e.key === 'ArrowLeft') this.prev();
                    if (e.key === 'ArrowRight') this.next();
                }
            });
        },
        
        changeMainImage(index) {
            const mainImage = document.querySelector('.gallery-main-image img');
            const thumbs = document.querySelectorAll('.gallery-thumb');
            const selectedThumb = thumbs[index].querySelector('img');
            
            if (mainImage && selectedThumb) {
                mainImage.src = selectedThumb.src;
                mainImage.alt = selectedThumb.alt;
                
                thumbs.forEach(thumb => thumb.classList.remove('active'));
                thumbs[index].classList.add('active');
            }
        },
        
        open(index) {
            this.currentIndex = index;
            this.updateImage();
            this.lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        },
        
        close() {
            this.lightbox.classList.remove('active');
            document.body.style.overflow = '';
        },
        
        next() {
            this.currentIndex = (this.currentIndex + 1) % this.images.length;
            this.updateImage();
        },
        
        prev() {
            this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
            this.updateImage();
        },
        
        updateImage() {
            const image = this.images[this.currentIndex];
            this.lightboxImage.src = image.src;
            this.lightboxImage.alt = image.alt;
            this.lightboxCounter.textContent = `${this.currentIndex + 1} / ${this.images.length}`;
        },
        
        attachSwipeEvents() {
            // Swipe для основной галереи (миниатюры)
            const galleryThumbnails = document.querySelector('.gallery-thumbnails');
            if (galleryThumbnails) {
                this.addSwipeListener(galleryThumbnails, (direction) => {
                    if (direction === 'left') {
                        const thumbs = document.querySelectorAll('.gallery-thumb');
                        const activeThumb = document.querySelector('.gallery-thumb.active');
                        const currentIndex = Array.from(thumbs).indexOf(activeThumb);
                        const nextIndex = (currentIndex + 1) % thumbs.length;
                        thumbs[nextIndex].click();
                    } else if (direction === 'right') {
                        const thumbs = document.querySelectorAll('.gallery-thumb');
                        const activeThumb = document.querySelector('.gallery-thumb.active');
                        const currentIndex = Array.from(thumbs).indexOf(activeThumb);
                        const prevIndex = (currentIndex - 1 + thumbs.length) % thumbs.length;
                        thumbs[prevIndex].click();
                    }
                });
            }
            
            // Swipe для lightbox
            const lightboxContent = this.lightbox.querySelector('.lightbox-content');
            this.addSwipeListener(lightboxContent, (direction) => {
                if (this.lightbox.classList.contains('active')) {
                    if (direction === 'left') {
                        this.next();
                    } else if (direction === 'right') {
                        this.prev();
                    }
                }
            });
        },
        
        addSwipeListener(element, callback) {
            let startX = 0;
            let startY = 0;
            let startTime = 0;
            
            element.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                startTime = Date.now();
                this.isSwiping = false;
            }, { passive: true });
            
            element.addEventListener('touchmove', (e) => {
                if (!startX || !startY) return;
                
                const currentX = e.touches[0].clientX;
                const currentY = e.touches[0].clientY;
                
                const diffX = startX - currentX;
                const diffY = startY - currentY;
                
                // Определяем, горизонтальный ли это свайп
                if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 10) {
                    this.isSwiping = true;
                    // Предотвращаем вертикальную прокрутку на iOS при горизонтальном свайпе
                    e.preventDefault();
                }
            }, { passive: false });
            
            element.addEventListener('touchend', (e) => {
                if (!startX || !startY) return;
                
                const endX = e.changedTouches[0].clientX;
                const endY = e.changedTouches[0].clientY;
                const endTime = Date.now();
                
                const diffX = startX - endX;
                const diffY = startY - endY;
                const diffTime = endTime - startTime;
                
                // Минимальное расстояние для свайпа (50px)
                const minSwipeDistance = 50;
                // Максимальное время для быстрого свайпа (300ms)
                const maxSwipeTime = 300;
                // Вычисляем скорость свайпа
                const velocity = Math.abs(diffX) / diffTime;
                
                // Проверяем, что это горизонтальный свайп
                if (Math.abs(diffX) > Math.abs(diffY)) {
                    // Свайп срабатывает если:
                    // 1. Расстояние больше минимального
                    // 2. Или это быстрый свайп (высокая скорость)
                    if (Math.abs(diffX) > minSwipeDistance || (velocity > 0.5 && diffTime < maxSwipeTime)) {
                        if (diffX > 0) {
                            // Свайп влево (следующее фото)
                            callback('left');
                        } else {
                            // Свайп вправо (предыдущее фото)
                            callback('right');
                        }
                    }
                }
                
                // Сброс значений
                startX = 0;
                startY = 0;
                this.isSwiping = false;
            }, { passive: true });
            
            // Дополнительная обработка для iOS Safari
            // Предотвращаем стандартное поведение bounce/rubber-band эффекта
            element.addEventListener('touchforcechange', (e) => {
                if (this.isSwiping) {
                    e.preventDefault();
                }
            }, { passive: false });
        }
    };
    
    gallery.init();
});




