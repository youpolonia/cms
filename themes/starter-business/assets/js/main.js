/**
 * Starter Business Theme — Main JS
 * Corporate interactions and UI enhancements
 */
(function () {
    'use strict';

    /* ========================================
       MOBILE NAVIGATION
    ======================================== */
    const mobileToggle = document.getElementById('mobile-toggle');
    const mainNav = document.getElementById('main-nav');
    const siteHeader = document.getElementById('site-header');

    if (mobileToggle && mainNav) {
        mobileToggle.addEventListener('click', function () {
            const isOpen = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isOpen);
            mainNav.classList.toggle('nav-open');
            document.body.classList.toggle('nav-active');
        });

        // Close menu on link click
        mainNav.querySelectorAll('.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                mobileToggle.setAttribute('aria-expanded', 'false');
                mainNav.classList.remove('nav-open');
                document.body.classList.remove('nav-active');
            });
        });

        // Close on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && mainNav.classList.contains('nav-open')) {
                mobileToggle.setAttribute('aria-expanded', 'false');
                mainNav.classList.remove('nav-open');
                document.body.classList.remove('nav-active');
            }
        });
    }

    /* ========================================
       STICKY HEADER
    ======================================== */
    if (siteHeader) {
        let lastScroll = 0;
        const scrollThreshold = 80;

        function handleScroll() {
            const currentScroll = window.pageYOffset;

            if (currentScroll > scrollThreshold) {
                siteHeader.classList.add('header-scrolled');
            } else {
                siteHeader.classList.remove('header-scrolled');
            }

            if (currentScroll > lastScroll && currentScroll > 400) {
                siteHeader.classList.add('header-hidden');
            } else {
                siteHeader.classList.remove('header-hidden');
            }

            lastScroll = currentScroll;
        }

        window.addEventListener('scroll', handleScroll, { passive: true });
        handleScroll();
    }

    /* ========================================
       ANIMATED COUNTER (Stats)
    ======================================== */
    function animateCounters() {
        const counters = document.querySelectorAll('[data-count]');
        counters.forEach(function (counter) {
            if (counter.dataset.animated) return;

            const target = parseInt(counter.getAttribute('data-count'), 10);
            const suffix = counter.getAttribute('data-suffix') || '';
            const prefix = counter.getAttribute('data-prefix') || '';
            const duration = 2000;
            const start = 0;
            const startTime = performance.now();

            function easeOutCubic(t) {
                return 1 - Math.pow(1 - t, 3);
            }

            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const eased = easeOutCubic(progress);
                const current = Math.floor(start + (target - start) * eased);

                counter.textContent = prefix + current.toLocaleString() + suffix;

                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }

            counter.dataset.animated = 'true';
            requestAnimationFrame(update);
        });
    }

    /* ========================================
       INTERSECTION OBSERVER — Fade-in & Counters
    ======================================== */
    const observerOptions = {
        threshold: 0.15,
        rootMargin: '0px 0px -50px 0px'
    };

    const fadeObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                fadeObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.fade-in, .fade-in-up, .fade-in-left, .fade-in-right').forEach(function (el) {
        fadeObserver.observe(el);
    });

    // Counter observer
    const counterObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                animateCounters();
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });

    const statsSection = document.querySelector('.stats-row');
    if (statsSection) {
        counterObserver.observe(statsSection);
    }

    /* ========================================
       TESTIMONIAL CAROUSEL
    ======================================== */
    const carousel = document.querySelector('.testimonials-carousel');
    if (carousel) {
        const track = carousel.querySelector('.carousel-track');
        const cards = carousel.querySelectorAll('.testimonial-card');
        const prevBtn = carousel.querySelector('.carousel-prev');
        const nextBtn = carousel.querySelector('.carousel-next');
        const dotsContainer = carousel.querySelector('.carousel-dots');

        if (track && cards.length > 0) {
            let currentIndex = 0;
            let cardsPerView = getCardsPerView();
            let maxIndex = Math.max(0, cards.length - cardsPerView);

            function getCardsPerView() {
                if (window.innerWidth < 640) return 1;
                if (window.innerWidth < 1024) return 2;
                return 3;
            }

            function buildDots() {
                if (!dotsContainer) return;
                dotsContainer.innerHTML = '';
                const dotCount = maxIndex + 1;
                for (let i = 0; i < dotCount; i++) {
                    const dot = document.createElement('button');
                    dot.classList.add('carousel-dot');
                    dot.setAttribute('aria-label', 'Slide ' + (i + 1));
                    if (i === 0) dot.classList.add('active');
                    dot.addEventListener('click', function () {
                        goTo(i);
                    });
                    dotsContainer.appendChild(dot);
                }
            }

            function goTo(index) {
                currentIndex = Math.max(0, Math.min(index, maxIndex));
                const cardWidth = cards[0].offsetWidth;
                const gap = parseInt(getComputedStyle(track).gap) || 24;
                const offset = currentIndex * (cardWidth + gap);
                track.style.transform = 'translateX(-' + offset + 'px)';

                if (dotsContainer) {
                    dotsContainer.querySelectorAll('.carousel-dot').forEach(function (dot, i) {
                        dot.classList.toggle('active', i === currentIndex);
                    });
                }
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function () {
                    goTo(currentIndex - 1);
                });
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', function () {
                    goTo(currentIndex + 1);
                });
            }

            buildDots();

            window.addEventListener('resize', function () {
                cardsPerView = getCardsPerView();
                maxIndex = Math.max(0, cards.length - cardsPerView);
                buildDots();
                goTo(Math.min(currentIndex, maxIndex));
            });

            // Touch/swipe support
            let touchStartX = 0;
            let touchEndX = 0;

            track.addEventListener('touchstart', function (e) {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });

            track.addEventListener('touchend', function (e) {
                touchEndX = e.changedTouches[0].screenX;
                const diff = touchStartX - touchEndX;
                if (Math.abs(diff) > 50) {
                    if (diff > 0) goTo(currentIndex + 1);
                    else goTo(currentIndex - 1);
                }
            }, { passive: true });

            // Auto-play
            let autoPlay = setInterval(function () {
                if (currentIndex >= maxIndex) goTo(0);
                else goTo(currentIndex + 1);
            }, 5000);

            carousel.addEventListener('mouseenter', function () {
                clearInterval(autoPlay);
            });
            carousel.addEventListener('mouseleave', function () {
                autoPlay = setInterval(function () {
                    if (currentIndex >= maxIndex) goTo(0);
                    else goTo(currentIndex + 1);
                }, 5000);
            });
        }
    }

    /* ========================================
       SMOOTH SCROLL for Anchor Links
    ======================================== */
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            const target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                const headerH = siteHeader ? siteHeader.offsetHeight : 0;
                const top = target.getBoundingClientRect().top + window.pageYOffset - headerH - 20;
                window.scrollTo({ top: top, behavior: 'smooth' });
            }
        });
    });

    /* ========================================
       NEWSLETTER FORM
    ======================================== */
    const nlForm = document.querySelector('.newsletter-form');
    if (nlForm) {
        nlForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const input = this.querySelector('input[type="email"]');
            const btn = this.querySelector('button');
            if (input && input.value) {
                btn.innerHTML = '<i class="fas fa-check"></i> Subscribed!';
                btn.disabled = true;
                input.disabled = true;
                setTimeout(function () {
                    btn.innerHTML = 'Subscribe <i class="fas fa-arrow-right"></i>';
                    btn.disabled = false;
                    input.disabled = false;
                    input.value = '';
                }, 3000);
            }
        });
    }

    /* ========================================
       SEARCH FORM (404 page)
    ======================================== */
    const searchForm = document.querySelector('.search-form-404');
    if (searchForm) {
        searchForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const q = this.querySelector('input[type="search"], input[type="text"]');
            if (q && q.value.trim()) {
                window.location.href = '/search?q=' + encodeURIComponent(q.value.trim());
            }
        });
    }

})();
