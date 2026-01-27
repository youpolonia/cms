/**
 * JTB Frontend JavaScript
 * Handles frontend interactions for Theme Builder
 *
 * @package JessieThemeBuilder
 */

(function() {
    'use strict';

    const JTBFrontend = {
        // Configuration
        config: {
            stickyOffset: 0,
            mobileBreakpoint: 980
        },

        // State
        state: {
            scrollY: 0,
            lastScrollY: 0,
            headerHeight: 0,
            isMobile: false
        },

        /**
         * Initialize frontend functionality
         */
        init() {
            this.detectMobile();
            this.initStickyHeader();
            this.initMobileMenu();
            this.initScrollAnimations();
            this.initCounters();
            this.initTabs();
            this.initAccordions();
            this.initSliders();
            this.initLightbox();
            this.initSmoothScroll();
            this.initLazyLoad();
            this.bindEvents();
        },

        /**
         * Bind global events
         */
        bindEvents() {
            window.addEventListener('scroll', this.throttle(() => this.onScroll(), 16));
            window.addEventListener('resize', this.debounce(() => this.onResize(), 150));
            document.addEventListener('DOMContentLoaded', () => this.onDOMReady());
        },

        /**
         * Scroll handler
         */
        onScroll() {
            this.state.scrollY = window.scrollY;
            this.updateStickyHeader();
            this.triggerScrollAnimations();
            this.state.lastScrollY = this.state.scrollY;
        },

        /**
         * Resize handler
         */
        onResize() {
            this.detectMobile();
            this.updateHeaderHeight();
        },

        /**
         * DOM Ready handler
         */
        onDOMReady() {
            this.updateHeaderHeight();
        },

        /**
         * Detect mobile viewport
         */
        detectMobile() {
            this.state.isMobile = window.innerWidth < this.config.mobileBreakpoint;
            document.body.classList.toggle('jtb-mobile', this.state.isMobile);
            document.body.classList.toggle('jtb-desktop', !this.state.isMobile);
        },

        // ========================================
        // Sticky Header
        // ========================================

        initStickyHeader() {
            const header = document.querySelector('.jtb-site-header.jtb-sticky');
            if (!header) return;

            this.stickyHeader = header;
            this.config.stickyOffset = parseInt(header.dataset.stickyOffset) || 0;
            this.shouldShrink = header.dataset.stickyShrink === 'true';

            // Add placeholder to prevent content jump
            this.createStickyPlaceholder();
        },

        createStickyPlaceholder() {
            if (!this.stickyHeader) return;

            const placeholder = document.createElement('div');
            placeholder.className = 'jtb-header-placeholder';
            placeholder.style.display = 'none';
            this.stickyHeader.parentNode.insertBefore(placeholder, this.stickyHeader);
            this.stickyPlaceholder = placeholder;
        },

        updateHeaderHeight() {
            if (this.stickyHeader) {
                this.state.headerHeight = this.stickyHeader.offsetHeight;
                if (this.stickyPlaceholder) {
                    this.stickyPlaceholder.style.height = this.state.headerHeight + 'px';
                }
            }
        },

        updateStickyHeader() {
            if (!this.stickyHeader) return;

            const scrolled = this.state.scrollY > this.config.stickyOffset;
            const scrollingDown = this.state.scrollY > this.state.lastScrollY;

            // Toggle scrolled class
            this.stickyHeader.classList.toggle('scrolled', scrolled);

            // Toggle shrink class
            if (this.shouldShrink) {
                this.stickyHeader.classList.toggle('shrink', scrolled && this.state.scrollY > 100);
            }

            // Show/hide placeholder
            if (this.stickyPlaceholder) {
                this.stickyPlaceholder.style.display = scrolled ? 'block' : 'none';
            }

            // Hide on scroll down, show on scroll up (optional)
            // this.stickyHeader.classList.toggle('hidden', scrolled && scrollingDown && this.state.scrollY > 300);
        },

        // ========================================
        // Mobile Menu
        // ========================================

        initMobileMenu() {
            const menuToggles = document.querySelectorAll('.jtb-mobile-menu-toggle');
            const mobileMenu = document.querySelector('.jtb-mobile-menu');

            menuToggles.forEach(toggle => {
                toggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.toggleMobileMenu();
                });
            });

            // Close on click outside
            document.addEventListener('click', (e) => {
                if (this.mobileMenuOpen && !e.target.closest('.jtb-mobile-menu') && !e.target.closest('.jtb-mobile-menu-toggle')) {
                    this.closeMobileMenu();
                }
            });

            // Close on escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.mobileMenuOpen) {
                    this.closeMobileMenu();
                }
            });
        },

        toggleMobileMenu() {
            this.mobileMenuOpen = !this.mobileMenuOpen;
            document.body.classList.toggle('jtb-mobile-menu-open', this.mobileMenuOpen);

            const menu = document.querySelector('.jtb-mobile-menu');
            if (menu) {
                menu.classList.toggle('open', this.mobileMenuOpen);
            }
        },

        closeMobileMenu() {
            this.mobileMenuOpen = false;
            document.body.classList.remove('jtb-mobile-menu-open');

            const menu = document.querySelector('.jtb-mobile-menu');
            if (menu) {
                menu.classList.remove('open');
            }
        },

        // ========================================
        // Scroll Animations
        // ========================================

        initScrollAnimations() {
            this.animatedElements = document.querySelectorAll('[data-jtb-animation]');

            if ('IntersectionObserver' in window) {
                this.animationObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.animateElement(entry.target);
                            this.animationObserver.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.1 });

                this.animatedElements.forEach(el => {
                    this.animationObserver.observe(el);
                });
            } else {
                // Fallback: animate all immediately
                this.animatedElements.forEach(el => this.animateElement(el));
            }
        },

        animateElement(el) {
            const animation = el.dataset.jtbAnimation;
            const delay = el.dataset.jtbDelay || 0;
            const duration = el.dataset.jtbDuration || '1s';

            setTimeout(() => {
                el.style.animationDuration = duration;
                el.classList.add('jtb-animated', `jtb-${animation}`);
            }, delay);
        },

        triggerScrollAnimations() {
            // Additional scroll-based animations if needed
        },

        // ========================================
        // Counters
        // ========================================

        initCounters() {
            const counters = document.querySelectorAll('.jtb-counter[data-target]');

            if ('IntersectionObserver' in window) {
                const counterObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.animateCounter(entry.target);
                            counterObserver.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.5 });

                counters.forEach(counter => counterObserver.observe(counter));
            }
        },

        animateCounter(counter) {
            const target = parseInt(counter.dataset.target);
            const duration = parseInt(counter.dataset.duration) || 2000;
            const start = 0;
            const startTime = performance.now();

            const updateCounter = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const easeProgress = 1 - Math.pow(1 - progress, 3); // Ease out cubic
                const current = Math.round(start + (target - start) * easeProgress);

                counter.textContent = current.toLocaleString();

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                }
            };

            requestAnimationFrame(updateCounter);
        },

        // ========================================
        // Tabs
        // ========================================

        initTabs() {
            document.querySelectorAll('.jtb-tabs').forEach(tabContainer => {
                const tabNavs = tabContainer.querySelectorAll('.jtb-tab-nav');
                const tabPanes = tabContainer.querySelectorAll('.jtb-tab-pane');

                tabNavs.forEach(nav => {
                    nav.addEventListener('click', (e) => {
                        e.preventDefault();
                        const targetId = nav.dataset.tab;

                        // Update nav state
                        tabNavs.forEach(n => n.classList.remove('active'));
                        nav.classList.add('active');

                        // Update pane state
                        tabPanes.forEach(pane => {
                            pane.classList.toggle('active', pane.id === targetId);
                        });
                    });
                });
            });
        },

        // ========================================
        // Accordions
        // ========================================

        initAccordions() {
            document.querySelectorAll('.jtb-accordion').forEach(accordion => {
                const items = accordion.querySelectorAll('.jtb-accordion-item');
                const allowMultiple = accordion.dataset.multiple === 'true';

                items.forEach(item => {
                    const header = item.querySelector('.jtb-accordion-header');
                    const content = item.querySelector('.jtb-accordion-content');

                    header.addEventListener('click', () => {
                        const isOpen = item.classList.contains('open');

                        if (!allowMultiple) {
                            items.forEach(i => {
                                i.classList.remove('open');
                                const c = i.querySelector('.jtb-accordion-content');
                                if (c) c.style.maxHeight = null;
                            });
                        }

                        if (!isOpen) {
                            item.classList.add('open');
                            content.style.maxHeight = content.scrollHeight + 'px';
                        } else {
                            item.classList.remove('open');
                            content.style.maxHeight = null;
                        }
                    });
                });
            });
        },

        // ========================================
        // Sliders
        // ========================================

        initSliders() {
            document.querySelectorAll('.jtb-slider').forEach(slider => {
                this.initSlider(slider);
            });
        },

        initSlider(container) {
            const slides = container.querySelectorAll('.jtb-slide');
            if (slides.length < 2) return;

            let currentIndex = 0;
            const autoplay = container.dataset.autoplay === 'true';
            const interval = parseInt(container.dataset.interval) || 5000;

            const showSlide = (index) => {
                slides.forEach((slide, i) => {
                    slide.classList.toggle('active', i === index);
                });
                currentIndex = index;
            };

            const nextSlide = () => {
                showSlide((currentIndex + 1) % slides.length);
            };

            const prevSlide = () => {
                showSlide((currentIndex - 1 + slides.length) % slides.length);
            };

            // Navigation
            const prevBtn = container.querySelector('.jtb-slider-prev');
            const nextBtn = container.querySelector('.jtb-slider-next');

            if (prevBtn) prevBtn.addEventListener('click', prevSlide);
            if (nextBtn) nextBtn.addEventListener('click', nextSlide);

            // Autoplay
            if (autoplay) {
                setInterval(nextSlide, interval);
            }

            // Show first slide
            showSlide(0);
        },

        // ========================================
        // Lightbox
        // ========================================

        initLightbox() {
            document.querySelectorAll('[data-jtb-lightbox]').forEach(trigger => {
                trigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    const src = trigger.dataset.jtbLightbox || trigger.href;
                    this.openLightbox(src);
                });
            });
        },

        openLightbox(src) {
            // Create lightbox
            const lightbox = document.createElement('div');
            lightbox.className = 'jtb-lightbox';
            lightbox.innerHTML = `
                <div class="jtb-lightbox-backdrop"></div>
                <div class="jtb-lightbox-content">
                    <img src="${src}" alt="">
                    <button class="jtb-lightbox-close">&times;</button>
                </div>
            `;

            document.body.appendChild(lightbox);
            document.body.style.overflow = 'hidden';

            // Animate in
            requestAnimationFrame(() => lightbox.classList.add('open'));

            // Close handlers
            const close = () => {
                lightbox.classList.remove('open');
                setTimeout(() => {
                    lightbox.remove();
                    document.body.style.overflow = '';
                }, 300);
            };

            lightbox.querySelector('.jtb-lightbox-backdrop').addEventListener('click', close);
            lightbox.querySelector('.jtb-lightbox-close').addEventListener('click', close);
            document.addEventListener('keydown', function handler(e) {
                if (e.key === 'Escape') {
                    close();
                    document.removeEventListener('keydown', handler);
                }
            });
        },

        // ========================================
        // Smooth Scroll
        // ========================================

        initSmoothScroll() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', (e) => {
                    const targetId = anchor.getAttribute('href');
                    if (targetId === '#') return;

                    const target = document.querySelector(targetId);
                    if (target) {
                        e.preventDefault();
                        const offset = this.state.headerHeight || 0;
                        const targetPosition = target.getBoundingClientRect().top + window.scrollY - offset;

                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        },

        // ========================================
        // Lazy Loading
        // ========================================

        initLazyLoad() {
            if ('IntersectionObserver' in window) {
                const lazyImages = document.querySelectorAll('img[data-src]');

                const imageObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                            imageObserver.unobserve(img);
                        }
                    });
                });

                lazyImages.forEach(img => imageObserver.observe(img));
            }
        },

        // ========================================
        // Utilities
        // ========================================

        throttle(fn, wait) {
            let lastTime = 0;
            return function(...args) {
                const now = Date.now();
                if (now - lastTime >= wait) {
                    lastTime = now;
                    fn.apply(this, args);
                }
            };
        },

        debounce(fn, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => fn.apply(this, args), wait);
            };
        }
    };

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => JTBFrontend.init());
    } else {
        JTBFrontend.init();
    }

    // Expose globally for external access
    window.JTBFrontend = JTBFrontend;

})();
