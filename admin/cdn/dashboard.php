<?php
declare(strict_types=1);

require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/security.php';
require_once __DIR__.'/../../includes/core/mcpalert.php';

class CDNDashboard {
    private const CACHE_TTL = 300;
    
    public static function render(): void {
        if (!AuthService::hasPermission('cdn_admin')) {
            SecurityHeadersMiddleware::denyAccess();
        }

        require_once __DIR__.'/../../templates/layout.php';
        
        $metrics = self::getPerformanceMetrics();
        $health = self::getHealthStatus();
        
        start_content('CDN Dashboard');

        echo '<div class="metrics-container">
<div class="metric-card">
<h3>Hit Rate</h3>
<p>' . htmlspecialchars($metrics['hit_rate']) . '%</p>
</div>
<div class="metric-card">
<h3>Bandwidth</h3>
<p>' . htmlspecialchars($metrics['bandwidth']) . ' MB/s</p>
</div>
<div class="metric-card">
<h3>Latency</h3>
<p>' . htmlspecialchars($metrics['latency']) . ' ms</p>
</div>
<div class="metric-card">
<h3>Errors</h3>
<p>' . htmlspecialchars($metrics['errors']) . '%</p>
</div>
</div>';
        echo '<div class="health-container">
<h2>System Health</h2>
<pre>' . htmlspecialchars(json_encode($health, JSON_PRETTY_PRINT)) . '</pre>
</div>';

        end_content();
    }

    private static function getPerformanceMetrics(): array {
        $cacheKey = 'cdn_metrics_'.date('YmdH');
        $cached = CacheManager::get($cacheKey);
        
        if ($cached === null) {
            $metrics = [
                'hit_rate' => EdgeCacheService::getHitRate(),
                'bandwidth' => EdgeCacheService::getBandwidthUsage(),
                'latency' => EdgeCacheService::getAverageLatency(),
                'errors' => EdgeCacheService::getErrorRates()
            ];
            CacheManager::set($cacheKey, $metrics, self::CACHE_TTL);
            return $metrics;
        }
        
        return $cached;
    }

    private static function getHealthStatus(): array {
        return [
            'edge_nodes' => FailoverController::getNodeStatus(),
            'geo_routing' => GeoDNSService::getRoutingHealth(),
            'purge_queue' => EdgeCacheService::getPurgeQueueSize()
        ];
    }
}

CDNDashboard::render();
