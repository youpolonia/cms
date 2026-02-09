/**
 * KnowledgeBase Theme — Main JS
 * Cmd+K search, sidebar toggle, active nav, smooth scroll
 */

(function () {
  'use strict';

  // --- Cmd+K / Ctrl+K to focus search ---
  document.addEventListener('keydown', function (e) {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
      e.preventDefault();
      var search = document.getElementById('docsSearch');
      if (search) {
        search.focus();
        search.select();
      }
    }
  });

  // --- Mobile sidebar toggle ---
  var toggle = document.querySelector('.mobile-toggle');
  var sidebar = document.getElementById('docsSidebar');

  if (toggle && sidebar) {
    toggle.addEventListener('click', function () {
      sidebar.classList.toggle('open');
      var isOpen = sidebar.classList.contains('open');
      toggle.textContent = isOpen ? '✕' : '☰';
      toggle.setAttribute('aria-expanded', isOpen);
    });
  }

  // --- Auto-highlight current page in sidebar ---
  var currentPath = window.location.pathname.replace(/\/$/, '') || '/';
  var sidebarLinks = document.querySelectorAll('.sidebar-links a');

  sidebarLinks.forEach(function (link) {
    var href = link.getAttribute('href');
    if (!href) return;
    var linkPath = href.replace(/\/$/, '') || '/';

    if (linkPath === currentPath) {
      link.classList.add('active');
    }
  });

  // --- Smooth scroll for anchor links ---
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      var targetId = this.getAttribute('href').slice(1);
      if (!targetId) return;

      var target = document.getElementById(targetId);
      if (target) {
        e.preventDefault();
        var headerHeight = 65;
        var top = target.getBoundingClientRect().top + window.pageYOffset - headerHeight - 20;
        window.scrollTo({ top: top, behavior: 'smooth' });
        history.pushState(null, '', '#' + targetId);
      }
    });
  });

  // --- Close sidebar on link click (mobile) ---
  if (sidebar) {
    sidebar.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        if (window.innerWidth <= 768) {
          sidebar.classList.remove('open');
          if (toggle) {
            toggle.textContent = '☰';
            toggle.setAttribute('aria-expanded', 'false');
          }
        }
      });
    });
  }

  // --- Escape to close search ---
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      var search = document.getElementById('docsSearch');
      if (search && document.activeElement === search) {
        search.blur();
      }
      if (sidebar && sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
        if (toggle) {
          toggle.textContent = '☰';
          toggle.setAttribute('aria-expanded', 'false');
        }
      }
    }
  });
})();
