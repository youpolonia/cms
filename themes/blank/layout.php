<?php
/**
 * Blank Canvas Theme - Layout
 * Minimal layout for full Theme Builder control
 * Supports TB Templates (Header/Footer) with Display Conditions
 * 
 * @var string $content Page content from Theme Builder
 * @var array $page Page data (optional)
 * @var string $title Page title (optional)
 */

// Load Theme Builder functions for dynamic header/footer
if (!function_exists('tb_render_site_template')) {
    $tbDatabasePath = dirname(__DIR__, 2) . '/core/theme-builder/database.php';
    if (file_exists($tbDatabasePath)) {
        require_once $tbDatabasePath;
    }
}

// Get current page context for Display Conditions
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$pageSlug = trim(parse_url($currentPath, PHP_URL_PATH) ?? '/', '/');
$pageContext = [
    'slug' => $pageSlug ?: 'home',
    'category' => $page['category'] ?? ''
];

// Try to get TB Header/Footer
$tbHeader = null;
$tbFooter = null;
if (function_exists('tb_render_site_template')) {
    $tbHeader = tb_render_site_template('header', $pageContext);
    $tbFooter = tb_render_site_template('footer', $pageContext);
}

// Theme configuration
$themeUrl = '/themes/blank';
$themePath = dirname(__FILE__);
$themeConfig = $_themeConfig ?? [];
$defaultOptions = $themeConfig['options'] ?? [];

// Load custom options from options.json (overrides theme.json defaults)
$customOptions = [];
$optionsFile = $themePath . '/options.json';
if (file_exists($optionsFile)) {
    $json = @file_get_contents($optionsFile);
    if ($json) {
        $customOptions = json_decode($json, true) ?: [];
    }
}

// Merge: custom options override defaults
$options = array_merge($defaultOptions, $customOptions);

// Load theme.json for colors (same as default theme)
$themeDesign = [];
$themeJsonFile = $themePath . '/theme.json';
if (file_exists($themeJsonFile)) {
    $themeDesign = json_decode(@file_get_contents($themeJsonFile), true) ?: [];
}

// Colors from theme.json
$colors = $themeDesign['colors'] ?? [];
$primaryColor = $colors['primary'] ?? '#1e40af';
$secondaryColor = $colors['secondary'] ?? '#3b82f6';
$accentColor = $colors['accent'] ?? '#f59e0b';
$bgColor = $colors['background'] ?? '#0f172a';
$surfaceColor = $colors['surface'] ?? '#1e293b';
$textColor = $colors['text'] ?? '#f1f5f9';
$textMuted = $colors['text_muted'] ?? '#a0a0b0';
$borderColor = $colors['border'] ?? '#2d2d3a';

// Options with defaults
$showHeader = $options['show_header'] ?? false;
$showFooter = $options['show_footer'] ?? false;
$bodyBg = $options['body_background'] ?? $bgColor;
$preloadFonts = $options['preload_fonts'] ?? false;

// Page data
$pageTitle = $title ?? ($page['title'] ?? 'Page');
$metaDesc = $page['meta_description'] ?? '';
$siteName = defined('SITE_NAME') ? SITE_NAME : 'Site';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?><?= $siteName ? ' | ' . $siteName : '' ?></title>
    <?php if ($metaDesc): ?>
    <meta name="description" content="<?= htmlspecialchars($metaDesc) ?>">
    <?php endif; ?>
    
    <?php if ($preloadFonts): ?>
    <!-- Preload fonts if enabled -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php endif; ?>
    
    <!-- Core CSS - Theme Builder styles -->
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="/public/assets/css/tb-frontend.css">
    
    <!-- Blank Theme minimal CSS -->
    <link rel="stylesheet" href="<?= $themeUrl ?>/assets/css/style.css">
    
    <style>
        :root {
            --primary: <?= htmlspecialchars($primaryColor) ?>;
            --color-primary: <?= htmlspecialchars($primaryColor) ?>;
            --secondary: <?= htmlspecialchars($secondaryColor) ?>;
            --color-secondary: <?= htmlspecialchars($secondaryColor) ?>;
            --accent: <?= htmlspecialchars($accentColor) ?>;
            --color-accent: <?= htmlspecialchars($accentColor) ?>;
            --background: <?= htmlspecialchars($bgColor) ?>;
            --color-background: <?= htmlspecialchars($bgColor) ?>;
            --surface: <?= htmlspecialchars($surfaceColor) ?>;
            --color-surface: <?= htmlspecialchars($surfaceColor) ?>;
            --text: <?= htmlspecialchars($textColor) ?>;
            --color-text: <?= htmlspecialchars($textColor) ?>;
            --text-muted: <?= htmlspecialchars($textMuted) ?>;
            --color-text-muted: <?= htmlspecialchars($textMuted) ?>;
            --border: <?= htmlspecialchars($borderColor) ?>;
            --color-border: <?= htmlspecialchars($borderColor) ?>;
        }
        body { background-color: <?= htmlspecialchars($bodyBg) ?>; color: var(--text); }
    </style>
</head>
<body class="blank-theme<?= ($showHeader || $tbHeader) ? '' : ' no-header' ?><?= ($showFooter || $tbFooter) ? '' : ' no-footer' ?>">

<?php if ($tbHeader): ?>
    <!-- Theme Builder Header -->
    <?= $tbHeader ?>
<?php elseif ($showHeader): ?>
    <!-- Fallback Minimal Header -->
    <header class="blank-header">
        <div class="blank-header-inner">
            <a href="/" class="blank-logo"><?= htmlspecialchars($siteName) ?></a>
            <?php if (function_exists('cms_nav_menu')): ?>
            <nav class="blank-nav">
                <?= cms_nav_menu(['location' => 'header', 'menu_class' => 'blank-menu', 'echo' => false]) ?>
            </nav>
            <?php endif; ?>
        </div>
    </header>
<?php endif; ?>

    <!-- Main Content - Full Theme Builder Control -->
    <main class="blank-main">
        <?= $content ?? '' ?>
    </main>

<?php if ($tbFooter): ?>
    <!-- Theme Builder Footer -->
    <?= $tbFooter ?>
<?php elseif ($showFooter): ?>
    <!-- Fallback Minimal Footer -->
    <footer class="blank-footer">
        <div class="blank-footer-inner">
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($siteName) ?></p>
        </div>
    </footer>
<?php endif; ?>

    <!-- Minimal JS -->
    <script src="<?= $themeUrl ?>/assets/js/main.js"></script>
</body>
</html>
