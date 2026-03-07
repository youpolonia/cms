<?php
/**
 * JTB Theme — Layout
 * Full Theme Builder integration: headers, footers and page content
 * rendered from JTB templates. Falls back to minimal defaults if no
 * templates are configured.
 *
 * @package JessieThemeBuilder
 * @since 2026-03-05
 */

if (!defined('CMS_ROOT')) define('CMS_ROOT', dirname(__DIR__, 2));
if (!defined('CMS_APP'))  define('CMS_APP', CMS_ROOT . '/app');

require_once CMS_APP . '/helpers/functions.php';
if (file_exists(CMS_ROOT . '/includes/helpers/menu.php')) {
    require_once CMS_ROOT . '/includes/helpers/menu.php';
}

// ── Load JTB Frontend ──
$jtbBootPath = CMS_ROOT . '/plugins/jessie-theme-builder/includes/jtb-frontend-boot.php';
if (file_exists($jtbBootPath)) {
    require_once $jtbBootPath;
}

// ── Theme Config ──
$themeConfig = get_theme_config();
$themeCssVariables = generate_theme_css_variables($themeConfig);
$themePath = '/themes/' . basename(__DIR__);

// ── Site Info ──
$siteName = get_site_name();
$siteLogo = get_site_logo();

// ── Page Data ──
$pageData = $page ?? [];
if (!empty($title) && empty($pageData['title'])) {
    $pageData['title'] = $title;
}

// ── JTB Templates ──
$jtbAvailable = class_exists('\JessieThemeBuilder\JTB_Theme_Integration');
$hasJtbHeader = $jtbAvailable && \JessieThemeBuilder\JTB_Theme_Integration::hasHeader();
$hasJtbFooter = $jtbAvailable && \JessieThemeBuilder\JTB_Theme_Integration::hasFooter();

// Set dynamic context for theme modules (post data, etc.)
if ($jtbAvailable && class_exists('\JessieThemeBuilder\JTB_Dynamic_Context')) {
    $ctx = [];
    if (!empty($page)) {
        $ctx['post'] = $page;
        $ctx['post_id'] = $page['id'] ?? 0;
    }
    $ctx['site_title'] = $siteName;
    $ctx['site_logo']  = $siteLogo;
    \JessieThemeBuilder\JTB_Dynamic_Context::set($ctx);
}

// ── JTB Page Content (if page was built with JTB) ──
$jtbPageContent = '';
$isJtbPage = false;
if ($jtbAvailable && !empty($page['id'])) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT content FROM jtb_pages WHERE post_id = ? LIMIT 1");
    $stmt->execute([(int)$page['id']]);
    $jtbRow = $stmt->fetch(\PDO::FETCH_ASSOC);
    if ($jtbRow && !empty($jtbRow['content'])) {
        $decoded = json_decode($jtbRow['content'], true);
        if (is_array($decoded) && !empty($decoded['content'])) {
            $isJtbPage = true;
            $jtbPageContent = \JessieThemeBuilder\JTB_Renderer::render($decoded, ['context' => 'frontend']);
        }
    }
}

// ── Collect CSS for <head> ──
$jtbHeadCss = '';
if ($jtbAvailable && class_exists('\JessieThemeBuilder\JTB_CSS_Output')) {
    $jtbHeadCss = \JessieThemeBuilder\JTB_CSS_Output::getCss();
}

