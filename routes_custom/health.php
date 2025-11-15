<?php

require_once __DIR__.'/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/redishelper.php';

$router->get('/health/redis', function() {
    try {
        $redis = RedisHelper::getConnection();
        $redis->ping();
        $info = $redis->info();
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'healthy',
            'details' => [
                'redis_version' => $info['redis_version'],
                'uptime_in_seconds' => $info['uptime_in_seconds'],
                'used_memory_human' => $info['used_memory_human'],
                'connected_clients' => $info['connected_clients']
            ]
        ]);
    } catch (Exception $e) {
        header('Content-Type: application/json', true, 500);
        echo json_encode([
            'status' => 'unhealthy',
            'error' => $e->getMessage()
        ]);
    }
});
