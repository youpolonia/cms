<?php
/**
 * Default Theme Layout
 * Static header/footer with theme colors from style.css
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

// Load theme.json for non-color options only
$themeDesign = [];
if (file_exists(__DIR__ . '/theme.json')) {
    $themeDesign = json_decode(@file_get_contents(__DIR__ . '/theme.json'), true) ?: [];
}

// Load options.json
$themeOptions = [];
if (file_exists(__DIR__ . '/options.json')) {
    $themeOptions = json_decode(@file_get_contents(__DIR__ . '/options.json'), true) ?: [];
}

// Options
$showHeader = $themeOptions['show_header'] ?? true;
$showFooter = $themeOptions['show_footer'] ?? true;
$preloadFonts = $themeOptions['preload_fonts'] ?? true;

// Site info
$siteName = get_site_name();
$siteLogo = get_site_logo();

// SEO
$pageData = $page ?? [];
if (!empty($title) && empty($pageData['title'])) {
    $pageData['title'] = $title;
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= render_seo_meta($pageData) ?>
    <?php if ($preloadFonts): ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Inter:wght@400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php endif; ?>
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="/assets/css/tb-frontend.css">
    <link rel="stylesheet" href="/themes/default/assets/css/style.css">
    <style>
        :root {
            --primary: var(--color-primary);
            --secondary: var(--color-secondary);
            --accent: var(--color-accent);
            --background: var(--color-background);
            --surface: var(--color-surface);
            --text: var(--color-text);
            --text-muted: var(--color-text-muted);
            --border: var(--color-border);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--font-family, 'Inter', sans-serif); line-height: var(--line-height, 1.6); color: var(--color-text); background: var(--color-background); }
        body.tb-page { background: transparent !important; }
        body.tb-page .site-header { position: absolute; top: 0; left: 0; right: 0; z-index: 100; background: transparent !important; }
        body.tb-page .site-footer { margin-top: 0; }
        .tb-section { position: relative; z-index: 1; }
        .tb-section-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 1; }
        .tb-section-inner { position: relative; z-index: 2; }
        a { color: var(--color-primary); }
        a:hover { color: var(--color-accent); }
        .site-header { background: var(--color-surface); padding: 20px 0; }
        .site-header .container { max-width: var(--container-width, 1200px); margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
        .site-header .logo { color: var(--color-text); font-size: 1.5rem; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .site-header .logo img { height: 40px; width: auto; }
        .site-header .nav-menu { list-style: none; display: flex; gap: 30px; margin: 0; padding: 0; }
        .site-header .nav-link { color: var(--color-text); text-decoration: none; }
        .site-header .nav-link:hover { color: var(--color-accent); }
        .site-footer { background: var(--color-surface); color: var(--color-text); padding: 40px 0; margin-top: 60px; text-align: center; }
        .site-footer .nav-menu { list-style: none; display: flex; justify-content: center; gap: 20px; margin: 0 0 20px 0; padding: 0; }
        .site-footer .nav-link { color: var(--color-text-muted); text-decoration: none; }
        .site-footer .nav-link:hover { color: var(--color-text); }
    </style>
</head>
<?php $isTbPage = !empty($page["is_tb_page"]); ?>
<body class="<?= esc(get_body_class()) ?><?= $isTbPage ? ' tb-page' : '' ?>">

<?php if ($showHeader): ?>
<header class="site-header">
    <div class="container">
        <a href="/" class="logo">
            <?php if ($siteLogo): ?><img src="<?= esc($siteLogo) ?>" alt="<?= esc($siteName) ?>"><?php endif; ?>
            <span><?= esc($siteName) ?></span>
        </a>
        <?= render_menu('header', ['class' => 'nav-menu', 'link_class' => 'nav-link']) ?>
    </div>
</header>
<?php endif; ?>

<?php if ($isTbPage): ?>
    <!-- TB Page - content controls layout, no wrapper -->
<?= $content ?? '' ?>
<?php else: ?>
<main>
    <?= $content ?? '' ?>
</main>
<?php endif; ?>

<?php if ($showFooter): ?>
<footer class="site-footer">
    <?= render_menu('footer', ['class' => 'nav-menu', 'link_class' => 'nav-link']) ?>
    <p>&copy; <?= date('Y') ?> <?= esc($siteName) ?>. All rights reserved.</p>
</footer>
<?php endif; ?>

</body>
</html>
