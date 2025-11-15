<?php
require_once __DIR__ . '/pluginhealthmonitor.php';

header('Content-Type: application/json');

// Basic authentication check
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="Plugin Health API"');
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

// Validate credentials (placeholder - should be replaced with real auth)
$validUser = 'admin';
$validPass = 'healthmonitor';
if ($_SERVER['PHP_AUTH_USER'] !== $validUser || $_SERVER['PHP_AUTH_PW'] !== $validPass) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid credentials']);
    exit;
}

$monitor = PluginHealthMonitor::getInstance();
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '/admin/plugins/health-api.php';

try {
    if (strpos($requestUri, $basePath . '/status') !== false) {
        // Get status for specific plugin
        $pluginName = $_GET['plugin'] ?? '';
        if (empty($pluginName)) {
            throw new Exception('Plugin name required');
        }
        echo json_encode($monitor->checkStatus($pluginName));
    } elseif (strpos($requestUri, $basePath . '/summary') !== false) {
        // Get full health summary
        echo json_encode($monitor->getHealthSummary());
    } else {
        // Default endpoint - basic info
        echo json_encode([
            'endpoints' => [
                '/status?plugin={name}' => 'Get plugin status',
                '/summary' => 'Get full health summary'
            ]
        ]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
