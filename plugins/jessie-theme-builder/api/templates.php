<?php
/**
 * JTB API - Get All Templates
 * GET /api/jtb/templates
 * GET /api/jtb/templates?type=header
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Authentication is already checked in router.php

// Only GET allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Get optional type filter
    $type = $_GET['type'] ?? null;

    if ($type !== null && !in_array($type, JTB_Templates::TYPES)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid template type']);
        exit;
    }

    // Get templates
    $templates = JTB_Templates::getAll($type);

    // Get counts
    $counts = JTB_Templates::getCountByType();

    echo json_encode([
        'success' => true,
        'templates' => $templates,
        'counts' => $counts,
        'types' => JTB_Templates::TYPES
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
