<?php
/**
 * Article Layouts API Endpoint
 * GET /api/jtb/article-layouts
 *
 * Returns list of available article layouts
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Authentication is already checked in router.php

// Load layouts class
require_once __DIR__ . '/../includes/class-jtb-article-layouts.php';

try {
    $layouts = JTB_Article_Layouts::getLayouts();

    echo json_encode([
        'success' => true,
        'data' => [
            'layouts' => $layouts
        ]
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error loading layouts: ' . $e->getMessage()
    ]);
}
