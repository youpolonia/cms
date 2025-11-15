<?php
require_once __DIR__ . '/../../includes/coreloader.php';
require_once __DIR__ . '/../../../debug_worker_monitoring_phase5.php';

header('Content-Type: application/json');

try {
    // Log API request for PHASE5-WORKFLOW-STEP4 debugging
    DebugWorkerMonitoringPhase5::logMessage('Heartbeat history API requested at ' . date('Y-m-d H:i:s'));
    
    // Validate request
    if (!WorkerAuthController::validateApiRequest()) {
        header('HTTP/1.0 403 Forbidden');
        $errorResponse = ['error' => 'Access denied'];
        echo json_encode($errorResponse);
        
        // Log authentication error
        DebugWorkerMonitoringPhase5::logMessage('Authentication failed for heartbeat history API');
        exit;
    }

    // Get time range parameter (default 24 hours)
    $hours = isset($_GET['hours']) ? (int)$_GET['hours'] : 24;
    $hours = min($hours, 168); // Max 1 week
    
    $db = \core\Database::connection();
    
    // Get hourly heartbeat counts
    $query = "SELECT 
                DATE_FORMAT(timestamp, '%Y-%m-%d %H:00:00') as hour,
                COUNT(*) as count
              FROM worker_activity_logs
              WHERE timestamp >= DATE_SUB(NOW(), INTERVAL ? HOUR)
              GROUP BY hour
              ORDER BY hour ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$hours]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format for Chart.js
    $labels = [];
    $values = [];
    
    // Fill in all hours in range, even if no data
    $current = new DateTime("-{$hours} hours");
    $end = new DateTime();
    
    while ($current <= $end) {
        $hourKey = $current->format('Y-m-d H:00:00');
        $labels[] = $current->format('M j, H:i');
        
        // Find matching data point or use 0
        $found = array_filter($results, function($item) use ($hourKey) {
            return $item['hour'] === $hourKey;
        });
        
        $values[] = $found ? reset($found)['count'] : 0;
        $current->modify('+1 hour');
    }

    $response = [
        'labels' => $labels,
        'values' => $values
    ];
    
    // Log successful response for PHASE5-WORKFLOW-STEP4 debugging
    DebugWorkerMonitoringPhase5::logMessage('Heartbeat history API responded successfully with ' . count($labels) . ' data points');
    
    // Validate response structure
    if (empty($labels) || empty($values)) {
        DebugWorkerMonitoringPhase5::logResponseValidation(
            'heartbeat-history',
            $response,
            false,
            'Empty data arrays in response'
        );
    } else {
        DebugWorkerMonitoringPhase5::logResponseValidation(
            'heartbeat-history',
            $response,
            true
        );
    }
    
    // Add cache control headers to prevent stale data
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo json_encode($response);
} catch (Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    $errorResponse = ['error' => $e->getMessage()];
    echo json_encode($errorResponse);
    
    // Log error for PHASE5-WORKFLOW-STEP4 debugging
    DebugWorkerMonitoringPhase5::logError('Heartbeat history API error: ' . $e->getMessage(), $e);
}
