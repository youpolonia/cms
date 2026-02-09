/**
 * AppFlow Theme — Main JS
 */
(function() {
    'use strict';

    /* ── Header scroll effect ── */
    const header = document.querySelector('.site-header');
    if (header) {
        const SCROLL_THRESHOLD = 60;
        let ticking = false;
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    if (window.scrollY > SCROLL_THRESHOLD) {
                        header.classList.add('scrolled');
                    } else {
                        header.classList.remove('scrolled');
                    }
                    ticking = false;
                });
                ticking = true;
            }
        });
    }

    /* ── Mobile menu toggle ── */
    const toggle = document.querySelector('.mobile-toggle');
    const nav = document.querySelector('.main-nav');
    const cta = document.querySelector('.nav-cta');
    if (toggle && nav) {
        toggle.addEventListener('click', function() {
            nav.classList.toggle('open');
            if (cta) cta.classList.toggle('open');
            toggle.setAttribute('aria-expanded', nav.classList.contains('open'));
            toggle.textContent = nav.classList.contains('open') ? '✕' : '☰';
        });
        nav.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                nav.classList.remove('open');
                if (cta) cta.classList.remove('open');
                toggle.textContent = '☰';
            });
        });
    }

    /* ── Smooth scroll for anchor links ── */
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                var offset = header ? header.offsetHeight : 0;
                var top = target.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({ top: top, behavior: 'smooth' });
            }
        });
    });

    /* ── Scroll-in animation observer ── */
    var animEls = document.querySelectorAll('.fade-in-up');
    if (animEls.length && 'IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
        animEls.forEach(function(el) { observer.observe(el); });
    }

    /* ── Testimonial slider ── */
    var slides = document.querySelectorAll('.testimonial-slide');
    var dots = document.querySelectorAll('.testimonial-dot');
    if (slides.length > 1) {
        var current = 0;
        function showSlide(idx) {
            slides.forEach(function(s, i) {
                s.classList.toggle('active', i === idx);
            });
            dots.forEach(function(d, i) {
                d.classList.toggle('active', i === idx);
            });
            current = idx;
        }
        dots.forEach(function(dot, i) {
            dot.addEventListener('click', function() { showSlide(i); });
        });
        setInterval(function() {
            showSlide((current + 1) % slides.length);
        }, 6000);
    }
})();
