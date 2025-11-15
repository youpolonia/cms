<?php
/**
 * Visitor-triggered Metrics Aggregation Endpoint
 */

require_once __DIR__ . '/../core/dailyaggregator.php';

// Basic authentication
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="Metrics Aggregation"');
    header('HTTP/1.0 401 Unauthorized');
    exit;
}

$validUser = 'aggregator';
$validPass = 'secure_password_123';

if ($_SERVER['PHP_AUTH_USER'] !== $validUser || 
    $_SERVER['PHP_AUTH_PW'] !== $validPass) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

// Run aggregation
header('Content-Type: application/json');
try {
    $success = DailyAggregator::runDailyAggregation();
    echo json_encode([
        'status' => $success ? 'success' : 'error',
        'message' => $success ? 'Metrics aggregated successfully' : 'Aggregation failed'
    ]);
} catch (Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
