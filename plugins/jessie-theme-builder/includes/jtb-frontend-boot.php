<?php
/**
 * JTB Frontend Bootstrap
 * Lightweight loader for rendering JTB templates on the frontend.
 * Usage: require_once this file, then use JTB_Theme_Integration::renderHeader()/renderFooter()
 */

if (defined('JTB_FRONTEND_LOADED')) return;
define('JTB_FRONTEND_LOADED', true);

// __DIR__ = .../plugins/jessie-theme-builder/includes
$jtbPath = dirname(__DIR__); // .../plugins/jessie-theme-builder
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 3)); // .../cms
}

$includesPath = $jtbPath . '/includes';

// Core classes
require_once $includesPath . '/class-jtb-element.php';
require_once $includesPath . '/class-jtb-registry.php';
require_once $includesPath . '/class-jtb-fields.php';
require_once $includesPath . '/class-jtb-fonts.php';
require_once $includesPath . '/class-jtb-default-styles.php';
require_once $includesPath . '/class-jtb-renderer.php';
require_once $includesPath . '/class-jtb-css-output.php';
require_once $includesPath . '/class-jtb-style-system.php';
require_once $includesPath . '/class-jtb-settings.php';
require_once $includesPath . '/class-jtb-icons.php';
require_once $includesPath . '/class-jtb-global-settings.php';
require_once $includesPath . '/class-jtb-dynamic-context.php';
require_once $includesPath . '/class-jtb-css-generator.php';
require_once $includesPath . '/class-jtb-seo.php';
require_once $includesPath . '/class-jtb-theme-settings.php';
require_once $includesPath . '/class-jtb-templates.php';
require_once $includesPath . '/class-jtb-template-conditions.php';
require_once $includesPath . '/class-jtb-template-matcher.php';
require_once $includesPath . '/class-jtb-theme-integration.php';

// Initialize registry and load modules
\JessieThemeBuilder\JTB_Registry::init();
\JessieThemeBuilder\JTB_Fields::init();

// Load all modules
foreach (['structure', 'content', 'interactive', 'media', 'forms', 'blog', 'fullwidth', 'theme'] as $cat) {
    $catDir = $jtbPath . '/modules/' . $cat;
    if (is_dir($catDir)) {
        foreach (glob($catDir . '/*.php') as $moduleFile) {
            require_once $moduleFile;
        }
    }
}

// Register JTB as a ContentRenderer (if available)
if (class_exists("ContentRenderer")) {
    ContentRenderer::register("jtb",
        function(string $content): bool {
            // Detect JTB content: either JSON structure or jtb- CSS classes
            if (str_contains($content, "jtb-section") || str_contains($content, "jtb-module")) {
                return true;
            }
            // Try JSON decode — JTB stores content as JSON
            $decoded = @json_decode($content, true);
            return is_array($decoded) && (isset($decoded["content"]) || isset($decoded["sections"]));
        },
        function(string $content): string {
            // Try JSON decode first (JTB native format)
            $decoded = @json_decode($content, true);
            if (is_array($decoded)) {
                return \JessieThemeBuilder\JTB_Renderer::renderClean($decoded);
            }
            // Fallback: strip JTB wrapper markup from HTML
            $content = preg_replace("/<[^>]*class=\"[^\"]*jtb-(section|row|column|module|content)[^\"]*\"[^>]*>/i", "", $content);
            $content = preg_replace("/\s+class=\"[^\"]*jtb-[^\"]*\"/i", "", $content);
            $content = preg_replace("/\s+data-jtb-[a-z-]+=\"[^\"]*\"/i", "", $content);
            $content = preg_replace("/\s+id=\"jtb-[^\"]*\"/i", "", $content);
            $content = preg_replace("/<div\s*>\s*<\/div>/i", "", $content);
            return trim($content);
        },
        5 // Higher priority than legacy-tb
    );
}
