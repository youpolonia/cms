<?php
/**
 * Jessie AI-CMS Theme - Main Layout
 * @var string $title Page title
 * @var string $content Main content
 */
$themeUrl = '/themes/jessie';

// Helper function for widget regions (stub if not defined)
if (!function_exists('render_widget_region')) {
    function render_widget_region(string $region): string {
        // Default navigation for header_menu
        if ($region === 'header_menu') {
            return '<ul class="nav-links">
                <li><a href="/">Home</a></li>
                <li><a href="/blog">Blog</a></li>
                <li><a href="/page/about">About</a></li>
                <li><a href="/page/contact">Contact</a></li>
            </ul>';
        }
        return '';
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Jessie AI-CMS') ?></title>
    <meta name="description" content="<?= htmlspecialchars($description ?? 'Jessie AI-CMS - Intelligent Content Management') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $themeUrl ?>/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="/assets/css/tb-frontend.css">
</head>
<body>
    <div class="bg-grid"></div>
    <div class="bg-glow"></div>

    <header class="site-header" id="site-header">
        <div class="container">
            <div class="header-inner">
                <a href="/" class="logo">
                    <span class="logo-icon"><img src="<?= $themeUrl ?>/assets/images/jessie-logo.svg" alt="Jessie" width="40" height="40"></span>
                    <span class="logo-text">Jessie</span>
                </a>
                <button class="mobile-toggle" onclick="document.querySelector('.nav-main').classList.toggle('open')">☰</button>
                <nav class="nav-main">
                    <?= render_widget_region('header_menu') ?>
                    <div class="nav-cta">
                        <a href="/admin/login" class="btn btn-primary">Get Started →</a>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <main class="main-content">
        <?= $content ?? '' ?>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="/" class="logo">
                        <span class="logo-icon"><img src="<?= $themeUrl ?>/assets/images/jessie-logo.svg" alt="Jessie" width="40" height="40"></span>
                        <span class="logo-text">Jessie</span>
                    </a>
                    <p>The intelligent content management system powered by AI.</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Jessie AI-CMS. Built with ❤️ and pure PHP.</p>
            </div>
        </div>
    </footer>

    <script>
        const header = document.getElementById('site-header');
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 50);
        });
    </script>
</body>
</html>
