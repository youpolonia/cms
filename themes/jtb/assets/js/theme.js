/**
 * JTB Theme - Frontend JavaScript
 * Official theme for Jessie Theme Builder
 *
 * @package JTB Theme
 * @version 1.0
 */
(function() {
    'use strict';

    /**
     * Mobile Menu Toggle
     */
    function initMobileMenu() {
        const mobileToggle = document.querySelector('.mobile-toggle');
        const navMobile = document.getElementById('mobile-menu');
        const navMain = document.querySelector('.nav-main');

        if (!mobileToggle) return;

        mobileToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';

            this.setAttribute('aria-expanded', !isExpanded);

            if (navMobile) {
                navMobile.hidden = isExpanded;
            }

            // Also toggle nav-main for CSS-only mobile menu
            if (navMain) {
                navMain.classList.toggle('open');
            }
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.jtb-default-header')) {
                if (navMobile && !navMobile.hidden) {
                    mobileToggle.setAttribute('aria-expanded', 'false');
                    navMobile.hidden = true;
                }
                if (navMain) {
                    navMain.classList.remove('open');
                }
            }
        });

        // Close menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (navMobile && !navMobile.hidden) {
                    mobileToggle.setAttribute('aria-expanded', 'false');
                    navMobile.hidden = true;
                    mobileToggle.focus();
                }
            }
        });
    }

    /**
     * Sticky Header
     * Only runs if JTB frontend.js is not loaded (has its own sticky logic)
     */
    function initStickyHeader() {
        // Skip if JTB frontend.js is handling this
        if (window.JTB && typeof window.JTB.initStickyHeader === 'function') {
            return;
        }

        const header = document.querySelector('.jtb-default-header, .jtb-site-header');
        if (!header) return;

        let lastScrollY = window.scrollY;
        let ticking = false;

        function updateHeader() {
            const scrollY = window.scrollY;

            // Add scrolled class when scrolled down
            header.classList.toggle('scrolled', scrollY > 50);

            // Optional: Hide header on scroll down, show on scroll up
            // Uncomment if desired:
            // if (scrollY > lastScrollY && scrollY > 200) {
            //     header.classList.add('header-hidden');
            // } else {
            //     header.classList.remove('header-hidden');
            // }

            lastScrollY = scrollY;
            ticking = false;
        }

        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(updateHeader);
                ticking = true;
            }
        }, { passive: true });
    }

    /**
     * Smooth Scroll for Anchor Links
     */
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');

                // Skip if it's just "#" or empty
                if (targetId === '#' || !targetId) return;

                const targetElement = document.querySelector(targetId);
                if (!targetElement) return;

                e.preventDefault();

                const headerHeight = document.querySelector('.jtb-default-header, .jtb-site-header')?.offsetHeight || 0;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight - 20;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });

                // Update URL without jumping
                history.pushState(null, null, targetId);
            });
        });
    }

    /**
     * Lazy Loading Images (native fallback)
     */
    function initLazyLoading() {
        // Modern browsers support native lazy loading
        // This is a fallback for older browsers
        if ('loading' in HTMLImageElement.prototype) {
            // Native lazy loading supported
            const lazyImages = document.querySelectorAll('img[loading="lazy"]');
            lazyImages.forEach(function(img) {
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                }
            });
        } else {
            // Fallback: IntersectionObserver
            const lazyImages = document.querySelectorAll('img[data-src]');

            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            const image = entry.target;
                            image.src = image.dataset.src;
                            image.removeAttribute('data-src');
                            imageObserver.unobserve(image);
                        }
                    });
                }, {
                    rootMargin: '50px 0px'
                });

                lazyImages.forEach(function(image) {
                    imageObserver.observe(image);
                });
            } else {
                // Final fallback: load all images
                lazyImages.forEach(function(img) {
                    img.src = img.dataset.src;
                });
            }
        }
    }

    /**
     * Back to Top Button (optional)
     */
    function initBackToTop() {
        const backToTopBtn = document.querySelector('.back-to-top');
        if (!backToTopBtn) return;

        function toggleButton() {
            backToTopBtn.classList.toggle('visible', window.scrollY > 500);
        }

        window.addEventListener('scroll', toggleButton, { passive: true });

        backToTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    /**
     * External Links - Open in new tab
     */
    function initExternalLinks() {
        const links = document.querySelectorAll('a[href^="http"]');
        const currentHost = window.location.host;

        links.forEach(function(link) {
            if (!link.host.includes(currentHost)) {
                link.setAttribute('target', '_blank');
                link.setAttribute('rel', 'noopener noreferrer');
            }
        });
    }

    /**
     * Print Current Year (for copyright)
     */
    function initCurrentYear() {
        const yearElements = document.querySelectorAll('[data-current-year]');
        const currentYear = new Date().getFullYear();

        yearElements.forEach(function(el) {
            el.textContent = currentYear;
        });
    }

    /**
     * Initialize All
     */
    function init() {
        initMobileMenu();
        initStickyHeader();
        initSmoothScroll();
        initLazyLoading();
        initBackToTop();
        initExternalLinks();
        initCurrentYear();

        // Dispatch event for other scripts
        document.dispatchEvent(new CustomEvent('jtbThemeReady'));
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose for external use
    window.JTBTheme = {
        init: init,
        initMobileMenu: initMobileMenu,
        initStickyHeader: initStickyHeader,
        initSmoothScroll: initSmoothScroll
    };

})();
