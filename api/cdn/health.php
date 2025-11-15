<?php
declare(strict_types=1);

require_once __DIR__.'/../../includes/cdn/cachepurger.php';
require_once __DIR__.'/../../includes/security.php';

header('Content-Type: application/json');

class CDNHealthMonitor {
    private const RATE_LIMIT = 60; // Requests per minute
    
    public static function handleRequest(): void {
        if (!SecurityHeadersMiddleware::validateApiKey()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        if (self::rateLimitExceeded()) {
            http_response_code(429);
            echo json_encode(['error' => 'Rate limit exceeded']);
            return;
        }

        $status = [
            'nodes' => EdgeCacheService::getNodeStatus(),
            'performance' => [
                'hit_rate' => EdgeCacheService::getHitRate(),
                'latency' => EdgeCacheService::getAverageLatency(),
                'errors' => EdgeCacheService::getErrorRates()
            ],
            'timestamp' => time()
        ];

        echo json_encode($status);
    }

    private static function rateLimitExceeded(): bool {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $minute = (int)(time() / 60);
        $key = "cdn_health_{$ip}_{$minute}";
        
        $count = CacheManager::get($key) ?? 0;
        CacheManager::set($key, $count + 1, 60);
        
        return $count >= self::RATE_LIMIT;
    }
}

CDNHealthMonitor::handleRequest();
