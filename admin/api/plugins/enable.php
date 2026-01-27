<?php
define('CMS_ROOT', dirname(__DIR__, 3));
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/auth.php';
authenticateAdmin();

require_once __DIR__ . '/../../../includes/init.php';
require_once __DIR__ . '/../middleware/csrf.php';
header('Content-Type: application/json');

// Method guard: POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

verifyCSRFToken();
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Direct access not allowed']));
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['plugin'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Plugin name required']));
}

try {
    $pluginManager = new PluginManager();
    $result = $pluginManager->enablePlugin($data['plugin']);
    
    if ($result) {
        SecurityLog::logEvent(
            'plugin_enabled',
            $_SESSION['admin_id'],
            $_SERVER['REMOTE_ADDR'],
            'Plugin: ' . $data['plugin']
        );
        echo json_encode(['success' => true]);
    } else {
        SecurityLog::logEvent(
            'plugin_enable_failed',
            $_SESSION['admin_id'],
            $_SERVER['REMOTE_ADDR'],
            'Plugin: ' . $data['plugin']
        );
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to enable plugin']);
    }
} catch (\Throwable $e) {
    SecurityLog::logEvent(
        'plugin_enable_error',
        $_SESSION['admin_id'],
        $_SERVER['REMOTE_ADDR'],
        'Plugin: ' . $data['plugin'] . ' - Error: ' . $e->getMessage()
    );
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Internal error']);
}
