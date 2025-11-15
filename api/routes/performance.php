<?php
require_once __DIR__.'/../../includes/database/databaseconnection.php';
require_once __DIR__ . '/../middleware/authmiddleware.php';
require_once __DIR__ . '/../middleware/csrfmiddleware.php';

header('Content-Type: application/json');
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../core/session_boot.php';

// Start session if not already started
cms_session_start('public');

// Get tenant ID from request headers
$tenantId = $_SERVER['HTTP_X_TENANT_ID'] ?? '';

if (empty($tenantId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tenant ID required']);
    exit;
}

try {
    // Check user role (admin or analytics_manager)
    $auth = new AuthMiddleware(['admin', 'analytics_manager']);
    $auth->authenticate();

    $db = \core\Database::connection();

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $metrics = $db->getPerformanceMetrics();
            echo json_encode([
                'status' => 'success',
                'data' => $metrics
            ]);
            break;
            
        case 'DELETE':
            $csrf = new CsrfMiddleware();
            $csrf->verifyToken();
            
            $db->flushPerformanceMetrics();
            echo json_encode([
                'status' => 'success',
                'message' => 'Performance metrics cleared'
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    error_log(date('[Y-m-d H:i:s]') . " Performance metrics error: " . 
              $e->getMessage() . "\n", 3, __DIR__.'/../../error_log.txt');
    
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}
