<?php
/**
 * JTB Theme - Blank Template
 * Used for pages built entirely with JTB Theme Builder
 * No header, footer, or wrapper - just the builder content
 *
 * @package JTB Theme
 *
 * Variables:
 * @var string $title - Page title
 * @var string $content - Page content (JTB builder content)
 */

defined('CMS_ROOT') or die('Direct access not allowed');

// Check if JTB is available
$jtbAvailable = class_exists('\JessieThemeBuilder\JTB_Theme_Integration');

// Get CSS from JTB
$templateCss = '';
$cssVars = '';
if ($jtbAvailable) {
    $templateCss = \JessieThemeBuilder\JTB_Theme_Integration::getCombinedCss();

    if (class_exists('\JessieThemeBuilder\JTB_Theme_Settings')) {
        $themeSettings = \JessieThemeBuilder\JTB_Theme_Settings::getAll();
        if (class_exists('\JessieThemeBuilder\JTB_CSS_Generator')) {
            $cssVars = \JessieThemeBuilder\JTB_CSS_Generator::generateGlobalCss($themeSettings);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl" class="jtb-html jtb-blank-template">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Page') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/themes/jtb/assets/css/style.css?v=<?= time() ?>">

    <?php if ($jtbAvailable): ?>
    <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/frontend.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/animations.css?v=<?= time() ?>">
    <?php endif; ?>

    <?php if (!empty($cssVars)): ?>
    <style id="jtb-theme-vars"><?= $cssVars ?></style>
    <?php endif; ?>

    <?php if (!empty($templateCss)): ?>
    <style id="jtb-template-css"><?= $templateCss ?></style>
    <?php endif; ?>
</head>
<body class="jtb-theme jtb-blank-page">

    <?= $content ?? '' ?>

    <?php if ($jtbAvailable): ?>
    <script src="/plugins/jessie-theme-builder/assets/js/frontend.js?v=<?= time() ?>"></script>
    <?php endif; ?>
    <script src="/themes/jtb/assets/js/theme.js?v=<?= time() ?>"></script>
</body>
</html>
