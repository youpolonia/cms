<?php
require_once __DIR__ . '/../../../includes/init.php';
require_once __DIR__ . '/../middleware/csrf.php';
header('Content-Type: application/json');
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
    $result = $pluginManager->disablePlugin($data['plugin']);
    
    if ($result) {
        SecurityLog::logEvent(
            'plugin_disabled',
            $_SESSION['admin_id'],
            $_SERVER['REMOTE_ADDR'],
            'Plugin: ' . $data['plugin']
        );
        echo json_encode(['success' => true]);
    } else {
        SecurityLog::logEvent(
            'plugin_disable_failed',
            $_SESSION['admin_id'],
            $_SERVER['REMOTE_ADDR'],
            'Plugin: ' . $data['plugin']
        );
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to disable plugin']);
    }
} catch (\Throwable $e) {
    SecurityLog::logEvent(
        'plugin_disable_error',
        $_SESSION['admin_id'],
        $_SERVER['REMOTE_ADDR'],
        'Plugin: ' . $data['plugin'] . ' - Error: ' . $e->getMessage()
    );
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Internal error']);
}
