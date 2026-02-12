/**
 * La Maison — Premium Restaurant Theme JS
 * Header scroll, mobile menu, animations, parallax, counter
 */
document.addEventListener('DOMContentLoaded', function () {

    /* ─── Header scroll effect ─────────────────── */
    const header = document.getElementById('siteHeader');
    if (header) {
        const scrollThreshold = 60;

        function handleScroll() {
            if (window.pageYOffset > scrollThreshold) {
                header.classList.add('header-scrolled');
            } else {
                header.classList.remove('header-scrolled');
            }
        }

        window.addEventListener('scroll', handleScroll, { passive: true });
        handleScroll();
    }

    /* ─── Mobile menu toggle ───────────────────── */
    const mobileToggle = document.getElementById('mobileToggle');
    const headerNav = document.getElementById('headerNav');
    const mobileOverlay = document.getElementById('mobileOverlay');

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

        // Close on nav link click
        headerNav.querySelectorAll('.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                headerNav.classList.remove('nav-open');
                mobileToggle.classList.remove('toggle-active');
                document.body.classList.remove('menu-open');
                if (mobileOverlay) mobileOverlay.classList.remove('overlay-active');
            });
        });
    }

    /* ─── Smooth scroll for anchor links ──────── */
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            const target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                const headerHeight = header ? header.offsetHeight : 0;
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerHeight;
                window.scrollTo({ top: targetPosition, behavior: 'smooth' });
            }
        });
    });

    /* ─── Scroll-triggered animations ─────────── */
    var animSelectors = '[data-animate], [data-animate-left], [data-animate-right]';
    var animatedElements = document.querySelectorAll(animSelectors);

    if (animatedElements.length > 0 && 'IntersectionObserver' in window) {
        var staggerDelay = 0;
        var lastBatch = 0;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    // Stagger siblings
                    var now = Date.now();
                    if (now - lastBatch > 200) staggerDelay = 0;
                    lastBatch = now;

                    setTimeout(function () {
                        entry.target.classList.add('animated');
                    }, staggerDelay);
                    staggerDelay += 100;

                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

        animatedElements.forEach(function (el) {
            observer.observe(el);
        });
    }

    /* ─── Hero parallax ───────────────────────── */
    var heroBg = document.querySelector('.hero-bg');
    if (heroBg) {
        var ticking = false;
        window.addEventListener('scroll', function () {
            if (!ticking) {
                requestAnimationFrame(function () {
                    var scrolled = window.pageYOffset;
                    if (scrolled < window.innerHeight) {
                        heroBg.style.transform = 'translateY(' + (scrolled * 0.35) + 'px) scale(1.05)';
                    }
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
    }

    /* ─── Counter animation ───────────────────── */
    var expNumber = document.querySelector('.exp-number');
    if (expNumber && 'IntersectionObserver' in window) {
        var text = expNumber.textContent.trim();
        var target = parseInt(text) || 15;
        var suffix = text.replace(/[0-9]/g, '') || '+';

        var counterObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounter(expNumber, target, suffix);
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        counterObserver.observe(expNumber);
    }

    function animateCounter(el, target, suffix) {
        var current = 0;
        var step = Math.max(1, Math.ceil(target / 50));
        var interval = setInterval(function () {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(interval);
            }
            el.textContent = current + suffix;
        }, 35);
    }

    /* ─── Reservation form handling ───────────── */
    var resForm = document.getElementById('reservationForm');
    if (resForm) {
        var dateInput = resForm.querySelector('#resDate');
        if (dateInput) {
            dateInput.setAttribute('min', new Date().toISOString().split('T')[0]);
        }

        resForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = resForm.querySelector('button[type="submit"]');
            var originalText = btn.textContent;
            btn.textContent = 'Sending...';
            btn.disabled = true;

            setTimeout(function () {
                btn.textContent = '✓ Reservation Requested!';
                setTimeout(function () {
                    btn.textContent = originalText;
                    btn.disabled = false;
                    resForm.reset();
                }, 3000);
            }, 1200);
        });
    }

});
