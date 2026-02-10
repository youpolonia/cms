<?php
/**
 * Starter SaaS Theme — Layout
 * Modern SaaS landing with glassmorphism and gradient accents
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}
if (!defined('CMS_APP')) {
    define('CMS_APP', CMS_ROOT . '/app');
}

require_once CMS_APP . '/helpers/functions.php';

if (file_exists(CMS_ROOT . '/includes/helpers/menu.php')) {
    require_once CMS_ROOT . '/includes/helpers/menu.php';
}

$jtbBootPath = CMS_ROOT . '/plugins/jessie-theme-builder/includes/jtb-frontend-boot.php';
if (file_exists($jtbBootPath)) {
    require_once $jtbBootPath;
}

$themeConfig = get_theme_config();
$themeOptions = $themeConfig['options'] ?? [];
$showHeader = $themeOptions['show_header'] ?? true;
$showFooter = $themeOptions['show_footer'] ?? true;
$siteName = get_site_name();
$siteLogo = get_site_logo();
$isTbPage = !empty($page['is_tb_page']);

$pageData = $page ?? [];
if (!empty($title) && empty($pageData['title'])) {
    $pageData['title'] = $title;
}

$themeCssVariables = generate_theme_css_variables($themeConfig);
$themeDir = '/themes/starter-saas';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= render_seo_meta($pageData) ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="/assets/css/tb-frontend.css">
    <link rel="stylesheet" href="<?= $themeDir ?>/assets/css/style.css">
    <style><?= $themeCssVariables ?></style>
</head>
<body class="starter-saas <?= esc(get_body_class() ?? '') ?><?= $isTbPage ? ' tb-page' : '' ?>">

<!-- Background Effects -->
<div class="bg-grid"></div>
<div class="bg-glow"></div>

<?php if ($showHeader): ?>
<header class="site-header" id="site-header">
    <div class="container">
        <div class="header-inner">
            <a href="/" class="logo" data-ts="brand.logo">
                <?php if ($siteLogo): ?>
                    <div class="logo-icon"><img src="<?= esc($siteLogo) ?>" alt="<?= esc($siteName) ?>"></div>
                <?php else: ?>
                    <div class="logo-icon"><i class="fas fa-rocket"></i></div>
                <?php endif; ?>
                <span class="logo-text" data-ts="brand.site_name"><?= esc($siteName) ?></span>
            </a>

            <nav class="nav-main" id="nav-main">
                <?= render_menu('header', ['class' => 'nav-links', 'link_class' => 'nav-link', 'wrap' => false]) ?>
                <div class="nav-cta">
                    <a href="/contact" class="btn btn-primary btn-sm" data-ts="header.cta_text" data-ts-href="header.cta_link">Get Started <i class="fas fa-arrow-right"></i></a>
                </div>
            </nav>

            <button class="mobile-toggle" id="mobile-toggle" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</header>
<?php endif; ?>

<?php if ($isTbPage): ?>
    <?= $content ?? '' ?>
<?php else: ?>
    <main class="main-content">
        <?= $content ?? '' ?>
    </main>
<?php endif; ?>

<?php if ($showFooter): ?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="/" class="logo" data-ts="brand.logo">
                    <?php if ($siteLogo): ?>
                        <div class="logo-icon"><img src="<?= esc($siteLogo) ?>" alt="<?= esc($siteName) ?>"></div>
                    <?php else: ?>
                        <div class="logo-icon"><i class="fas fa-rocket"></i></div>
                    <?php endif; ?>
                    <span class="logo-text" data-ts="brand.site_name"><?= esc($siteName) ?></span>
                </a>
                <p data-ts="footer.description">Build something amazing with modern tools and AI-powered features.</p>
                <div class="footer-social">
                    <a href="#" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" aria-label="GitHub"><i class="fab fa-github"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" aria-label="Discord"><i class="fab fa-discord"></i></a>
                </div>
            </div>
            <div class="footer-column">
                <h4>Product</h4>
                <?= render_menu('footer', ['class' => 'footer-links', 'link_class' => 'footer-link', 'wrap' => false]) ?>
            </div>
            <div class="footer-column">
                <h4>Company</h4>
                <ul class="footer-links">
                    <li><a href="/about" class="footer-link">About</a></li>
                    <li><a href="/blog" class="footer-link">Blog</a></li>
                    <li><a href="/contact" class="footer-link">Contact</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Stay Updated</h4>
                <p class="footer-desc">Get the latest updates and news.</p>
                <form class="footer-newsletter" onsubmit="return false;">
                    <input type="email" placeholder="your@email.com" aria-label="Email">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p data-ts="footer.copyright">&copy; <?= date('Y') ?> <?= esc($siteName) ?>. All rights reserved.</p>
        </div>
    </div>
</footer>
<?php endif; ?>

<script src="<?= $themeDir ?>/assets/js/main.js"></script>
</body>
</html>
