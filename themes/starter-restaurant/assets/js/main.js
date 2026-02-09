/**
 * Starter Restaurant Theme — Main JavaScript
 * Mobile menu, header scroll, animations, reservation form
 */
document.addEventListener('DOMContentLoaded', function () {

    /* ────────────────────────────────────
       Header scroll effect
    ──────────────────────────────────── */
    const header = document.getElementById('siteHeader');
    if (header) {
        let lastScroll = 0;
        const scrollThreshold = 80;

        function handleScroll() {
            const currentScroll = window.pageYOffset;
            if (currentScroll > scrollThreshold) {
                header.classList.add('header-scrolled');
            } else {
                header.classList.remove('header-scrolled');
            }
            lastScroll = currentScroll;
        }

        window.addEventListener('scroll', handleScroll, { passive: true });
        handleScroll(); // Run once on load
    }

    /* ────────────────────────────────────
       Mobile menu toggle
    ──────────────────────────────────── */
    const mobileToggle = document.getElementById('mobileToggle');
    const headerNav = document.getElementById('headerNav');
    const mobileOverlay = document.getElementById('mobileOverlay');

    if (mobileToggle && headerNav) {
        mobileToggle.addEventListener('click', function () {
            const isOpen = headerNav.classList.contains('nav-open');
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

    /* ────────────────────────────────────
       Smooth scroll for anchor links
    ──────────────────────────────────── */
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                const headerHeight = header ? header.offsetHeight : 0;
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerHeight;
                window.scrollTo({ top: targetPosition, behavior: 'smooth' });
            }
        });
    });

    /* ────────────────────────────────────
       Scroll-triggered animations
    ──────────────────────────────────── */
    const animatedElements = document.querySelectorAll('[data-animate]');

    if (animatedElements.length > 0 && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

        animatedElements.forEach(function (el) {
            observer.observe(el);
        });
    }

    /* ────────────────────────────────────
       Parallax effect on hero
    ──────────────────────────────────── */
    const heroBg = document.querySelector('.hero-bg');
    if (heroBg) {
        window.addEventListener('scroll', function () {
            const scrolled = window.pageYOffset;
            heroBg.style.transform = 'translateY(' + (scrolled * 0.4) + 'px)';
        }, { passive: true });
    }

    /* ────────────────────────────────────
       Reservation form handling
    ──────────────────────────────────── */
    const resForm = document.getElementById('reservationForm');
    if (resForm) {
        // Set minimum date to today
        const dateInput = resForm.querySelector('#resDate');
        if (dateInput) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.setAttribute('min', today);
        }

        resForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = resForm.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            btn.textContent = 'Sending...';
            btn.disabled = true;

            // Simulate form submission
            setTimeout(function () {
                btn.textContent = '✓ Reservation Requested!';
                btn.classList.add('btn-success');
                setTimeout(function () {
                    btn.textContent = originalText;
                    btn.disabled = false;
                    btn.classList.remove('btn-success');
                    resForm.reset();
                }, 3000);
            }, 1200);
        });
    }

    /* ────────────────────────────────────
       Counter animation for experience number
    ──────────────────────────────────── */
    const expNumber = document.querySelector('.exp-number');
    if (expNumber && 'IntersectionObserver' in window) {
        const counterObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounter(expNumber);
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        counterObserver.observe(expNumber);
    }

    function animateCounter(el) {
        const target = parseInt(el.textContent);
        let current = 0;
        const step = Math.ceil(target / 40);
        const interval = setInterval(function () {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(interval);
            }
            el.textContent = current + '+';
        }, 40);
    }

});
