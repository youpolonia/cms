<?php
declare(strict_types=1);

require_once __DIR__ . '/../../services/metricsservice.php';

class ResponseTimeMiddleware
{
    public static function handle(callable $next): void
    {
        $startTime = microtime(true);
        
        try {
            $next();
        } finally {
            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
            
            $requestUri = $_SERVER['REQUEST_URI'] ?? 'unknown';
            MetricsService::trackResponseTime($requestUri, $responseTime);
        }
    }
}
