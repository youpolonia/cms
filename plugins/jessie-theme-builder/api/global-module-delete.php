<?php
/**
 * JTB API - Delete Global Module
 * POST /api/jtb/global-module-delete
 *
 * Body: { id }
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Authentication and CSRF are checked in router.php

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get request body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Validate ID
if (empty($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Module ID is required']);
    exit;
}

try {
    $moduleId = (int) $data['id'];

    // Check if module exists
    $module = JTB_Global_Modules::get($moduleId);
    if (!$module) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Module not found']);
        exit;
    }

    // Delete module
    $success = JTB_Global_Modules::delete($moduleId);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Module deleted from library'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete module']);
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
