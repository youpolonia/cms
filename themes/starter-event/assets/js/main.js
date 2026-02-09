/**
 * Summit 2026 — Event Theme JS
 * Countdown Timer, Mobile Nav, Scroll Animations
 */
(function() {
    'use strict';

    // ─── COUNTDOWN TIMER ────────────────────────────────────────
    function initCountdown() {
        const countdownEls = document.querySelectorAll('.countdown');
        if (!countdownEls.length) return;

        // Set event date to ~3 months from now
        const now = new Date();
        const eventDate = new Date(now.getFullYear(), now.getMonth() + 3, 15, 9, 0, 0);
        // If event already passed, push to next year
        if (eventDate < now) {
            eventDate.setFullYear(eventDate.getFullYear() + 1);
        }

        function update() {
            const diff = eventDate - new Date();
            if (diff <= 0) {
                countdownEls.forEach(el => {
                    el.innerHTML = '<div class="countdown-live">🎉 Event is LIVE!</div>';
                });
                return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            countdownEls.forEach(el => {
                const daysEl = el.querySelector('[data-days]');
                const hoursEl = el.querySelector('[data-hours]');
                const minutesEl = el.querySelector('[data-minutes]');
                const secondsEl = el.querySelector('[data-seconds]');

                if (daysEl) animateValue(daysEl, days);
                if (hoursEl) animateValue(hoursEl, hours);
                if (minutesEl) animateValue(minutesEl, minutes);
                if (secondsEl) animateValue(secondsEl, seconds);
            });
        }

        function animateValue(el, value) {
            const str = String(value).padStart(2, '0');
            if (el.textContent !== str) {
                el.classList.add('flip');
                el.textContent = str;
                setTimeout(() => el.classList.remove('flip'), 300);
            }
        }

        update();
        setInterval(update, 1000);
    }

    // ─── MOBILE MENU ────────────────────────────────────────────
    function initMobileMenu() {
        const toggle = document.querySelector('.mobile-toggle');
        const nav = document.querySelector('.main-nav');

        if (!toggle || !nav) return;

        toggle.addEventListener('click', function() {
            nav.classList.toggle('is-open');
            toggle.classList.toggle('is-active');
            toggle.textContent = nav.classList.contains('is-open') ? '✕' : '☰';
        });

        // Close on link click
        nav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                nav.classList.remove('is-open');
                toggle.classList.remove('is-active');
                toggle.textContent = '☰';
            });
        });
    }

    // ─── HEADER SCROLL ──────────────────────────────────────────
    function initHeaderScroll() {
        const header = document.querySelector('.site-header');
        if (!header) return;

        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 80) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }, { passive: true });
    }

    // ─── SMOOTH SCROLL ──────────────────────────────────────────
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    // ─── SCROLL ANIMATIONS ──────────────────────────────────────
    function initScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-on-scroll, .speaker-card, .timeline-item, .sponsor-item, .article-card').forEach(el => {
            observer.observe(el);
        });
    }

    // ─── ANIMATED BACKGROUND ────────────────────────────────────
    function initAnimatedBg() {
        const hero = document.querySelector('.hero-section');
        if (!hero) return;

        let angle = 0;
        function animateBg() {
            angle = (angle + 0.2) % 360;
            hero.style.background = 'linear-gradient(' + angle + 'deg, #1e1b4b 0%, #2d1b69 25%, #1e1b4b 50%, #1b1444 75%, #1e1b4b 100%)';
            requestAnimationFrame(animateBg);
        }
        animateBg();
    }

    // ─── INIT ───────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        initCountdown();
        initMobileMenu();
        initHeaderScroll();
        initSmoothScroll();
        initScrollAnimations();
        initAnimatedBg();
    });
})();
