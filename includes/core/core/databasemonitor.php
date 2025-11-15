<?php
declare(strict_types=1);

/**
 * Enhanced Database Performance Monitoring Service
 */
class DatabaseMonitor {
    const METRICS_FILE = __DIR__ . '/../../logs/db_metrics.log';
    const PERFORMANCE_LOG = __DIR__ . '/../../logs/db_performance.log';
    const CONFIG_CACHE = __DIR__ . '/../../cms_storage/db_monitor_config.cache';
    private static array $thresholds = [];
    private static ?Redis $redis = null;

    /**
     * Initialize Redis connection
     */
    public static function initRedis(string $host, int $port = 6379, string $password = ''): bool {
        try {
            self::$redis = new Redis();
            if (!self::$redis->connect($host, $port)) {
                throw new RuntimeException("Redis connection failed");
            }
            if ($password !== '' && !self::$redis->auth($password)) {
                throw new RuntimeException("Redis authentication failed");
            }
            return true;
        } catch (Exception $e) {
            error_log("Redis Error: " . $e->getMessage());
            self::$redis = null;
            return false;
        }
    }

    /**
     * Track database performance metrics with tenant context
     */
    public static function trackMetrics(array $metrics, ?string $tenantId = null): void {
        $startTime = microtime(true);
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'tenant_id' => $tenantId,
            'metrics' => $metrics
        ];

        // Log to file
        file_put_contents(
            self::METRICS_FILE,
            json_encode($logEntry) . PHP_EOL,
            FILE_APPEND
        );

        // Push to Redis if available
        if (self::$redis !== null) {
            try {
                self::$redis->rPush('db_metrics', json_encode($logEntry));
                self::$redis->expire('db_metrics', 86400); // Keep for 24h
            } catch (RedisException $e) {
                error_log("Redis Error: " . $e->getMessage());
            }
        }

        self::checkThresholds($metrics, $tenantId);
        
        // Track monitoring overhead
        $overhead = microtime(true) - $startTime;
        self::logPerformanceImpact($overhead, memory_get_usage());
    }

    /**
     * Check metrics against alert thresholds with tenant context
     */
    protected static function checkThresholds(array $metrics, ?string $tenantId = null): void {
        $alerts = [];
        
        foreach (self::$thresholds as $metric => $threshold) {
            if (isset($metrics[$metric])) {
                if ($metrics[$metric] > $threshold) {
                    $alerts[$metric] = [
                        'value' => $metrics[$metric],
                        'threshold' => $threshold,
                        'severity' => $metrics[$metric] > $threshold * 1.5 ? 'critical' : 'warning',
                        'tenant_id' => $tenantId
                    ];
                }
            }
        }

        if (!empty($alerts)) {
            self::triggerAlerts($alerts);
        }
    }

    // ... [rest of existing methods remain unchanged] ...

    /**
     * Get metrics from Redis if available, fallback to file
     */
    public static function getMetrics(int $hours = 24): array {
        if (self::$redis !== null) {
            try {
                $metrics = [];
                $redisMetrics = self::$redis->lRange('db_metrics', 0, -1);
                foreach ($redisMetrics as $metric) {
                    $data = json_decode($metric, true);
                    if ($data !== null) {
                        $metrics[] = $data;
                    }
                }
                return array_slice($metrics, -$hours * 60);
            } catch (RedisException $e) {
                error_log("Redis Error: " . $e->getMessage());
            }
        }

        // Fallback to file-based metrics
        $contents = file_exists(self::METRICS_FILE) ?
            file_get_contents(self::METRICS_FILE) : '';
        
        $lines = array_filter(explode(PHP_EOL, $contents));
        $metrics = [];
        
        foreach ($lines as $line) {
            if (!empty($line)) {
                $data = json_decode($line, true);
                if ($data !== null) {
                    $metrics[] = $data;
                }
            }
        }

        return array_slice($metrics, -$hours * 60);
    }

    /**
     * Get tenant-specific metrics
     */
    public static function getTenantMetrics(string $tenantId, int $hours = 24): array {
        $allMetrics = self::getMetrics($hours);
        return array_filter($allMetrics, fn($m) => ($m['tenant_id'] ?? null) === $tenantId);
    }
}
