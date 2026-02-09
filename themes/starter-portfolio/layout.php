<?php
/**
 * Starter Portfolio — Layout
 * Main HTML wrapper for all pages
 */

if (!defined('CMS_ROOT')) exit;

$themeConfig = get_theme_config();
$siteName    = get_site_name();
$siteLogo    = get_site_logo();
$bodyClass   = get_body_class();
$title = $title ?? $page["title"] ?? $siteName ?? "";
$themeDir    = '/themes/starter-portfolio';

// Theme options with defaults
$options = $themeConfig['options'] ?? [];
$showCursor     = $options['show_cursor_effect'] ?? true;
$showAnimations = $options['show_scroll_animations'] ?? true;
$headerStyle    = $options['header_style'] ?? 'minimal';
$footerText     = $options['footer_text'] ?? 'All rights reserved.';
$socialGithub   = $options['social_github'] ?? '';
$socialTwitter  = $options['social_twitter'] ?? '';
$socialLinkedin = $options['social_linkedin'] ?? '';
$socialDribbble = $options['social_dribbble'] ?? '';
$socialEmail    = $options['social_email'] ?? '';

// Google Fonts
$googleFontsUrl = "https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>

    <?php if (!empty($page)): ?>
        <?= render_seo_meta($page) ?>
    <?php endif; ?>

    <?php if ($googleFontsUrl): ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="<?= esc($googleFontsUrl) ?>" rel="stylesheet">
    <?php endif; ?>

    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="<?= $themeDir ?>/assets/css/style.css">

    <style>
        :root {
            <?= generate_theme_css_variables($themeConfig) ?>
        }
    </style>

    <?php
    // JTB Theme Builder support
    $jtbBootPath = CMS_ROOT . '/plugins/jessie-theme-builder/includes/jtb-frontend-boot.php';
    if (file_exists($jtbBootPath)) {
        require_once $jtbBootPath;
    }
    ?>
</head>
<body class="<?= esc($bodyClass) ?> header-<?= esc($headerStyle) ?><?= $showCursor ? ' custom-cursor' : '' ?><?= $showAnimations ? ' has-animations' : '' ?>">

    <!-- Custom Cursor -->
    <?php if ($showCursor): ?>
    <div class="cursor-dot" id="cursorDot"></div>
    <div class="cursor-ring" id="cursorRing"></div>
    <?php endif; ?>

    <!-- Noise Overlay -->
    <div class="noise-overlay" aria-hidden="true"></div>

    <!-- Header -->
    <header class="site-header" id="siteHeader">
        <div class="header-inner">
            <a href="/" class="site-brand">
                <?php if ($siteLogo): ?>
                    <img src="<?= esc($siteLogo) ?>" alt="<?= esc($siteName) ?>" class="site-logo">
                <?php else: ?>
                    <span class="site-name"><?= esc($siteName) ?></span>
                <?php endif; ?>
            </a>

            <nav class="site-nav" id="siteNav">
                <?= render_menu('header', ['class' => 'nav-links', 'link_class' => 'nav-link', 'wrap' => false]) ?>
            </nav>

            <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu" aria-expanded="false">
                <span class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>
        </div>
    </header>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileOverlay">
        <nav class="mobile-menu-inner">
            <?= render_menu('header', ['class' => 'mobile-nav-links', 'link_class' => 'mobile-nav-link', 'wrap' => false]) ?>
        </nav>
    </div>

    <!-- Main Content -->
    <main class="site-main" id="siteMain">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-top">
                <div class="footer-brand">
                    <a href="/" class="footer-logo-link">
                        <?php if ($siteLogo): ?>
                            <img src="<?= esc($siteLogo) ?>" alt="<?= esc($siteName) ?>" class="footer-logo">
                        <?php else: ?>
                            <span class="footer-name"><?= esc($siteName) ?></span>
                        <?php endif; ?>
                    </a>
                </div>

                <div class="footer-nav">
                    <?= render_menu('footer', ['class' => 'footer-nav-menu', 'link_class' => 'footer-nav-link', 'wrap' => false]) ?>
                </div>

                <div class="footer-social">
                    <?php if ($socialGithub): ?>
                        <a href="<?= esc($socialGithub) ?>" class="social-link" target="_blank" rel="noopener" aria-label="GitHub">
                            <i class="fab fa-github"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($socialTwitter): ?>
                        <a href="<?= esc($socialTwitter) ?>" class="social-link" target="_blank" rel="noopener" aria-label="Twitter">
                            <i class="fab fa-x-twitter"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($socialLinkedin): ?>
                        <a href="<?= esc($socialLinkedin) ?>" class="social-link" target="_blank" rel="noopener" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($socialDribbble): ?>
                        <a href="<?= esc($socialDribbble) ?>" class="social-link" target="_blank" rel="noopener" aria-label="Dribbble">
                            <i class="fab fa-dribbble"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($socialEmail): ?>
                        <a href="mailto:<?= esc($socialEmail) ?>" class="social-link" aria-label="Email">
                            <i class="fas fa-envelope"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="footer-line"></div>
                <p class="footer-copyright">
                    &copy; <?= date('Y') ?> <?= esc($siteName) ?>. <?= esc($footerText) ?>
                </p>
            </div>
        </div>
    </footer>

    <script src="<?= $themeDir ?>/assets/js/main.js"></script>
</body>
</html>
