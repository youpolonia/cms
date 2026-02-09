<?php
/**
 * API: Get Layout Library
 * Returns premade page and section layouts
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Class is loaded by router

try {
    $layouts = JTB_Layout_Library::getLayouts();
    $categories = JTB_Layout_Library::getCategories();

    echo json_encode([
        'success' => true,
        'layouts' => $layouts,
        'categories' => $categories
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
