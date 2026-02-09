<?php
/**
 * Default Theme Layout - UNIFIED
 * Uses generate_theme_css_variables() for theme.json colors
 * Supports TB custom headers/footers with Display Conditions
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}
if (!defined('CMS_APP')) {
    define('CMS_APP', CMS_ROOT . '/app');
}

// Load helpers (includes generate_theme_css_variables)
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

// Page context for TB Display Conditions
$pageSlug = $page['slug'] ?? '';
$pageContext = [
    'slug' => $pageSlug ?: 'home',
    'category' => $pageCategory ?? ''
];

// DEFAULT THEME: Uses its own header/footer (fallback only)
// TB Site Templates are used EXCLUSIVELY by Blank Canvas theme
// This ensures clean separation:
//   - Default/Jessie themes = traditional PHP templates
//   - Blank Canvas = full Theme Builder control (headers, footers, pages)
$tbHeader = null;
$tbFooter = null;
// DO NOT call tb_render_site_template() - default theme always uses fallback

// SEO data
$pageData = $page ?? [];
if (!empty($title) && empty($pageData['title'])) {
    $pageData['title'] = $title;
}

// Is this a TB page?
$isTbPage = !empty($page['is_tb_page']);

// Generate CSS variables from theme.json
$themeCssVariables = generate_theme_css_variables($themeConfig);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= render_seo_meta($pageData) ?>
    <?php if ($preloadFonts): ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php endif; ?>
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="/assets/css/tb-frontend.css">
    <style>
<?= $themeCssVariables ?>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { 
            font-family: var(--font-family); 
            font-size: var(--font-size-base); 
            line-height: var(--line-height); 
            color: var(--text); 
            background: var(--background); 
        }
        a { color: var(--primary); text-decoration: none; }
        a:hover { color: var(--accent); }
        
        /* Fallback header/footer styles */
        .site-header-fallback { 
            background: var(--surface); 
            padding: 20px 0; 
        }
        .site-header-fallback .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 0 20px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .site-header-fallback .logo { 
            color: var(--text); 
            font-size: 1.5rem; 
            font-weight: 700; 
            text-decoration: none; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        .site-header-fallback .logo img { height: 40px; width: auto; }
        .site-header-fallback .nav-menu { 
            list-style: none; 
            display: flex; 
            gap: 30px; 
            margin: 0; 
            padding: 0; 
        }
        .site-header-fallback .nav-link { color: var(--text); text-decoration: none; }
        .site-header-fallback .nav-link:hover { color: var(--primary); }
        
        .site-footer-fallback { 
            background: var(--surface); 
            color: var(--text); 
            padding: 40px 0; 
            margin-top: 60px; 
            text-align: center; 
        }
        .site-footer-fallback .nav-menu { 
            list-style: none; 
            display: flex; 
            justify-content: center; 
            gap: 20px; 
            margin: 0 0 20px 0; 
            padding: 0; 
        }
        .site-footer-fallback .nav-link { color: var(--text-muted); text-decoration: none; }
        .site-footer-fallback .nav-link:hover { color: var(--text); }
        
        /* TB page adjustments */
        body.tb-page .site-footer-fallback { margin-top: 0; }
    </style>
</head>
<body class="<?= esc(get_body_class() ?? '') ?><?= $isTbPage ? ' tb-page' : '' ?>">

<?php if ($showHeader): ?>
    <?php if ($tbHeader): ?>
        <!-- TB Custom Header -->
        <?= $tbHeader ?>
    <?php else: ?>
        <!-- Fallback Header -->
        <header class="site-header-fallback">
            <div class="container">
                <a href="/" class="logo">
                    <?php if ($siteLogo): ?><img src="<?= esc($siteLogo) ?>" alt="<?= esc($siteName) ?>"><?php endif; ?>
                    <span><?= esc($siteName) ?></span>
                </a>
                <?= render_menu('header', ['class' => 'nav-menu', 'link_class' => 'nav-link']) ?>
            </div>
        </header>
    <?php endif; ?>
<?php endif; ?>

<?php if ($isTbPage): ?>
    <!-- TB Page - content controls layout -->
    <?= $content ?? '' ?>
<?php else: ?>
    <main>
        <?= $content ?? '' ?>
    </main>
<?php endif; ?>

<?php if ($showFooter): ?>
    <?php if ($tbFooter): ?>
        <!-- TB Custom Footer -->
        <?= $tbFooter ?>
    <?php else: ?>
        <!-- Fallback Footer -->
        <footer class="site-footer-fallback">
            <?= render_menu('footer', ['class' => 'nav-menu', 'link_class' => 'nav-link']) ?>
            <p>&copy; <?= date('Y') ?> <?= esc($siteName) ?>. All rights reserved.</p>
        </footer>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>
