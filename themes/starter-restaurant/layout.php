<?php
/**
 * Starter Restaurant Theme Layout
 * Elegant dining experience with warm gold tones and serif typography
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}
if (!defined('CMS_APP')) {
    define('CMS_APP', CMS_ROOT . '/app');
}

// Load helpers
require_once CMS_APP . '/helpers/functions.php';

// Load menu helper
if (file_exists(CMS_ROOT . '/includes/helpers/menu.php')) {
    require_once CMS_ROOT . '/includes/helpers/menu.php';
}

// Load JTB for custom headers/footers
$jtbBootPath = CMS_ROOT . '/plugins/jessie-theme-builder/includes/jtb-frontend-boot.php';
if (file_exists($jtbBootPath)) {
    require_once $jtbBootPath;
}

// Get theme config
$themeConfig = get_theme_config();
$themeOptions = $themeConfig['options'] ?? [];

// Options
$showHeader = $themeOptions['show_header'] ?? true;
$showFooter = $themeOptions['show_footer'] ?? true;
$preloadFonts = $themeOptions['preload_fonts'] ?? true;

// Site info
$siteName = get_site_name();
$siteLogo = get_site_logo();

// Page context
$pageSlug = $page['slug'] ?? '';
$pageContext = [
    'slug' => $pageSlug ?: 'home',
    'category' => $pageCategory ?? ''
];

// Default theme uses its own header/footer
$tbHeader = null;
$tbFooter = null;

// SEO data
$pageData = $page ?? [];
if (!empty($title) && empty($pageData['title'])) {
    $pageData['title'] = $title;
}

// Is this a TB page?
$isTbPage = !empty($page['is_tb_page']);

// Generate CSS variables from theme.json
$themeCssVariables = generate_theme_css_variables($themeConfig);

// Theme path
$themePath = '/themes/starter-restaurant';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= render_seo_meta($pageData) ?>
    <?php if ($preloadFonts): ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php endif; ?>
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="/assets/css/tb-frontend.css">
    <link rel="stylesheet" href="<?= $themePath ?>/assets/css/style.css">
    <style>
<?= $themeCssVariables ?>
<?= generate_studio_css_overrides() ?>
    </style>
<?= function_exists("theme_render_favicon") ? theme_render_favicon() : "" ?>
<?= function_exists("theme_render_og_image") ? theme_render_og_image() : "" ?>
</head>
<body class="<?= esc(get_body_class() ?? '') ?><?= $isTbPage ? ' tb-page' : '' ?>">
<?= function_exists("theme_render_announcement_bar") ? theme_render_announcement_bar() : "" ?>

<?php if ($showHeader): ?>
    <?php if ($tbHeader): ?>
        <?= $tbHeader ?>
    <?php else: ?>
    <!-- Restaurant Header -->
    <header class="site-header" id="siteHeader">
        <div class="header-container">
            <?php $tsLogo = theme_get('brand.logo') ?: $siteLogo; ?>
            <a href="/" class="header-logo" data-ts="brand.logo">
                <?php if ($tsLogo): ?>
                    <img src="<?= esc($tsLogo) ?>" alt="<?= esc(theme_get('brand.site_name', $siteName)) ?>">
                <?php else: ?>
                    <span class="logo-text" data-ts="brand.site_name"><?= esc(theme_get('brand.site_name', $siteName)) ?></span>
                <?php endif; ?>
            </a>
            <nav class="header-nav" id="headerNav">
                <?= render_menu('header', ['class' => 'nav-links', 'link_class' => 'nav-link', 'wrap' => false]) ?>
            </nav>
            <?php $showCta = theme_get('header.show_cta', true); ?>
            <?php if ($showCta): ?>
            <a href="<?= esc(theme_get('header.cta_link', '#reservation')) ?>" class="header-cta" data-ts="header.cta_text" data-ts-href="header.cta_link"><?= esc(theme_get('header.cta_text', 'Reserve a Table')) ?></a>
            <?php endif; ?>
            <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($isTbPage): ?>
    <?= $content ?? '' ?>
<?php else: ?>
    <main>
        <?= $content ?? '' ?>
    </main>
<?php endif; ?>

<?php if ($showFooter): ?>
    <?php if ($tbFooter): ?>
        <?= $tbFooter ?>
    <?php else: ?>
    <!-- Restaurant Footer -->
    <footer class="site-footer">
        <div class="footer-top">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-brand">
                        <a href="/" class="footer-logo" data-ts="brand.logo">
                            <?php if ($tsLogo): ?>
                                <img src="<?= esc($tsLogo) ?>" alt="<?= esc(theme_get('brand.site_name', $siteName)) ?>">
                            <?php else: ?>
                                <span class="logo-text" data-ts="brand.site_name"><?= esc(theme_get('brand.site_name', $siteName)) ?></span>
                            <?php endif; ?>
                        </a>
                        <p class="footer-tagline" data-ts="footer.description"><?= esc(theme_get('footer.description', 'An exquisite dining experience where tradition meets innovation. Every dish tells a story of passion and craftsmanship.')) ?></p>
                        <div class="footer-social">
                            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" aria-label="TripAdvisor"><i class="fab fa-tripadvisor"></i></a>
                        </div>
                    </div>
                    <div class="footer-hours">
                        <h4>Opening Hours</h4>
                        <ul class="hours-list">
                            <li><span>Monday – Friday</span><span>11:00 – 22:00</span></li>
                            <li><span>Saturday</span><span>10:00 – 23:00</span></li>
                            <li><span>Sunday</span><span>10:00 – 21:00</span></li>
                        </ul>
                    </div>
                    <div class="footer-contact">
                        <h4>Contact Us</h4>
                        <ul class="contact-list">
                            <li><i class="fas fa-map-marker-alt"></i> 42 Gourmet Avenue, London</li>
                            <li><i class="fas fa-phone"></i> +44 20 7946 0958</li>
                            <li><i class="fas fa-envelope"></i> hello@restaurant.com</li>
                        </ul>
                    </div>
                    <div class="footer-nav">
                        <h4>Quick Links</h4>
                        <?= render_menu('footer', ['class' => 'nav-menu', 'link_class' => 'nav-link', 'wrap' => false]) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p data-ts="footer.copyright"><?= theme_get('footer.copyright') ? esc(theme_get('footer.copyright')) : '&copy; ' . date('Y') . ' ' . esc(theme_get('brand.site_name', $siteName)) . '. All rights reserved.' ?></p>
            </div>
        </div>
    </footer>
    <?php endif; ?>
<?php endif; ?>

<script src="<?= $themePath ?>/assets/js/main.js"></script>
</body>
</html>
