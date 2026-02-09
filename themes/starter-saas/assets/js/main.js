/**
 * Starter SaaS Theme — JavaScript
 */
(function() {
    'use strict';

    // Header scroll effect
    const header = document.getElementById('site-header');
    if (header) {
        let lastScroll = 0;
        window.addEventListener('scroll', function() {
            const scrollY = window.scrollY;
            if (scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            lastScroll = scrollY;
        }, { passive: true });
    }

    // Mobile menu toggle
    const mobileToggle = document.getElementById('mobile-toggle');
    const navMain = document.getElementById('nav-main');
    if (mobileToggle && navMain) {
        mobileToggle.addEventListener('click', function() {
            navMain.classList.toggle('open');
            const icon = mobileToggle.querySelector('i');
            if (navMain.classList.contains('open')) {
                icon.className = 'fas fa-times';
            } else {
                icon.className = 'fas fa-bars';
            }
        });

        // Close on link click
        navMain.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                navMain.classList.remove('open');
                mobileToggle.querySelector('i').className = 'fas fa-bars';
            });
        });
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Intersection Observer for fade-in animations
    const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.feature-card, .testimonial-card, .pricing-card, .showcase-content, .showcase-visual').forEach(function(el) {
        el.classList.add('fade-in');
        observer.observe(el);
    });

    // Stats counter animation
    document.querySelectorAll('.stat-number').forEach(function(stat) {
        const observer2 = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    stat.classList.add('counted');
                    observer2.unobserve(stat);
                }
            });
        }, { threshold: 0.5 });
        observer2.observe(stat);
    });
})();
