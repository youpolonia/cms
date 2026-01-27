<?php
require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../../auth/workerauthcontroller.php';

// Track page view analytics endpoint
header('Content-Type: application/json');

// Authentication check
if (!\Includes\Auth\WorkerAuthController::validateApiRequest()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check for mock mode
$mockMode = isset($_SERVER['HTTP_X_MOCK_MODE']) && $_SERVER['HTTP_X_MOCK_MODE'] === 'true';

// Validate tenant
if (!isset($_SERVER['HTTP_X_TENANT_ID'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Tenant ID header missing']));
}

$tenantId = filter_var($_SERVER['HTTP_X_TENANT_ID'], FILTER_SANITIZE_STRING);
if (empty($tenantId)) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid tenant ID']));
}

// Get and sanitize input
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid JSON input']));
}

$requiredFields = ['page_id', 'url', 'timestamp'];
foreach ($requiredFields as $field) {
    if (!isset($input[$field])) {
        http_response_code(400);
        die(json_encode(['error' => "Missing required field: $field"]));
    }
}

// Validate timestamp format (ISO 8601 or Unix timestamp)
if (!preg_match('/^\d{10,}$/', $input['timestamp']) &&
    !DateTime::createFromFormat(DateTime::ATOM, $input['timestamp'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid timestamp format']));
}

$data = [
    'tenant_id' => $tenantId,
    'page_id' => filter_var($input['page_id'], FILTER_SANITIZE_STRING),
    'url' => filter_var($input['url'], FILTER_SANITIZE_URL),
    'timestamp' => is_numeric($input['timestamp']) ? (int)$input['timestamp'] : $input['timestamp'],
    'user_agent' => isset($input['user_agent']) ? filter_var($input['user_agent'], FILTER_SANITIZE_STRING) : null,
    'ip_address' => $_SERVER['REMOTE_ADDR'],
    'referrer' => isset($input['referrer']) ? filter_var($input['referrer'], FILTER_SANITIZE_URL) : null,
    'session_id' => isset($_SERVER['HTTP_X_SESSION_ID']) ? filter_var($_SERVER['HTTP_X_SESSION_ID'], FILTER_SANITIZE_STRING) : null,
    'user_id' => isset($_SERVER['HTTP_X_USER_ID']) ? filter_var($_SERVER['HTTP_X_USER_ID'], FILTER_SANITIZE_STRING) : null
];

if ($mockMode) {
    // In mock mode, just return the data that would be stored
    http_response_code(200);
    echo json_encode(['success' => true, 'mock' => true, 'data' => $data]);
    exit;
}

try {
    // Store analytics data (file-based for FTP deployable)
    $storageDir = __DIR__ . '/../../../../analytics/data/' . $tenantId;
    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0755, true);
    }

    $filename = $storageDir . '/views_' . date('Y-m-d') . '.log';
    file_put_contents($filename, json_encode($data) . PHP_EOL, FILE_APPEND);

    http_response_code(200);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['error' => 'Database error']);
}
