<?php
require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../../auth/workerauthcontroller.php';

// AI History API Endpoint
header('Content-Type: application/json');

// Authentication check
if (!\Includes\Auth\WorkerAuthController::validateApiRequest()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Security check - require tenant context
if (!isset($_SERVER['HTTP_X_TENANT_CONTEXT'])) {
    http_response_code(400);
    die(json_encode(['error' => 'X-Tenant-Context header required']));
}

$tenantId = $_SERVER['HTTP_X_TENANT_CONTEXT'];
$logFile = __DIR__ . '/../../../logs/ai-insights.log';

// Validate and sanitize inputs
$limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 20;
$typeFilter = isset($_GET['type']) ? trim($_GET['type']) : null;

try {
    if (!file_exists($logFile)) {
        http_response_code(404);
        die(json_encode(['error' => 'AI insights log not found']));
    }

    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $results = [];

    foreach (array_reverse($lines) as $line) {
        $entry = json_decode($line, true);
        
        // Filter by tenant and type
        if ($entry['tenant'] === $tenantId && 
            (!$typeFilter || $entry['type'] === $typeFilter)) {
            
            $results[] = [
                'timestamp' => $entry['timestamp'],
                'type' => $entry['type'],
                'user' => $entry['user'],
                'summary' => $entry['summary'],
                'status' => $entry['status']
            ];

            if (count($results) >= $limit) break;
        }
    }

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'count' => count($results),
        'data' => $results
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to process AI history',
        'message' => $e->getMessage()
    ]);
}
