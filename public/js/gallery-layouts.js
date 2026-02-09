/**
 * Gallery Layouts — Lightbox + Carousel controls
 * Shared JS for all CMS themes
 */
(function() {
    'use strict';

    // ===== LIGHTBOX =====
    let lightboxImages = [];
    let lightboxIndex = 0;

    function createLightbox() {
        if (document.getElementById('cms-gallery-lightbox')) return;

        const overlay = document.createElement('div');
        overlay.id = 'cms-gallery-lightbox';
        overlay.className = 'gallery-lightbox-overlay';
        overlay.innerHTML = `
            <button class="gallery-lightbox-close" aria-label="Close">&times;</button>
            <button class="gallery-lightbox-prev" aria-label="Previous">&#8249;</button>
            <button class="gallery-lightbox-next" aria-label="Next">&#8250;</button>
            <img src="" alt="">
            <div class="gallery-lightbox-caption"></div>
            <div class="gallery-lightbox-counter"></div>
        `;
        document.body.appendChild(overlay);

        overlay.querySelector('.gallery-lightbox-close').addEventListener('click', closeLightbox);
        overlay.querySelector('.gallery-lightbox-prev').addEventListener('click', function(e) {
            e.stopPropagation();
            navigateLightbox(-1);
        });
        overlay.querySelector('.gallery-lightbox-next').addEventListener('click', function(e) {
            e.stopPropagation();
            navigateLightbox(1);
        });
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeLightbox();
        });

        document.addEventListener('keydown', function(e) {
            const lb = document.getElementById('cms-gallery-lightbox');
            if (!lb || !lb.classList.contains('active')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') navigateLightbox(-1);
            if (e.key === 'ArrowRight') navigateLightbox(1);
        });

        // Touch swipe support
        let touchStartX = 0;
        overlay.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });
        overlay.addEventListener('touchend', function(e) {
            const diff = e.changedTouches[0].screenX - touchStartX;
            if (Math.abs(diff) > 50) {
                navigateLightbox(diff > 0 ? -1 : 1);
            }
        }, { passive: true });
    }

    function openLightbox(images, index) {
        createLightbox();
        lightboxImages = images;
        lightboxIndex = index;
        showLightboxImage();
        document.getElementById('cms-gallery-lightbox').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        const lb = document.getElementById('cms-gallery-lightbox');
        if (lb) lb.classList.remove('active');
        document.body.style.overflow = '';
    }

    function navigateLightbox(dir) {
        lightboxIndex = (lightboxIndex + dir + lightboxImages.length) % lightboxImages.length;
        showLightboxImage();
    }

    function showLightboxImage() {
        const lb = document.getElementById('cms-gallery-lightbox');
        if (!lb) return;
        const img = lightboxImages[lightboxIndex];
        lb.querySelector('img').src = img.src;
        lb.querySelector('img').alt = img.alt || '';
        lb.querySelector('.gallery-lightbox-caption').textContent = img.title || img.alt || '';
        lb.querySelector('.gallery-lightbox-counter').textContent =
            (lightboxIndex + 1) + ' / ' + lightboxImages.length;

        // Hide nav if only one image
        lb.querySelector('.gallery-lightbox-prev').style.display = lightboxImages.length > 1 ? '' : 'none';
        lb.querySelector('.gallery-lightbox-next').style.display = lightboxImages.length > 1 ? '' : 'none';
        lb.querySelector('.gallery-lightbox-counter').style.display = lightboxImages.length > 1 ? '' : 'none';
    }

    // ===== INIT ALL GALLERIES =====
    function initGalleries() {
        document.querySelectorAll('.gallery-section').forEach(function(section) {
            const items = section.querySelectorAll('.gallery-item');
            if (!items.length) return;

            // Collect image data for lightbox
            const images = [];
            items.forEach(function(item) {
                const img = item.querySelector('img');
                const cap = item.querySelector('.gallery-caption span');
                if (img) {
                    images.push({
                        src: item.dataset.src || img.src,
                        alt: img.alt || '',
                        title: cap ? cap.textContent : (img.alt || '')
                    });
                }
            });

            // Bind click to open lightbox
            items.forEach(function(item, i) {
                item.addEventListener('click', function() {
                    openLightbox(images, i);
                });
            });
        });

        // ===== CAROUSEL CONTROLS =====
        document.querySelectorAll('.gallery-layout-carousel').forEach(function(carousel) {
            const track = carousel.querySelector('.gallery-carousel-track');
            if (!track) return;

            const prevBtn = carousel.querySelector('.gallery-carousel-btn[data-dir="prev"]');
            const nextBtn = carousel.querySelector('.gallery-carousel-btn[data-dir="next"]');

            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    track.scrollBy({ left: -370, behavior: 'smooth' });
                });
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    track.scrollBy({ left: 370, behavior: 'smooth' });
                });
            }
        });
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGalleries);
    } else {
        initGalleries();
    }
})();
