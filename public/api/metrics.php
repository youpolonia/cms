<?php
require_once __DIR__ . '/../../core/bootstrap.php';
declare(strict_types=1);

require_once __DIR__ . '/../../includes/services/metricsservice.php';

header('Content-Type: application/json');

try {
    $metrics = MetricsService::getMetrics();
    echo json_encode($metrics);
} catch (Exception $e) {
    http_response_code(500);
    $errorResponse = [
        'error' => 'Failed to retrieve metrics',
        'message' => $e->getMessage()
    ];
    echo json_encode($errorResponse);
}
