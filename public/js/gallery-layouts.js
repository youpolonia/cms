/**
 * Gallery — Scroll Reveal + Lightbox + Carousel
 * 2025/2026 style: IntersectionObserver driven reveals, smooth transitions
 */
(function() {
    'use strict';

    // ===== SCROLL REVEAL =====
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    // ===== LIGHTBOX =====
    let lbImages = [], lbIdx = 0;

    function createLightbox() {
        if (document.getElementById('cms-gallery-lightbox')) return;
        const el = document.createElement('div');
        el.id = 'cms-gallery-lightbox';
        el.className = 'gallery-lightbox-overlay';
        el.innerHTML = `
            <button class="gallery-lightbox-close" aria-label="Close">&times;</button>
            <button class="gallery-lightbox-prev" aria-label="Previous">&#8249;</button>
            <button class="gallery-lightbox-next" aria-label="Next">&#8250;</button>
            <img src="" alt="">
            <div class="gallery-lightbox-caption"></div>
            <div class="gallery-lightbox-counter"></div>
        `;
        document.body.appendChild(el);

        el.querySelector('.gallery-lightbox-close').addEventListener('click', closeLb);
        el.querySelector('.gallery-lightbox-prev').addEventListener('click', e => { e.stopPropagation(); navLb(-1); });
        el.querySelector('.gallery-lightbox-next').addEventListener('click', e => { e.stopPropagation(); navLb(1); });
        el.addEventListener('click', e => { if (e.target === el) closeLb(); });

        document.addEventListener('keydown', e => {
            const lb = document.getElementById('cms-gallery-lightbox');
            if (!lb || !lb.classList.contains('active')) return;
            if (e.key === 'Escape') closeLb();
            if (e.key === 'ArrowLeft') navLb(-1);
            if (e.key === 'ArrowRight') navLb(1);
        });

        // Touch swipe
        let tx = 0;
        el.addEventListener('touchstart', e => { tx = e.changedTouches[0].screenX; }, { passive: true });
        el.addEventListener('touchend', e => {
            const d = e.changedTouches[0].screenX - tx;
            if (Math.abs(d) > 50) navLb(d > 0 ? -1 : 1);
        }, { passive: true });
    }

    function openLb(images, idx) {
        createLightbox();
        lbImages = images;
        lbIdx = idx;
        showLb();
        document.getElementById('cms-gallery-lightbox').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeLb() {
        const lb = document.getElementById('cms-gallery-lightbox');
        if (lb) lb.classList.remove('active');
        document.body.style.overflow = '';
    }

    function navLb(dir) {
        lbIdx = (lbIdx + dir + lbImages.length) % lbImages.length;
        showLb();
    }

    function showLb() {
        const lb = document.getElementById('cms-gallery-lightbox');
        if (!lb) return;
        const img = lbImages[lbIdx];
        const lbImg = lb.querySelector('img');
        // Animate image change
        lbImg.style.opacity = '0';
        lbImg.style.transform = 'scale(0.95)';
        setTimeout(() => {
            lbImg.src = img.src;
            lbImg.alt = img.alt || '';
            lbImg.style.opacity = '1';
            lbImg.style.transform = 'scale(1)';
        }, 150);
        lb.querySelector('.gallery-lightbox-caption').textContent = img.title || img.alt || '';
        lb.querySelector('.gallery-lightbox-counter').textContent =
            (lbIdx + 1) + ' / ' + lbImages.length;
        lb.querySelector('.gallery-lightbox-prev').style.display = lbImages.length > 1 ? '' : 'none';
        lb.querySelector('.gallery-lightbox-next').style.display = lbImages.length > 1 ? '' : 'none';
        lb.querySelector('.gallery-lightbox-counter').style.display = lbImages.length > 1 ? '' : 'none';
    }

    // ===== INIT =====
    function init() {
        // Scroll reveal all gallery items
        document.querySelectorAll('.gallery-item').forEach(item => observer.observe(item));

        // Lightbox bindings
        document.querySelectorAll('.gallery-section').forEach(section => {
            const items = section.querySelectorAll('.gallery-item');
            if (!items.length) return;
            const images = [];
            items.forEach(item => {
                const img = item.querySelector('img');
                const cap = item.querySelector('.gallery-caption span');
                if (img) images.push({
                    src: item.dataset.src || img.src,
                    alt: img.alt || '',
                    title: cap ? cap.textContent : (img.alt || '')
                });
            });
            items.forEach((item, i) => {
                item.addEventListener('click', () => openLb(images, i));
            });
        });

        // Carousel: drag to scroll + buttons
        document.querySelectorAll('.gallery-layout-carousel').forEach(carousel => {
            const track = carousel.querySelector('.gallery-carousel-track');
            if (!track) return;

            // Button nav
            const prev = carousel.querySelector('[data-dir="prev"]');
            const next = carousel.querySelector('[data-dir="next"]');
            const scrollAmt = 440;
            if (prev) prev.addEventListener('click', () => track.scrollBy({ left: -scrollAmt, behavior: 'smooth' }));
            if (next) next.addEventListener('click', () => track.scrollBy({ left: scrollAmt, behavior: 'smooth' }));

            // Drag to scroll
            let isDown = false, startX, scrollLeft;
            track.addEventListener('mousedown', e => {
                isDown = true;
                startX = e.pageX - track.offsetLeft;
                scrollLeft = track.scrollLeft;
                track.style.cursor = 'grabbing';
            });
            track.addEventListener('mouseleave', () => { isDown = false; track.style.cursor = 'grab'; });
            track.addEventListener('mouseup', () => { isDown = false; track.style.cursor = 'grab'; });
            track.addEventListener('mousemove', e => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - track.offsetLeft;
                track.scrollLeft = scrollLeft - (x - startX) * 1.5;
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
