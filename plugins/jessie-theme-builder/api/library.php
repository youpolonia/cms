<?php
/**
 * JTB Library API - List templates
 * GET /api/jtb/library
 *
 * Query params:
 * - category: filter by category slug
 * - type: filter by template_type (page, section, row)
 * - premade: 1 for premade only, 0 for user only
 * - featured: 1 for featured only
 * - search: search term
 * - limit: max results
 * - offset: pagination offset
 * - order_by: name, created_at, downloads
 * - order_dir: ASC or DESC
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

header('Content-Type: application/json');

// Ensure tables exist
if (!JTB_Library::tablesExist()) {
    JTB_Library::createTables();
}

// Build filters from query params
$filters = [];

if (!empty($_GET['category'])) {
    $filters['category'] = $_GET['category'];
}

if (!empty($_GET['type'])) {
    $filters['type'] = $_GET['type'];
}

if (isset($_GET['premade'])) {
    $filters['is_premade'] = $_GET['premade'] === '1';
}

if (!empty($_GET['featured'])) {
    $filters['featured'] = true;
}

if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

if (!empty($_GET['limit'])) {
    $filters['limit'] = (int)$_GET['limit'];
}

if (!empty($_GET['offset'])) {
    $filters['offset'] = (int)$_GET['offset'];
}

if (!empty($_GET['order_by'])) {
    $filters['order_by'] = $_GET['order_by'];
}

if (!empty($_GET['order_dir'])) {
    $filters['order_dir'] = $_GET['order_dir'];
}

try {
    $templates = JTB_Library::getAll($filters);
    $total = JTB_Library::getCount($filters);

    // Remove full content from list view (too heavy)
    foreach ($templates as &$template) {
        unset($template['content']);
    }

    echo json_encode([
        'success' => true,
        'templates' => $templates,
        'total' => $total,
        'filters' => $filters
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch templates: ' . $e->getMessage()
    ]);
}
