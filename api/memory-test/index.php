<?php
require_once __DIR__ . '/../../includes/services/workermonitoringservice.php';

header('Content-Type: application/json');

try {
    $monitor = WorkerMonitoringService::getInstance();
    $results = $monitor->simulateModelLoading();
    
    echo json_encode([
        'status' => 'success',
        'data' => $results,
        'timestamp' => time()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => time()
    ]);
}
