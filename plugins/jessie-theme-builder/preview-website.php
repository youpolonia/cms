<?php
/**
 * Website Preview Controller
 * GET /preview/website?header={id}&footer={id}&page={id}
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Plugin path
$pluginPath = CMS_ROOT . '/plugins/jessie-theme-builder';

// Load dependencies - SAME AS router.php
require_once $pluginPath . '/includes/class-jtb-element.php';
require_once $pluginPath . '/includes/class-jtb-registry.php';
require_once $pluginPath . '/includes/class-jtb-fields.php';
require_once $pluginPath . '/includes/class-jtb-fonts.php';
require_once $pluginPath . '/includes/class-jtb-default-styles.php';
require_once $pluginPath . '/includes/class-jtb-global-settings.php';  // Must be before renderer!
require_once $pluginPath . '/includes/class-jtb-renderer.php';
require_once $pluginPath . '/includes/class-jtb-css-output.php';
require_once $pluginPath . '/includes/class-jtb-settings.php';
require_once $pluginPath . '/includes/class-jtb-builder.php';
require_once $pluginPath . '/includes/class-jtb-icons.php';

// Theme Builder classes
require_once $pluginPath . '/includes/class-jtb-templates.php';
require_once $pluginPath . '/includes/class-jtb-template-conditions.php';
require_once $pluginPath . '/includes/class-jtb-global-modules.php';
require_once $pluginPath . '/includes/class-jtb-theme-settings.php';
require_once $pluginPath . '/includes/class-jtb-css-generator.php';
require_once $pluginPath . '/includes/class-jtb-style-system.php';
require_once $pluginPath . '/includes/class-jtb-dynamic-context.php';
require_once $pluginPath . '/includes/class-jtb-seo.php';

// Initialize registry
JTB_Registry::init();
JTB_Fields::init();

// Load modules
$modulesPath = $pluginPath . '/modules';
$moduleCategories = ['structure', 'content', 'interactive', 'media', 'forms', 'blog', 'fullwidth', 'theme'];

foreach ($moduleCategories as $category) {
    $categoryPath = $modulesPath . '/' . $category;
    if (is_dir($categoryPath)) {
        foreach (glob($categoryPath . '/*.php') as $moduleFile) {
            require_once $moduleFile;
        }
    }
}

// Get parameters
$headerId = isset($_GET['header']) ? (int)$_GET['header'] : null;
$footerId = isset($_GET['footer']) ? (int)$_GET['footer'] : null;
$pageId = isset($_GET['page']) ? (int)$_GET['page'] : null;
$bodyId = isset($_GET['body']) ? (int)$_GET['body'] : null;

// Render content
$headerHtml = '';
$headerCss = '';
$footerHtml = '';
$footerCss = '';
$pageHtml = '';
$pageCss = '';

// Render header
if ($headerId) {
    $template = JTB_Templates::get($headerId);
    if ($template && !empty($template['content'])) {
        $content = is_string($template['content']) ? json_decode($template['content'], true) : $template['content'];
        if ($content) {
            $headerHtml = JTB_Renderer::render($content);
            $headerCss = JTB_Renderer::generateCss($content);
        }
    }
}

// Render footer
if ($footerId) {
    $template = JTB_Templates::get($footerId);
    if ($template && !empty($template['content'])) {
        $content = is_string($template['content']) ? json_decode($template['content'], true) : $template['content'];
        if ($content) {
            $footerHtml = JTB_Renderer::render($content);
            $footerCss = JTB_Renderer::generateCss($content);
        }
    }
}

// Render page
if ($pageId) {
    $result = JTB_Builder::getContent($pageId);
    if ($result && !empty($result['content'])) {
        // Pass whole object to render (not just content array)
        if ($result) {
            $pageHtml = JTB_Renderer::render($result);
            $pageCss = JTB_Renderer::generateCss($result);
        }
    }
} elseif ($bodyId) {
    $template = JTB_Templates::get($bodyId);
    if ($template && !empty($template['content'])) {
        $content = is_string($template['content']) ? json_decode($template['content'], true) : $template['content'];
        if ($content) {
            $pageHtml = JTB_Renderer::render($content);
            $pageCss = JTB_Renderer::generateCss($content);
        }
    }
}

$siteName = JTB_Dynamic_Context::getSiteTitle() ?: 'Website Preview';
$pluginUrl = '/plugins/jessie-theme-builder';
$allCss = $headerCss . "\n" . $pageCss . "\n" . $footerCss;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteName) ?> - Preview</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $pluginUrl ?>/assets/css/frontend.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $pluginUrl ?>/assets/css/jtb-base-modules.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $pluginUrl ?>/assets/css/animations.css?v=<?= time() ?>">
    <style>
        .jtb-preview-bar {
            position: fixed; top: 0; left: 0; right: 0;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white; padding: 8px 16px; font-family: 'Inter', sans-serif;
            font-size: 13px; display: flex; align-items: center;
            justify-content: space-between; z-index: 10000;
        }
        .jtb-preview-bar a {
            color: white; text-decoration: none; padding: 4px 12px;
            background: rgba(255,255,255,0.2); border-radius: 4px;
        }
        body { padding-top: 40px; margin: 0; }
        <?= $allCss ?>
    </style>
</head>
<body>
    <div class="jtb-preview-bar">
        <span>🔍 Preview Mode</span>
        <a href="javascript:window.close()">✕ Close</a>
    </div>
    <?php if ($headerHtml): ?><header><?= $headerHtml ?></header><?php endif; ?>
    <main><?= $pageHtml ?: '<div style="padding:100px 20px;text-align:center;color:#666;"><h2>No page content</h2></div>' ?></main>
    <?php if ($footerHtml): ?><footer><?= $footerHtml ?></footer><?php endif; ?>
    <script src="<?= $pluginUrl ?>/assets/js/frontend.js?v=<?= time() ?>"></script>
</body>
</html>
