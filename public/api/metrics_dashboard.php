<?php
require_once __DIR__ . '/../../services/metricsservice.php';
require_once __DIR__ . '/../../includes/core/tenantrepository.php';

header('Content-Type: application/json');

try {
    // Authenticate request
    $tenantId = TenantRepository::validateRequest();
    $metricsService = new MetricsService(TenantRepository::getPDO($tenantId));
    
    $action = $_GET['action'] ?? '';
    $response = ['status' => 'error', 'message' => 'Invalid action'];
    
    switch ($action) {
        case 'get_history':
            $metricName = $_GET['metric'] ?? '';
            if (empty($metricName)) {
                throw new InvalidArgumentException('Metric name required');
            }
            
            $days = min(90, max(1, (int)($_GET['days'] ?? 30)));
            $data = $metricsService->getMetricsHistory($tenantId, $metricName, $days);
            
            $response = [
                'status' => 'success',
                'data' => $data
            ];
            break;
            
        case 'cleanup':
            $success = $metricsService->cleanupOldMetrics();
            $response = [
                'status' => $success ? 'success' : 'error',
                'message' => $success ? 'Cleanup completed' : 'Cleanup failed'
            ];
            break;
            
        case 'current':
            // Implement current metrics endpoint if needed
            $response = [
                'status' => 'success',
                'data' => [] // Placeholder for current metrics
            ];
            break;
    }
    
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
