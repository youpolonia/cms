<?php
define('CMS_ROOT', dirname(__DIR__, 3));
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/auth.php';
authenticateAdmin();

// POST-only guard
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

/**
 * Widget Layout API Endpoint
 * Handles saving widget positions and visibility rules
 */

require_once __DIR__ . '/../../../core/csrf.php';
csrf_validate_or_403();

// Get and validate input data
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

if (!isset($input['widgets']) || !isset($input['visibility_rules'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

try {
    // Process widget positions
    $widgets = $input['widgets'];
    $visibilityRules = $input['visibility_rules'];
    
    // TODO: Implement actual database saving logic
    // This is a placeholder - replace with your actual database operations
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Layout saved successfully',
        'data' => [
            'widgets_updated' => count($widgets),
            'rules_updated' => count($visibilityRules)
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal Server Error',
        'message' => $e->getMessage()
    ]);
}