// ── Theme Settings CSS (global styles from JTB Theme Settings) ──
$themeSettingsCss = '';
if ($jtbAvailable && class_exists('\JessieThemeBuilder\JTB_Style_System')) {
    $styleSystem = \JessieThemeBuilder\JTB_Style_System::getInstance();
    $themeSettingsCss = $styleSystem->getGlobalCss();
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= function_exists('render_seo_meta') ? render_seo_meta($pageData) : '' ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="<?= $themePath ?>/assets/css/style.css?v=<?= @filemtime(__DIR__ . '/assets/css/style.css') ?: time() ?>">
    <?php
    // JTB frontend CSS
    $jtbFrontendCss = CMS_ROOT . '/plugins/jessie-theme-builder/assets/css/frontend.css';
    if (file_exists($jtbFrontendCss)): ?>
    <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/frontend.css?v=<?= @filemtime($jtbFrontendCss) ?: time() ?>">
    <?php endif; ?>
    <?php
    $jtbBaseModulesCss = CMS_ROOT . '/plugins/jessie-theme-builder/assets/css/jtb-base-modules.css';
    if (file_exists($jtbBaseModulesCss)): ?>
    <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/jtb-base-modules.css?v=<?= @filemtime($jtbBaseModulesCss) ?: time() ?>">
    <?php endif; ?>
    <?php
    $jtbAnimCss = CMS_ROOT . '/plugins/jessie-theme-builder/assets/css/animations.css';
    if (file_exists($jtbAnimCss)): ?>
    <link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/animations.css?v=<?= @filemtime($jtbAnimCss) ?: time() ?>">
    <?php endif; ?>
    <style>
<?= $themeCssVariables ?>
<?= function_exists('generate_studio_css_overrides') ? generate_studio_css_overrides() : '' ?>
<?php if ($themeSettingsCss): ?>
/* JTB Theme Settings */
<?= $themeSettingsCss ?>
<?php endif; ?>
<?php if ($jtbHeadCss): ?>
/* JTB Module CSS */
<?= $jtbHeadCss ?>
<?php endif; ?>
    </style>
    <?= function_exists('theme_render_favicon') ? theme_render_favicon() : '' ?>
    <?= function_exists('theme_render_og_image') ? theme_render_og_image() : '' ?>
</head>
<body class="jtb-theme <?= esc(get_body_class() ?? '') ?><?= $isJtbPage ? ' jtb-page' : '' ?>">

<?php
// ════════════════════════════════════════════
// HEADER
// ════════════════════════════════════════════
if ($hasJtbHeader):
    echo \JessieThemeBuilder\JTB_Theme_Integration::renderHeader();
else:
    // Fallback: minimal header
?>
<header class="jtb-fallback-header">
    <div class="jtb-fallback-container">
        <a href="/" class="jtb-fallback-logo">
            <?php if ($siteLogo): ?>
                <img src="<?= esc($siteLogo) ?>" alt="<?= esc($siteName) ?>" style="height:40px">
            <?php else: ?>
                <span><?= esc($siteName) ?></span>
            <?php endif; ?>
        </a>
        <nav class="jtb-fallback-nav">
            <?php if (function_exists('render_menu')): ?>
                <?= render_menu('main', ['class' => 'jtb-fallback-menu']) ?>
            <?php endif; ?>
        </nav>
    </div>
</header>
<?php endif; ?>

<?php
// ════════════════════════════════════════════
// MAIN CONTENT
// ════════════════════════════════════════════
?>
<main id="main-content" class="jtb-main">
<?php
if ($isJtbPage):
    // Page built with JTB — render full JTB content
    echo $jtbPageContent;
elseif (!empty($content)):
    // Content from template (passed by render_with_theme)
    echo '<div class="jtb-fallback-container jtb-page-content">' . $content . '</div>';
else:
    // Direct page content
    $pageContent = $page['content'] ?? '';
    if ($pageContent):
        echo '<div class="jtb-fallback-container jtb-page-content">';
        if (strlen(strip_tags($pageContent)) !== strlen($pageContent)):
            echo $pageContent;
        else:
            echo '<p>' . nl2br(esc($pageContent)) . '</p>';
        endif;
        echo '</div>';
    endif;
endif;
?>
</main>

<?php
// ════════════════════════════════════════════
// FOOTER
// ════════════════════════════════════════════
if ($hasJtbFooter):
    echo \JessieThemeBuilder\JTB_Theme_Integration::renderFooter();
else:
    // Fallback: minimal footer
?>
<footer class="jtb-fallback-footer">
    <div class="jtb-fallback-container">
        <p>&copy; <?= date('Y') ?> <?= esc($siteName) ?>. All rights reserved.</p>
    </div>
</footer>
<?php endif; ?>

<?php
// JTB Frontend JS
$jtbFrontendJs = CMS_ROOT . '/plugins/jessie-theme-builder/assets/js/frontend.js';
if (file_exists($jtbFrontendJs)): ?>
<script src="/plugins/jessie-theme-builder/assets/js/frontend.js?v=<?= @filemtime($jtbFrontendJs) ?: time() ?>"></script>
<?php endif; ?>
<?php
// Theme JS
$themeJs = __DIR__ . '/assets/js/theme.js';
if (file_exists($themeJs)): ?>
<script src="<?= $themePath ?>/assets/js/theme.js?v=<?= @filemtime($themeJs) ?: time() ?>"></script>
<?php endif; ?>

</body>
</html>
