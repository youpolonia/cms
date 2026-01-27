<?php
/**
 * Jessie AI-CMS - Footer Component
 * Supports Theme Builder custom footers with Display Conditions
 */

// Load Theme Builder functions for dynamic footer
if (!function_exists('tb_render_site_template')) {
    $tbDatabasePath = dirname(__DIR__, 4) . '/core/theme-builder/database.php';
    if (file_exists($tbDatabasePath)) {
        require_once $tbDatabasePath;
    }
}

// Get current page context for Display Conditions (reuse from header if available)
if (!isset($pageContext)) {
    $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
    $urlPath = trim(parse_url($currentPath, PHP_URL_PATH) ?? '/', '/');
    // Extract actual slug from URL patterns like /page/slug or just /slug
    if (preg_match('#^page/(.+)$#', $urlPath, $m)) {
        $pageSlug = $m[1];
    } else {
        $pageSlug = $urlPath;
    }
    // Also check if page data is available from controller
    if (isset($page['slug']) && !empty($page['slug'])) {
        $pageSlug = $page['slug'];
    }
    $pageContext = [
        'slug' => $pageSlug ?: 'home',
        'category' => $pageCategory ?? ''
    ];
}

// Try to get TB Footer matching current page conditions
$tbFooter = null;
if (function_exists('tb_render_site_template')) {
    $tbFooter = tb_render_site_template('footer', $pageContext);
}
?>
    </main>
    <?php if ($tbFooter): ?>
        <!-- Theme Builder Footer -->
        <?= $tbFooter ?>
    <?php else: ?>
        <!-- Static Fallback Footer -->
        <footer class="site-footer">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-brand">
                        <a href="/" class="logo">
                            <span class="logo-icon">🤖</span>
                            <span class="logo-text">Jessie</span>
                        </a>
                        <p>The intelligent content management system powered by AI.</p>
                    </div>
                    <div class="footer-links">
                        <h4>Product</h4>
                        <ul>
                            <li><a href="/features">Features</a></li>
                            <li><a href="/articles">Blog</a></li>
                        </ul>
                    </div>
                    <div class="footer-links">
                        <h4>Company</h4>
                        <ul>
                            <li><a href="/about">About</a></li>
                            <li><a href="/contact">Contact</a></li>
                        </ul>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; <?= date('Y') ?> Jessie AI-CMS. Built with pure PHP.</p>
                </div>
            </div>
        </footer>
    <?php endif; ?>
    <style>
        .site-footer { background: var(--bg-secondary); border-top: 1px solid var(--border); padding: 60px 0 30px; margin-top: 80px; }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 48px; }
        @media (max-width: 768px) { .footer-grid { grid-template-columns: 1fr; gap: 32px; } }
        .footer-brand .logo { margin-bottom: 16px; }
        .footer-brand p { font-size: 0.9rem; color: var(--text-muted); }
        .footer-links h4 { font-size: 0.85rem; font-weight: 600; color: var(--text-primary); margin-bottom: 16px; text-transform: uppercase; }
        .footer-links ul { list-style: none; }
        .footer-links li { margin-bottom: 10px; }
        .footer-links a { color: var(--text-muted); font-size: 0.9rem; transition: color 0.2s; }
        .footer-links a:hover { color: var(--text-primary); }
        .footer-bottom { margin-top: 40px; padding-top: 20px; border-top: 1px solid var(--border); text-align: center; }
        .footer-bottom p { color: var(--text-muted); font-size: 0.85rem; }
    </style>
    <script>
        const header = document.getElementById('site-header');
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 50);
        });

        // Theme Builder Animations Handler
        (function() {
            'use strict';
            function initAnimations() {
                var elements = document.querySelectorAll('[style*="animation:"]');
                if (!elements.length) return;

                var supportsObserver = 'IntersectionObserver' in window;

                elements.forEach(function(el) {
                    var style = el.getAttribute('style') || '';
                    var match = style.match(/animation:\s*([^;]+)/);
                    if (!match) return;

                    var animValue = match[1];
                    var scrollTrigger = el.dataset.scrollTrigger === 'true';
                    var triggerPoint = parseInt(el.dataset.triggerPoint || '80', 10);
                    var animateOnce = el.dataset.animateOnce !== 'false';

                    if (scrollTrigger && supportsObserver) {
                        el.style.opacity = '0';
                        el.style.animation = 'none';

                        var observer = new IntersectionObserver(function(entries) {
                            entries.forEach(function(entry) {
                                if (entry.isIntersecting) {
                                    entry.target.style.opacity = '';
                                    entry.target.style.animation = animValue;
                                    if (animateOnce) observer.unobserve(entry.target);
                                } else if (!animateOnce) {
                                    entry.target.style.opacity = '0';
                                    entry.target.style.animation = 'none';
                                }
                            });
                        }, { threshold: triggerPoint / 100 });

                        observer.observe(el);
                    }
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAnimations);
            } else {
                initAnimations();
            }
        })();
    </script>
</body>
</html>
