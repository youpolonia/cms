<?php
/**
 * JTB Layout Gallery API - List layouts
 * GET /api/jtb/layouts
 *
 * Query params:
 * - category: rows, sections, pages
 * - layout_type: row, section, page
 * - premade: 1 for premade only
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

header('Content-Type: application/json');

// Ensure class is loaded
require_once dirname(__DIR__) . '/includes/class-jtb-layout-gallery.php';

// Ensure tables exist
if (!JTB_Layout_Gallery::tablesExist()) {
    JTB_Layout_Gallery::createTables();
}

$filters = [];

if (!empty($_GET['category'])) {
    $filters['category'] = $_GET['category'];
}

if (!empty($_GET['layout_type'])) {
    $filters['layout_type'] = $_GET['layout_type'];
}

if (isset($_GET['premade'])) {
    $filters['is_premade'] = $_GET['premade'] === '1';
}

try {
    $layouts = JTB_Layout_Gallery::getAll($filters);
    $categories = JTB_Layout_Gallery::getCategories();

    echo json_encode([
        'success' => true,
        'layouts' => $layouts,
        'categories' => $categories
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch layouts: ' . $e->getMessage()
    ]);
}
