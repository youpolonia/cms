<?php
require_once __DIR__ . '/../../config.php';

// DEV_MODE guard
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

$migrations_log = __DIR__ . '/../../migrations.log';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Set JSON header
header('Content-Type: application/json; charset=UTF-8');

try {
    if (!file_exists($migrations_log)) {
        echo json_encode([
            'success' => true,
            'logs' => [],
            'message' => 'No log file found'
        ]);
        exit;
    }

    $logs = file($migrations_log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($logs === false) {
        throw new Exception('Could not read log file');
    }

    // Filter logs for UserSessionAuditorTask entries
    $filtered_logs = array_filter($logs, function($line) use ($filter) {
        if (!empty($filter)) {
            return stripos($line, $filter) !== false && stripos($line, 'UserSessionAuditorTask') !== false;
        }
        return stripos($line, 'UserSessionAuditorTask') !== false;
    });

    // Reverse to show most recent first
    $filtered_logs = array_reverse($filtered_logs);

    echo json_encode([
        'success' => true,
        'logs' => array_values($filtered_logs),
        'total_count' => count($filtered_logs)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error reading logs: ' . $e->getMessage()
    ]);
}
