<?php
declare(strict_types=1);
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}

require_once __DIR__.'/../core/bootstrap.php';

header('Content-Type: application/json');

try {
    $action = $_GET['action'] ?? '';
    $tenantId = $_GET['tenant_id'] ?? '';
    $level = $_GET['level'] ?? 'hourly';
    
    switch ($action) {
        case 'process_batch':
            $result = AnalyticsBatchProcessor::processCustomBatch(
                [$tenantId], 
                $level,
                (int)($_GET['limit'] ?? 100)
            );
            break;
            
        case 'get_aggregated':
            $result = AggregatedDataStore::getForTenant($tenantId, $level);
            break;
            
        default:
            throw new InvalidArgumentException("Invalid test action");
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $result
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
