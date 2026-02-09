/**
 * JTB Frontend JavaScript
 * Handles frontend interactions for Theme Builder
 *
 * @package JessieThemeBuilder
 */

(function() {
    'use strict';

    // Global observer reference for cleanup
    let jtbMainObserver = null;

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
            this.initMutationObserver();
        },

        /**
         * MutationObserver for dynamically added content (preview, AJAX)
         * Re-initializes interactive modules when new content is injected
         */
        initMutationObserver() {
            // Skip if MutationObserver not supported
            if (typeof MutationObserver === 'undefined') return;

            // Disconnect previous observer if exists (prevent duplicates)
            if (jtbMainObserver) {
                jtbMainObserver.disconnect();
                jtbMainObserver = null;
            }

            const self = this;

            jtbMainObserver = new MutationObserver((mutations) => {
                let shouldReinit = false;

                for (const mutation of mutations) {
                    if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                        for (const node of mutation.addedNodes) {
                            if (node.nodeType === 1) { // Element node
                                // Check if new content contains interactive modules
                                if (node.classList && (
                                    node.classList.contains('jtb-slider-container') ||
                                    node.classList.contains('jtb-module-slider') ||
                                    node.querySelector && node.querySelector('.jtb-slider-container, .jtb-tabs, .jtb-accordion')
                                )) {
                                    shouldReinit = true;
                                    break;
                                }
                            }
                        }
                    }
                    if (shouldReinit) break;
                }

                if (shouldReinit) {
                    // Debounce re-initialization
                    clearTimeout(self._reinitTimeout);
                    self._reinitTimeout = setTimeout(() => {
                        self.reinitSliders();
                        self.initTabs();
                        self.initAccordions();
                        self.initCounters();
                    }, 100);
                }
            });

            // Observe the entire document for changes
            jtbMainObserver.observe(document.body, {
                childList: true,
                subtree: true
            });

            // Cleanup on page unload to prevent memory leaks
            window.addEventListener('beforeunload', () => {
                if (jtbMainObserver) {
                    jtbMainObserver.disconnect();
                    jtbMainObserver = null;
                }
            });
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
            // Hamburger buttons (can be multiple menus on page)
            const hamburgers = document.querySelectorAll('.jtb-hamburger');
            const closeButtons = document.querySelectorAll('.jtb-mobile-menu-close');
            const overlays = document.querySelectorAll('.jtb-mobile-menu-overlay');

            // Open mobile menu
            hamburgers.forEach(hamburger => {
                hamburger.addEventListener('click', (e) => {
                    e.preventDefault();
                    const menuId = hamburger.getAttribute('aria-controls');
                    const menu = menuId ? document.getElementById(menuId) : document.querySelector('.jtb-mobile-menu');
                    if (menu) {
                        this.openMobileMenu(menu, hamburger);
                    }
                });
            });

            // Close button
            closeButtons.forEach(closeBtn => {
                closeBtn.addEventListener('click', () => {
                    this.closeMobileMenu();
                });
            });

            // Close on overlay click
            overlays.forEach(overlay => {
                overlay.addEventListener('click', () => {
                    this.closeMobileMenu();
                });
            });

            // Close on escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.mobileMenuOpen) {
                    this.closeMobileMenu();
                }
            });

            // Mobile dropdown toggles
            this.initMobileDropdowns();
        },

        initMobileDropdowns() {
            const toggles = document.querySelectorAll('.jtb-mobile-nav-toggle');

            toggles.forEach(toggle => {
                toggle.addEventListener('click', () => {
                    const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
                    const dropdown = toggle.nextElementSibling;

                    // Close other dropdowns
                    toggles.forEach(t => {
                        if (t !== toggle) {
                            t.setAttribute('aria-expanded', 'false');
                            const d = t.nextElementSibling;
                            if (d) d.classList.remove('open');
                        }
                    });

                    // Toggle this one
                    toggle.setAttribute('aria-expanded', !isExpanded);
                    if (dropdown) {
                        dropdown.classList.toggle('open', !isExpanded);
                    }
                });
            });
        },

        openMobileMenu(menu, trigger) {
            this.mobileMenuOpen = true;
            this.activeMobileMenu = menu;
            this.activeTrigger = trigger;

            menu.classList.add('open');
            menu.setAttribute('aria-hidden', 'false');
            trigger.setAttribute('aria-expanded', 'true');
            document.body.classList.add('jtb-mobile-menu-open');

            // Focus first focusable element
            const firstFocusable = menu.querySelector('a, button');
            if (firstFocusable) {
                setTimeout(() => firstFocusable.focus(), 100);
            }
        },

        closeMobileMenu() {
            if (!this.mobileMenuOpen) return;

            this.mobileMenuOpen = false;

            const menu = this.activeMobileMenu || document.querySelector('.jtb-mobile-menu.open');
            if (menu) {
                menu.classList.remove('open');
                menu.setAttribute('aria-hidden', 'true');
            }

            if (this.activeTrigger) {
                this.activeTrigger.setAttribute('aria-expanded', 'false');
                this.activeTrigger.focus();
            }

            document.body.classList.remove('jtb-mobile-menu-open');
            this.activeMobileMenu = null;
            this.activeTrigger = null;
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
                // Skip if already initialized
                if (tabContainer.dataset.jtbTabsInit) return;
                tabContainer.dataset.jtbTabsInit = 'true';

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
                // Skip if already initialized
                if (accordion.dataset.jtbAccordionInit) return;
                accordion.dataset.jtbAccordionInit = 'true';

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
            // Support both class names for compatibility
            document.querySelectorAll('.jtb-slider-container, .jtb-slider').forEach(slider => {
                // Skip if already initialized
                if (slider.dataset.jtbSliderInit) return;
                slider.dataset.jtbSliderInit = 'true';
                this.initSlider(slider);
            });
        },

        /**
         * Re-initialize sliders (call after dynamic content injection)
         */
        reinitSliders() {
            this.initSliders();
        },

        initSlider(container) {
            // Support both class names: .jtb-slider-slide (PHP) and .jtb-slide (legacy)
            const slides = container.querySelectorAll('.jtb-slider-slide, .jtb-slide');
            if (slides.length < 2) return;

            let currentIndex = 0;
            // Support both data attributes: data-auto (PHP) and data-autoplay (legacy)
            const autoplay = container.dataset.auto === 'true' || container.dataset.autoplay === 'true';
            const interval = parseInt(container.dataset.speed || container.dataset.interval) || 5000;

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

            // Autoplay with proper cleanup
            if (autoplay) {
                let intervalId = setInterval(nextSlide, interval);

                // Store interval ID on container for cleanup
                container._jtbIntervalId = intervalId;

                // Pause on hover (UX improvement)
                container.addEventListener('mouseenter', () => {
                    if (container._jtbIntervalId) {
                        clearInterval(container._jtbIntervalId);
                        container._jtbIntervalId = null;
                    }
                });

                container.addEventListener('mouseleave', () => {
                    if (!container._jtbIntervalId) {
                        container._jtbIntervalId = setInterval(nextSlide, interval);
                    }
                });

                // Cleanup when slider is removed from DOM
                const cleanupObserver = new MutationObserver((mutations) => {
                    for (const mutation of mutations) {
                        for (const node of mutation.removedNodes) {
                            if (node === container || (node.contains && node.contains(container))) {
                                if (container._jtbIntervalId) {
                                    clearInterval(container._jtbIntervalId);
                                    container._jtbIntervalId = null;
                                }
                                cleanupObserver.disconnect();
                                return;
                            }
                        }
                    }
                });

                if (container.parentNode) {
                    cleanupObserver.observe(container.parentNode, { childList: true, subtree: true });
                }
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
