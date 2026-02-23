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
            document.body.classList.toggle('nav-open');
            mobileToggle.setAttribute('aria-expanded', document.body.classList.contains('nav-open'));
            if (mobileOverlay) mobileOverlay.classList.toggle('overlay-active');
        });

        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', function () {
                headerNav.classList.remove('nav-open');
                mobileToggle.classList.remove('toggle-active');
                document.body.classList.remove('nav-open');
                mobileToggle.setAttribute('aria-expanded', 'false');
                mobileOverlay.classList.remove('overlay-active');
            });
        }

        headerNav.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                headerNav.classList.remove('nav-open');
                mobileToggle.classList.remove('toggle-active');
                document.body.classList.remove('nav-open');
                mobileToggle.setAttribute('aria-expanded', 'false');
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
    } else {
        // Fallback: no IntersectionObserver — show all immediately
        animEls.forEach(function (el) { el.classList.add('animated'); });
    }

    // Safety net: if any [data-animate] elements are still hidden after 2s, force-show them
    // This prevents invisible content if JS animation fails for any reason
    setTimeout(function () {
        document.querySelectorAll('[data-animate]:not(.animated)').forEach(function (el) {
            el.classList.add('animated');
        });
    }, 2000);

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

    /* ── Back to top button ── */
    var backToTop = document.querySelector('.back-to-top, #backToTop');
    if (backToTop) {
        window.addEventListener('scroll', function () {
            if (window.pageYOffset > 400) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        }, { passive: true });
        backToTop.addEventListener('click', function (e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ── FAQ Accordion ── */
    document.querySelectorAll('.faq-question, .accordion-trigger, [data-accordion]').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            var parent = this.closest('.faq-item, .accordion-item');
            if (!parent) return;
            var isActive = parent.classList.contains('active');
            // Close siblings
            var container = parent.parentElement;
            if (container) {
                container.querySelectorAll('.faq-item.active, .accordion-item.active').forEach(function (item) {
                    item.classList.remove('active');
                    var answer = item.querySelector('.faq-answer, .accordion-content');
                    if (answer) answer.style.maxHeight = null;
                });
            }
            // Toggle current
            if (!isActive) {
                parent.classList.add('active');
                var answer = parent.querySelector('.faq-answer, .accordion-content');
                if (answer) answer.style.maxHeight = answer.scrollHeight + 'px';
            }
        });
    });

    /* ── Counter animation (stats) ── */
    var counters = document.querySelectorAll('[data-count], .stat-number, .counter');
    if (counters.length > 0 && 'IntersectionObserver' in window) {
        var counterObs = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var el = entry.target;
                    var target = parseInt(el.getAttribute('data-count') || el.textContent.replace(/[^0-9]/g, ''), 10);
                    if (isNaN(target) || target === 0) return;
                    var suffix = el.textContent.replace(/[0-9,. ]/g, '');
                    var duration = 1500;
                    var start = 0;
                    var startTime = null;
                    function step(ts) {
                        if (!startTime) startTime = ts;
                        var progress = Math.min((ts - startTime) / duration, 1);
                        var eased = 1 - Math.pow(1 - progress, 3);
                        el.textContent = Math.floor(eased * target).toLocaleString() + suffix;
                        if (progress < 1) requestAnimationFrame(step);
                    }
                    requestAnimationFrame(step);
                    counterObs.unobserve(el);
                }
            });
        }, { threshold: 0.3 });
        counters.forEach(function (el) { counterObs.observe(el); });
    }

    /* ── Lightbox for gallery images ── */
    document.querySelectorAll('.gallery-item img, .lightbox-trigger img, [data-lightbox] img').forEach(function (img) {
        img.style.cursor = 'pointer';
        img.addEventListener('click', function () {
            var overlay = document.createElement('div');
            overlay.style.cssText = 'position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.9);display:flex;align-items:center;justify-content:center;cursor:pointer;opacity:0;transition:opacity 0.3s';
            var bigImg = document.createElement('img');
            bigImg.src = this.src;
            bigImg.alt = this.alt || '';
            bigImg.style.cssText = 'max-width:90vw;max-height:90vh;object-fit:contain;border-radius:8px;transform:scale(0.9);transition:transform 0.3s';
            overlay.appendChild(bigImg);
            document.body.appendChild(overlay);
            requestAnimationFrame(function () { overlay.style.opacity = '1'; bigImg.style.transform = 'scale(1)'; });
            overlay.addEventListener('click', function () {
                overlay.style.opacity = '0';
                setTimeout(function () { overlay.remove(); }, 300);
            });
            document.addEventListener('keydown', function escHandler(e) {
                if (e.key === 'Escape') { overlay.click(); document.removeEventListener('keydown', escHandler); }
            });
        });
    });

    /* ── Dark mode toggle ── */
    var darkToggle = document.querySelector('[data-dark-toggle], .dark-mode-toggle');
    if (darkToggle) {
        var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        var savedTheme = localStorage.getItem('theme-mode');
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
        } else if (prefersDark) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }

        darkToggle.addEventListener('click', function() {
            var current = document.documentElement.getAttribute('data-theme');
            var next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('theme-mode', next);
            this.textContent = next === 'dark' ? '☀️' : '🌙';
        });

        // Set initial icon
        var isDark = document.documentElement.getAttribute('data-theme') === 'dark' ||
                     (!document.documentElement.getAttribute('data-theme') && prefersDark);
        darkToggle.textContent = isDark ? '☀️' : '🌙';
    }

});