<?php
/**
 * Theme Builder Layout Library API
 * Returns header, footer, and body layouts for Theme Builder
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Load Layout Library class if not loaded
$layoutLibraryFile = dirname(__DIR__) . '/includes/class-jtb-layout-library.php';
if (file_exists($layoutLibraryFile)) {
    require_once $layoutLibraryFile;
}

// Get template type from query
$type = $_GET['type'] ?? '';

$layouts = [];

try {
    // Get layouts based on type
    switch ($type) {
        case 'header':
            $layouts = JTB_Layout_Library::getHeaderLayouts();
            break;

        case 'footer':
            $layouts = JTB_Layout_Library::getFooterLayouts();
            break;

        case 'body':
            $layouts = JTB_Layout_Library::getBodyLayouts();
            break;

        default:
            // Return all theme builder layouts
            $themeLayouts = JTB_Layout_Library::getThemeBuilderLayouts();
            $layouts = array_merge(
                $themeLayouts['headers'] ?? [],
                $themeLayouts['footers'] ?? [],
                $themeLayouts['body'] ?? []
            );
            break;
    }

    // Transform layouts to match expected format
    $formattedLayouts = [];
    foreach ($layouts as $layout) {
        $formattedLayouts[] = [
            'id' => $layout['id'] ?? uniqid('layout_'),
            'name' => $layout['name'] ?? 'Unnamed Layout',
            'type' => $layout['type'] ?? $type,
            'category_slug' => $layout['category'] ?? $type,
            'thumbnail' => $layout['thumbnail'] ?? null,
            'content' => $layout['content'] ?? [],
            'is_premade' => true,
            'is_featured' => $layout['is_featured'] ?? false,
            'downloads' => $layout['downloads'] ?? 0,
        ];
    }

    echo json_encode([
        'success' => true,
        'layouts' => $formattedLayouts,
        'type' => $type,
        'total' => count($formattedLayouts)
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
