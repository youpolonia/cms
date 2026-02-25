/**
 * Jessie CMS Demo Theme — Main JS
 */
(function() {
    'use strict';

    // Header scroll
    const header = document.getElementById('siteHeader');
    if (header) {
        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const y = window.scrollY;
            header.classList.toggle('scrolled', y > 50);
            lastScroll = y;
        }, { passive: true });
    }

    // Mobile menu
    const burger = document.getElementById('mobileToggle');
    const nav = document.querySelector('.jd-nav');
    const overlay = document.getElementById('mobileOverlay');
    if (burger && nav) {
        burger.addEventListener('click', () => {
            nav.classList.toggle('open');
            document.body.style.overflow = nav.classList.contains('open') ? 'hidden' : '';
        });
        if (overlay) {
            overlay.addEventListener('click', () => {
                nav.classList.remove('open');
                document.body.style.overflow = '';
            });
        }
        nav.querySelectorAll('.jd-nav-link').forEach(link => {
            link.addEventListener('click', () => {
                nav.classList.remove('open');
                document.body.style.overflow = '';
            });
        });
    }

    // FAQ accordion
    document.querySelectorAll('.jd-faq-question').forEach(btn => {
        btn.addEventListener('click', () => {
            const item = btn.closest('.jd-faq-item');
            const wasOpen = item.classList.contains('open');
            // Close all
            document.querySelectorAll('.jd-faq-item.open').forEach(i => i.classList.remove('open'));
            if (!wasOpen) item.classList.add('open');
        });
    });

    // Scroll animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.jd-fade-up').forEach(el => observer.observe(el));

    // Counter animation
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.dataset.count, 10);
                const suffix = el.dataset.suffix || '';
                const prefix = el.dataset.prefix || '';
                const duration = 2000;
                const start = performance.now();
                
                function update(now) {
                    const elapsed = now - start;
                    const progress = Math.min(elapsed / duration, 1);
                    // Ease out cubic
                    const eased = 1 - Math.pow(1 - progress, 3);
                    const current = Math.round(eased * target);
                    el.textContent = prefix + current.toLocaleString() + suffix;
                    if (progress < 1) requestAnimationFrame(update);
                }
                requestAnimationFrame(update);
                counterObserver.unobserve(el);
            }
        });
    }, { threshold: 0.3 });

    document.querySelectorAll('[data-count]').forEach(el => counterObserver.observe(el));

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const target = document.querySelector(a.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

})();
