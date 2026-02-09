/**
 * Starter Portfolio — Main JavaScript
 * Cursor effects, scroll reveals, mobile menu, header state
 */

(function () {
    'use strict';

    // ── Custom Cursor ─────────────────────────────────────────
    const cursorDot  = document.getElementById('cursorDot');
    const cursorRing = document.getElementById('cursorRing');

    if (cursorDot && cursorRing) {
        let mouseX = 0, mouseY = 0;
        let ringX  = 0, ringY  = 0;

        document.addEventListener('mousemove', (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;
            cursorDot.style.left = mouseX + 'px';
            cursorDot.style.top  = mouseY + 'px';
        });

        // Smooth ring follow
        function animateRing() {
            ringX += (mouseX - ringX) * 0.15;
            ringY += (mouseY - ringY) * 0.15;
            cursorRing.style.left = ringX + 'px';
            cursorRing.style.top  = ringY + 'px';
            requestAnimationFrame(animateRing);
        }
        animateRing();

        // Hover effect on interactive elements
        const hoverTargets = document.querySelectorAll('a, button, .work-card, .skill-card, input, textarea, [role="button"]');
        hoverTargets.forEach((el) => {
            el.addEventListener('mouseenter', () => {
                cursorDot.classList.add('cursor-hover');
                cursorRing.classList.add('cursor-hover');
            });
            el.addEventListener('mouseleave', () => {
                cursorDot.classList.remove('cursor-hover');
                cursorRing.classList.remove('cursor-hover');
            });
        });

        // Hide cursor when leaving window
        document.addEventListener('mouseleave', () => {
            cursorDot.style.opacity  = '0';
            cursorRing.style.opacity = '0';
        });
        document.addEventListener('mouseenter', () => {
            cursorDot.style.opacity  = '1';
            cursorRing.style.opacity = '0.5';
        });
    }

    // ── Header Scroll State ───────────────────────────────────
    const header = document.getElementById('siteHeader');
    if (header) {
        let lastScroll = 0;
        const scrollThreshold = 50;

        function updateHeader() {
            const currentScroll = window.scrollY;
            if (currentScroll > scrollThreshold) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            lastScroll = currentScroll;
        }

        window.addEventListener('scroll', updateHeader, { passive: true });
        updateHeader();
    }

    // ── Mobile Menu ───────────────────────────────────────────
    const menuToggle   = document.getElementById('menuToggle');
    const mobileOverlay = document.getElementById('mobileOverlay');

    if (menuToggle && mobileOverlay) {
        menuToggle.addEventListener('click', () => {
            const isActive = menuToggle.classList.toggle('active');
            mobileOverlay.classList.toggle('active');
            menuToggle.setAttribute('aria-expanded', isActive);
            document.body.style.overflow = isActive ? 'hidden' : '';
        });

        // Close on link click
        mobileOverlay.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                menuToggle.classList.remove('active');
                mobileOverlay.classList.remove('active');
                menuToggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            });
        });

        // Close on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mobileOverlay.classList.contains('active')) {
                menuToggle.classList.remove('active');
                mobileOverlay.classList.remove('active');
                menuToggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }
        });
    }

    // ── Scroll Reveal (IntersectionObserver) ──────────────────
    if (document.body.classList.contains('has-animations')) {
        const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale');

        if (revealElements.length > 0 && 'IntersectionObserver' in window) {
            const observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('revealed');
                            observer.unobserve(entry.target);
                        }
                    });
                },
                {
                    threshold: 0.1,
                    rootMargin: '0px 0px -60px 0px',
                }
            );

            revealElements.forEach((el) => observer.observe(el));
        } else {
            // Fallback: show everything
            revealElements.forEach((el) => el.classList.add('revealed'));
        }
    }

    // ── Smooth scroll for anchor links ────────────────────────
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // ── Marquee pause on hover ────────────────────────────────
    document.querySelectorAll('.marquee').forEach((marquee) => {
        const inner = marquee.querySelector('.marquee-inner');
        if (inner) {
            marquee.addEventListener('mouseenter', () => {
                inner.style.animationPlayState = 'paused';
            });
            marquee.addEventListener('mouseleave', () => {
                inner.style.animationPlayState = 'running';
            });
        }
    });

    // ── Text typing effect for hero (optional class) ──────────
    document.querySelectorAll('.typing-text').forEach((el) => {
        const text = el.textContent;
        el.textContent = '';
        el.style.visibility = 'visible';
        let i = 0;

        function typeChar() {
            if (i < text.length) {
                el.textContent += text.charAt(i);
                i++;
                setTimeout(typeChar, 50 + Math.random() * 30);
            }
        }

        // Start typing when element is visible
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                typeChar();
                observer.disconnect();
            }
        });
        observer.observe(el);
    });

    // ── Parallax on mouse move (hero section) ─────────────────
    const hero = document.querySelector('.hero');
    if (hero && window.innerWidth > 768) {
        hero.addEventListener('mousemove', (e) => {
            const rect   = hero.getBoundingClientRect();
            const x       = (e.clientX - rect.left) / rect.width - 0.5;
            const y       = (e.clientY - rect.top) / rect.height - 0.5;

            const floaters = hero.querySelectorAll('.hero-float');
            floaters.forEach((el, i) => {
                const speed = (i + 1) * 8;
                el.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
            });
        });
    }

    // ── Current year in footer (safety) ───────────────────────
    document.querySelectorAll('[data-year]').forEach((el) => {
        el.textContent = new Date().getFullYear();
    });

})();
