<?php
/**
 * TB 4.0 Admin API Entry Point
 *
 * Handles all AJAX requests for the Visual Builder from admin panel.
 * Authenticates admin user and delegates to TB4\Api class.
 *
 * @package Admin\API
 * @version 4.0
 */

define('CMS_ROOT', dirname(__DIR__, 2));

// Load configuration
require_once CMS_ROOT . '/config.php';

// Load database
require_once CMS_ROOT . '/core/database.php';

// Session and authentication
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

// CSRF utilities
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

// Verify admin authentication
if (empty($_SESSION['admin_id']) && empty($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized - admin login required'
    ]);
    exit;
}

// Load and initialize TB4 API
require_once CMS_ROOT . '/core/tb4/api.php';

try {
    $api = new \Core\TB4\Api();
    $api->handle_request();
} catch (\Throwable $e) {
    error_log('[TB4 API] Unhandled error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);
    exit;
}
