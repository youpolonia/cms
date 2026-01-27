<?php
/**
 * JTB Theme - Main Layout
 * Full integration with Jessie Theme Builder
 *
 * @package JTB Theme
 * @version 1.0
 *
 * Variables available:
 * @var string $title - Page title
 * @var string $content - Page content
 * @var array $post - Post data (if viewing single post/page)
 * @var string $bodyClass - Additional body classes
 */

defined('CMS_ROOT') or die('Direct access not allowed');

// Check if JTB plugin is available
$jtbAvailable = class_exists('\JessieThemeBuilder\JTB_Theme_Integration');

// Get JTB Theme Settings if available
$themeSettings = [];
$cssVars = '';
if ($jtbAvailable && class_exists('\JessieThemeBuilder\JTB_Theme_Settings')) {
    $themeSettings = \JessieThemeBuilder\JTB_Theme_Settings::getAll();

    // Generate CSS Variables from JTB settings
    if (class_exists('\JessieThemeBuilder\JTB_CSS_Generator')) {
        $cssVars = \JessieThemeBuilder\JTB_CSS_Generator::generateGlobalCss($themeSettings);
    }
}

// Get combined CSS from JTB templates
$templateCss = '';
if ($jtbAvailable) {
    $templateCss = \JessieThemeBuilder\JTB_Theme_Integration::getCombinedCss();
}

// Determine current context for body class
$contextClass = 'jtb-page';
if (isset($post)) {
    if (!empty($post['type'])) {
        $contextClass = 'jtb-' . $post['type'];
    }
    if (!empty($post['slug']) && $post['slug'] === 'home') {
        $contextClass .= ' jtb-home';
    }
}
?>
<!DOCTYPE html>
<html lang="pl" class="jtb-html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= htmlspecialchars($title ?? 'JTB Theme') ?></title>

    <!-- Preconnect for Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Theme Base CSS -->
    <link rel="stylesheet" href="/themes/jtb/assets/css/style.css?v=<?= time() ?>">

    <?php if ($jtbAvailable): ?>
    <!-- JTB Frontend CSS -->
    <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/frontend.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/animations.css?v=<?= time() ?>">
    <?php endif; ?>

    <!-- CSS Variables from JTB Theme Settings -->
    <?php if (!empty($cssVars)): ?>
    <style id="jtb-theme-vars">
<?= $cssVars ?>
    </style>
    <?php endif; ?>

    <!-- JTB Template CSS -->
    <?php if (!empty($templateCss)): ?>
    <style id="jtb-template-css">
<?= $templateCss ?>
    </style>
    <?php endif; ?>
</head>
<body class="jtb-theme <?= $contextClass ?> <?= htmlspecialchars($bodyClass ?? '') ?>">

    <!-- Skip to main content (accessibility) -->
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <?php
    // ========================================
    // HEADER
    // Try JTB header template first, fallback to default
    // ========================================
    $headerRendered = false;
    if ($jtbAvailable) {
        $headerRendered = \JessieThemeBuilder\JTB_Theme_Integration::outputHeader();
    }
    if (!$headerRendered) {
        include __DIR__ . '/header.php';
    }
    ?>

    <!-- MAIN CONTENT -->
    <main id="main-content" class="jtb-site-main" role="main">
        <?php
        // ========================================
        // BODY CONTENT
        // Try JTB body template first, fallback to direct content
        // ========================================
        $bodyRendered = false;
        if ($jtbAvailable && isset($content)) {
            // Check if this page has JTB content or body template
            $bodyRendered = \JessieThemeBuilder\JTB_Theme_Integration::outputBody($content);
        }

        if (!$bodyRendered) {
            // Direct content output
            echo $content ?? '';
        }
        ?>
    </main>

    <?php
    // ========================================
    // FOOTER
    // Try JTB footer template first, fallback to default
    // ========================================
    $footerRendered = false;
    if ($jtbAvailable) {
        $footerRendered = \JessieThemeBuilder\JTB_Theme_Integration::outputFooter();
    }
    if (!$footerRendered) {
        include __DIR__ . '/footer.php';
    }
    ?>

    <?php if ($jtbAvailable): ?>
    <!-- JTB Frontend JavaScript -->
    <script src="/plugins/jessie-theme-builder/assets/js/frontend.js?v=<?= time() ?>"></script>
    <?php endif; ?>

    <!-- Theme JavaScript -->
    <script src="/themes/jtb/assets/js/theme.js?v=<?= time() ?>"></script>
</body>
</html>
