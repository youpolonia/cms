<?php
/**
 * JTB API - Get Single Global Module
 * GET /api/jtb/global-module-get/{id}
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

// Get module ID
$moduleId = $_GET['post_id'] ?? $_GET['id'] ?? null;

if (!$moduleId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Module ID required']);
    exit;
}

try {
    $module = JTB_Global_Modules::get((int) $moduleId);

    if (!$module) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Module not found']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'module' => $module
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
