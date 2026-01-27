<?php
/**
 * JTB API - Get All Global Modules
 * GET /api/jtb/global-modules
 * GET /api/jtb/global-modules?type=section
 * GET /api/jtb/global-modules?search=hero
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
    $type = $_GET['type'] ?? null;
    $search = $_GET['search'] ?? null;

    if ($search) {
        // Search modules
        $modules = JTB_Global_Modules::search($search, $type);
    } else {
        // Get all modules
        $modules = JTB_Global_Modules::getAll($type);
    }

    // Get available types
    $types = JTB_Global_Modules::getTypes();

    // Get total count
    $count = JTB_Global_Modules::getCount();

    echo json_encode([
        'success' => true,
        'modules' => $modules,
        'types' => $types,
        'count' => $count
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
