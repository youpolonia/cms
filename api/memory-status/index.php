<?php
require_once __DIR__.'/../../services/memoryprofiler.php';
require_once __DIR__.'/../../services/heartbeatmonitor.php';

header('Content-Type: application/json');

try {
    $profiler = MemoryProfiler::getInstance();
    $heartbeat = HeartbeatMonitor::getInstance();
    
    $threshold = 1024 * 1024 * 100; // 100MB threshold
    
    $response = [
        'status' => 'ok',
        'memory' => $profiler->getMemoryStats(),
        'threshold_exceeded' => $profiler->checkThreshold($threshold),
        'heartbeat_status' => $heartbeat->getStatus()
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
