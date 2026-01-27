<?php
require_once __DIR__ . '/../services/dashboardvisualizer.php';

header('Content-Type: application/json');

try {
    $tenantId = $_GET['tenant_id'] ?? null;
    if (!$tenantId) {
        throw new InvalidArgumentException('Missing tenant_id parameter');
    }

    $days = min(30, max(1, intval($_GET['days'] ?? 7)));
    $visualizer = DashboardVisualizer::getInstance();
    $summary = $visualizer->getEventSummary($tenantId, $days);

    echo json_encode([
        'status' => 'success',
        'data' => $summary
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
