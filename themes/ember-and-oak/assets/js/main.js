/**
 * AI-Generated Theme — Main JavaScript
 * Header scroll, mobile menu, scroll animations, parallax
 */
document.addEventListener('DOMContentLoaded', function () {

    /* ── Header scroll ── */
    var header = document.getElementById('siteHeader');
    if (header) {
        function handleScroll() {
            if (window.pageYOffset > 60) {
                header.classList.add('header-scrolled');
            } else {
                header.classList.remove('header-scrolled');
            }
        }
        window.addEventListener('scroll', handleScroll, { passive: true });
        handleScroll();
    }

    /* ── Mobile menu ── */
    var mobileToggle = document.getElementById('mobileToggle');
    var headerNav = document.getElementById('headerNav');
    var mobileOverlay = document.getElementById('mobileOverlay');

    if (mobileToggle && headerNav) {
        mobileToggle.addEventListener('click', function () {
            headerNav.classList.toggle('nav-open');
            mobileToggle.classList.toggle('toggle-active');
            document.body.classList.toggle('menu-open');
            if (mobileOverlay) mobileOverlay.classList.toggle('overlay-active');
        });

        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', function () {
                headerNav.classList.remove('nav-open');
                mobileToggle.classList.remove('toggle-active');
                document.body.classList.remove('menu-open');
                mobileOverlay.classList.remove('overlay-active');
            });
        }

        headerNav.querySelectorAll('.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                headerNav.classList.remove('nav-open');
                mobileToggle.classList.remove('toggle-active');
                document.body.classList.remove('menu-open');
                if (mobileOverlay) mobileOverlay.classList.remove('overlay-active');
            });
        });
    }

    /* ── Smooth scroll ── */
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            var targetId = this.getAttribute('href');
            if (targetId === '#') return;
            var target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                var headerHeight = header ? header.offsetHeight : 0;
                window.scrollTo({
                    top: target.getBoundingClientRect().top + window.pageYOffset - headerHeight,
                    behavior: 'smooth'
                });
            }
        });
    });

    /* ── Scroll animations ── */
    var animEls = document.querySelectorAll('[data-animate]');
    if (animEls.length > 0 && 'IntersectionObserver' in window) {
        var delay = 0, lastBatch = 0;
        var obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var now = Date.now();
                    if (now - lastBatch > 200) delay = 0;
                    lastBatch = now;
                    setTimeout(function () { entry.target.classList.add('animated'); }, delay);
                    delay += 100;
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });
        animEls.forEach(function (el) { obs.observe(el); });
    }

    /* ── Hero parallax ── */
    var heroBg = document.querySelector('.hero-bg, .hero__bg');
    if (heroBg) {
        var ticking = false;
        window.addEventListener('scroll', function () {
            if (!ticking) {
                requestAnimationFrame(function () {
                    var scrolled = window.pageYOffset;
                    if (scrolled < window.innerHeight) {
                        heroBg.style.transform = 'translateY(' + (scrolled * 0.35) + 'px)';
                    }
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
    }

});