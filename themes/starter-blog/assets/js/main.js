/**
 * Starter Blog Theme — JavaScript
 */
(function() {
    'use strict';

    // Reading progress bar
    const progressBar = document.getElementById('reading-progress');
    if (progressBar) {
        window.addEventListener('scroll', function() {
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
            progressBar.style.width = progress + '%';
        }, { passive: true });
    }

    // Header scroll effect
    const header = document.getElementById('site-header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }, { passive: true });
    }

    // Mobile menu
    const mobileToggle = document.getElementById('mobile-toggle');
    const navMain = document.getElementById('nav-main');
    if (mobileToggle && navMain) {
        mobileToggle.addEventListener('click', function() {
            navMain.classList.toggle('open');
            const icon = mobileToggle.querySelector('i');
            icon.className = navMain.classList.contains('open') ? 'fas fa-times' : 'fas fa-bars';
        });

        navMain.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                navMain.classList.remove('open');
                mobileToggle.querySelector('i').className = 'fas fa-bars';
            });
        });
    }

    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Fade-in animations
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });

    document.querySelectorAll('.post-card, .category-card, .featured-card, .newsletter-card').forEach(function(el) {
        el.classList.add('fade-in');
        observer.observe(el);
    });
})();
