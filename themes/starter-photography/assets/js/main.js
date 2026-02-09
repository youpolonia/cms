/**
 * Lens & Light — Photography Portfolio Theme
 * Main JavaScript
 */
(function() {
    'use strict';

    // --- Hamburger Menu ---
    const menuToggle = document.getElementById('menuToggle');
    const overlayMenu = document.getElementById('overlayMenu');
    let menuOpen = false;

    if (menuToggle && overlayMenu) {
        menuToggle.addEventListener('click', function() {
            menuOpen = !menuOpen;
            menuToggle.classList.toggle('active', menuOpen);
            overlayMenu.classList.toggle('active', menuOpen);
            document.body.style.overflow = menuOpen ? 'hidden' : '';
        });

        // Close menu on link click
        overlayMenu.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                menuOpen = false;
                menuToggle.classList.remove('active');
                overlayMenu.classList.remove('active');
                document.body.style.overflow = '';
            });
        });

        // Close on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && menuOpen) {
                menuOpen = false;
                menuToggle.classList.remove('active');
                overlayMenu.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }

    // --- Header scroll effect ---
    const header = document.querySelector('.site-header');
    if (header) {
        let lastScroll = 0;
        window.addEventListener('scroll', function() {
            const scrollY = window.pageYOffset || document.documentElement.scrollTop;
            header.classList.toggle('scrolled', scrollY > 80);
            lastScroll = scrollY;
        }, { passive: true });
    }

    // --- Horizontal Scroll Drag ---
    document.querySelectorAll('.horizontal-scroll').forEach(function(scroller) {
        let isDown = false;
        let startX;
        let scrollLeft;

        scroller.addEventListener('mousedown', function(e) {
            isDown = true;
            scroller.style.cursor = 'grabbing';
            startX = e.pageX - scroller.offsetLeft;
            scrollLeft = scroller.scrollLeft;
        });

        scroller.addEventListener('mouseleave', function() {
            isDown = false;
            scroller.style.cursor = 'grab';
        });

        scroller.addEventListener('mouseup', function() {
            isDown = false;
            scroller.style.cursor = 'grab';
        });

        scroller.addEventListener('mousemove', function(e) {
            if (!isDown) return;
            e.preventDefault();
            var x = e.pageX - scroller.offsetLeft;
            var walk = (x - startX) * 2;
            scroller.scrollLeft = scrollLeft - walk;
        });
    });

    // --- Fade-in on scroll ---
    var fadeEls = document.querySelectorAll('.fade-in');
    if (fadeEls.length > 0) {
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -40px 0px'
        });

        fadeEls.forEach(function(el) {
            observer.observe(el);
        });
    }

    // --- Parallax on hero (subtle) ---
    var hero = document.querySelector('.hero-fullscreen');
    if (hero && window.innerWidth > 768) {
        window.addEventListener('scroll', function() {
            var scrollY = window.pageYOffset;
            if (scrollY < window.innerHeight) {
                hero.style.backgroundPositionY = (scrollY * 0.3) + 'px';
            }
        }, { passive: true });
    }

})();
