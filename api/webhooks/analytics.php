<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/analytics/collector.php';
require_once __DIR__ . '/../../includes/security/auth/auth.php';

header('Content-Type: application/json');

// Basic authentication
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="Analytics API"');
    http_response_code(401);
    die(json_encode(['error' => 'Authentication required']));
}

$validUser = 'n8n';
$validPass = 'analytics_webhook_2025';

if ($_SERVER['PHP_AUTH_USER'] !== $validUser || 
    $_SERVER['PHP_AUTH_PW'] !== $validPass) {
    http_response_code(403);
    die(json_encode(['error' => 'Invalid credentials']));
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid JSON']));
}

// Validate required fields
if (empty($input['tenant_id']) || empty($input['data'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Missing required fields']));
}

// Track analytics
$success = AnalyticsCollector::track($input['data'], $input['tenant_id']);

if ($success) {
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to store analytics']);
}
