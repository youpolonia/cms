/**
 * The Daily Muse - Magazine Theme JavaScript
 */
(function () {
  'use strict';

  /* ──────────────────────────────────────────────
   * Mobile Menu Toggle
   * ────────────────────────────────────────────── */
  const mobileToggle = document.querySelector('.mobile-toggle');
  const mainNav = document.querySelector('.main-nav');

  if (mobileToggle && mainNav) {
    mobileToggle.addEventListener('click', function () {
      mainNav.classList.toggle('nav-open');
      this.classList.toggle('active');
      this.setAttribute('aria-expanded', mainNav.classList.contains('nav-open'));

      // Animate hamburger to X
      if (mainNav.classList.contains('nav-open')) {
        this.textContent = '✕';
      } else {
        this.textContent = '☰';
      }
    });

    // Close menu when clicking a link
    mainNav.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        mainNav.classList.remove('nav-open');
        mobileToggle.classList.remove('active');
        mobileToggle.textContent = '☰';
      });
    });
  }

  /* ──────────────────────────────────────────────
   * Scroll Header Shadow
   * ────────────────────────────────────────────── */
  const siteHeader = document.querySelector('.site-header');
  let lastScrollY = 0;
  let ticking = false;

  function updateHeaderShadow() {
    if (window.scrollY > 10) {
      siteHeader.classList.add('header-scrolled');
    } else {
      siteHeader.classList.remove('header-scrolled');
    }
    ticking = false;
  }

  if (siteHeader) {
    window.addEventListener('scroll', function () {
      lastScrollY = window.scrollY;
      if (!ticking) {
        window.requestAnimationFrame(updateHeaderShadow);
        ticking = true;
      }
    });
  }

  /* ──────────────────────────────────────────────
   * Newsletter Form Handler
   * ────────────────────────────────────────────── */
  document.querySelectorAll('.newsletter-form').forEach(function (form) {
    var btn = form.querySelector('button');
    var input = form.querySelector('input[type="email"]');

    if (btn && input) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var email = input.value.trim();

        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          input.style.borderColor = '#e63946';
          input.setAttribute('placeholder', 'Please enter a valid email');
          input.value = '';
          setTimeout(function () {
            input.style.borderColor = '';
            input.setAttribute('placeholder', 'Your email');
          }, 3000);
          return;
        }

        // Show success state
        btn.textContent = '✓ Subscribed!';
        btn.style.backgroundColor = '#2a9d8f';
        btn.disabled = true;
        input.disabled = true;
        input.value = email;

        setTimeout(function () {
          btn.textContent = 'Subscribe';
          btn.style.backgroundColor = '';
          btn.disabled = false;
          input.disabled = false;
          input.value = '';
        }, 4000);
      });
    }
  });

  /* ──────────────────────────────────────────────
   * Sidebar Search
   * ────────────────────────────────────────────── */
  document.querySelectorAll('.widget-search .search-input').forEach(function (input) {
    input.addEventListener('keypress', function (e) {
      if (e.key === 'Enter') {
        var query = this.value.trim();
        if (query) {
          window.location.href = '/articles?q=' + encodeURIComponent(query);
        }
      }
    });
  });

  /* ──────────────────────────────────────────────
   * Breaking News Ticker Animation
   * ────────────────────────────────────────────── */
  var tickerTexts = [
    'The future of AI in creative industries',
    'Climate summit reaches historic agreement',
    'New archaeological discovery changes timeline',
    'Tech giants face antitrust scrutiny worldwide',
    'Breakthrough in quantum computing announced'
  ];

  var tickerEl = document.querySelector('.ticker-text');
  if (tickerEl) {
    var tickerIndex = 0;
    setInterval(function () {
      tickerEl.style.opacity = '0';
      tickerEl.style.transform = 'translateY(-10px)';
      setTimeout(function () {
        tickerIndex = (tickerIndex + 1) % tickerTexts.length;
        tickerEl.textContent = tickerTexts[tickerIndex];
        tickerEl.style.opacity = '1';
        tickerEl.style.transform = 'translateY(0)';
      }, 400);
    }, 5000);
  }

  /* ──────────────────────────────────────────────
   * Lazy Image Loading + Fade In
   * ────────────────────────────────────────────── */
  document.querySelectorAll('.article-card img, .featured-article img').forEach(function (img) {
    img.addEventListener('load', function () {
      this.classList.add('loaded');
    });
    if (img.complete) {
      img.classList.add('loaded');
    }
  });

  /* ──────────────────────────────────────────────
   * Category Filter (articles page)
   * ────────────────────────────────────────────── */
  document.querySelectorAll('.filter-pill').forEach(function (pill) {
    pill.addEventListener('click', function () {
      document.querySelectorAll('.filter-pill').forEach(function (p) {
        p.classList.remove('active');
      });
      this.classList.add('active');

      var category = this.getAttribute('data-category');
      document.querySelectorAll('.article-list-item').forEach(function (item) {
        if (category === 'all' || item.getAttribute('data-category') === category) {
          item.style.display = '';
        } else {
          item.style.display = 'none';
        }
      });
    });
  });

})();
