<?php
/**
 * JTB Library API - Get categories
 * GET /api/jtb/library-categories
 *
 * Returns list of all categories with template counts
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

header('Content-Type: application/json');

// Ensure tables exist
if (!JTB_Library::tablesExist()) {
    JTB_Library::createTables();
}

try {
    $categories = JTB_Library::getCategories();

    // Add 'All' pseudo-category at the beginning
    $totalCount = JTB_Library::getCount();

    array_unshift($categories, [
        'id' => 0,
        'name' => 'All Templates',
        'slug' => '',
        'description' => 'All available templates',
        'icon' => 'grid',
        'sort_order' => 0,
        'template_count' => $totalCount
    ]);

    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch categories: ' . $e->getMessage()
    ]);
}
