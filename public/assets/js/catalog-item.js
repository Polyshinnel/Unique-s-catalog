document.addEventListener('DOMContentLoaded', function() {
    const gallery = {
        images: [],
        currentIndex: 0,
        
        init() {
            this.collectImages();
            this.createLightbox();
            this.attachEvents();
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
            this.lightbox.querySelector('.lightbox-overlay').addEventListener('click', () => this.close());
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
        }
    };
    
    gallery.init();
});



