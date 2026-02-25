<?php
/**
 * Jessie CMS Demo — Layout
 * Official showcase theme
 */
if (!defined('CMS_ROOT')) define('CMS_ROOT', dirname(__DIR__, 2));
if (!defined('CMS_APP')) define('CMS_APP', CMS_ROOT . '/app');

require_once CMS_APP . '/helpers/functions.php';
if (file_exists(CMS_ROOT . '/includes/helpers/menu.php')) {
    require_once CMS_ROOT . '/includes/helpers/menu.php';
}

$jtbBootPath = CMS_ROOT . '/plugins/jessie-theme-builder/includes/jtb-frontend-boot.php';
if (file_exists($jtbBootPath)) require_once $jtbBootPath;

$themeConfig = get_theme_config();
$themeOptions = $themeConfig['options'] ?? [];
$showHeader = $themeOptions['show_header'] ?? true;
$showFooter = $themeOptions['show_footer'] ?? true;

$siteName = theme_get('brand.site_name', get_site_name());
$siteLogo = theme_get('brand.logo', get_site_logo());
$pageData = $page ?? [];
if (!empty($title) && empty($pageData['title'])) $pageData['title'] = $title;
$isTbPage = !empty($page['is_tb_page']);

$themeCssVariables = generate_theme_css_variables($themeConfig);
$themePath = '/themes/' . basename(__DIR__);
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
    <link rel="stylesheet" href="<?= $themePath ?>/assets/css/style.css?v=<?= @filemtime(__DIR__ . '/assets/css/style.css') ?: time() ?>">
    <style><?= $themeCssVariables ?><?= generate_studio_css_overrides() ?></style>
    <?= function_exists('theme_render_favicon') ? theme_render_favicon() : '' ?>
    <?= function_exists('theme_render_og_image') ? theme_render_og_image() : '' ?>
</head>
<body class="jd-body <?= esc(get_body_class() ?? '') ?><?= $isTbPage ? ' tb-page' : '' ?>">

<?php if ($showHeader): ?>
<a href="#main-content" class="skip-nav">Skip to content</a>
<header id="siteHeader" class="jd-header">
  <div class="jd-header-inner">
    <a href="/" class="jd-brand">
      <img src="<?= $themePath ?>/assets/img/jessie-logo.svg" alt="Jessie CMS" class="jd-brand-logo">
      <span class="jd-brand-text">Jessie<span class="jd-brand-accent">CMS</span></span>
    </a>
    <nav id="headerNav" class="jd-nav">
      <ul class="jd-nav-list">
        <li><a href="/#features" class="jd-nav-link">Features</a></li>
        <li><a href="/#plugins" class="jd-nav-link">Plugins</a></li>
        <li><a href="/#saas" class="jd-nav-link">SaaS Tools</a></li>
        <li><a href="/demo-features" class="jd-nav-link">Full Features</a></li>
        <li><a href="/demo-pricing" class="jd-nav-link">Pricing</a></li>
      </ul>
    </nav>
    <div class="jd-header-actions">
      <a href="/admin" class="jd-header-cta">Admin Panel →</a>
      <button id="mobileToggle" class="jd-burger" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>
<div class="mobile-overlay" id="mobileOverlay"></div>
<?php endif; ?>

<?php if ($isTbPage): ?>
    <?= $content ?? '' ?>
<?php else: ?>
    <main id="main-content"><?= $content ?? '' ?></main>
<?php endif; ?>

<?php if ($showFooter): ?>
<footer class="jd-footer">
  <div class="jd-footer-inner">
    <div class="jd-footer-grid">
      <div class="jd-footer-brand-col">
        <a href="/" class="jd-brand">
          <img src="<?= $themePath ?>/assets/img/jessie-logo.svg" alt="Jessie CMS" class="jd-brand-logo">
          <span class="jd-brand-text">Jessie<span class="jd-brand-accent">CMS</span></span>
        </a>
        <p class="jd-footer-desc">AI-powered CMS with 18 plugins, 6 SaaS tools, and drag & drop page builder. Built with love, named after a beloved dog.</p>
      </div>
      <div class="jd-footer-links-col">
        <h4>Product</h4>
        <ul>
          <li><a href="/#features">Features</a></li>
          <li><a href="/#plugins">Plugins</a></li>
          <li><a href="/#saas">SaaS Tools</a></li>
          <li><a href="/demo-pricing">Pricing</a></li>
        </ul>
      </div>
      <div class="jd-footer-links-col">
        <h4>Plugins</h4>
        <ul>
          <li><a href="/#plugins">E-Commerce</a></li>
          <li><a href="/#plugins">Booking</a></li>
          <li><a href="/#plugins">LMS</a></li>
          <li><a href="/#plugins">CRM</a></li>
        </ul>
      </div>
      <div class="jd-footer-links-col">
        <h4>Resources</h4>
        <ul>
          <li><a href="/admin">Admin Panel</a></li>
          <li><a href="/demo-about">About</a></li>
          <li><a href="/demo-pricing">Pricing</a></li>
        </ul>
      </div>
    </div>
  </div>
  <div class="jd-footer-bottom">
    <p>&copy; <?= date('Y') ?> Jessie CMS. Built with ❤️ in memory of Jessie 🐕</p>
  </div>
</footer>
<?php endif; ?>

<script src="<?= $themePath ?>/assets/js/main.js"></script>
</body>
</html>
