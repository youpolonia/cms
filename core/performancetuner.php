<?php
/**
 * Performance Tuner for CMS Final Optimization Phase
 * Extends PerformanceMonitor with advanced analysis capabilities
 * Supports tenant-specific performance metrics
 */
class PerformanceTuner extends PerformanceMonitor {
    private static $queryStats = []; // Format: [tenant_id][query_fingerprint] => stats
    private static $memoryStats = []; // Format: [tenant_id][context] => stats
    private static $recommendations = [];

    /**
     * Track query execution details
     * @param string $query The SQL query
     * @param float $duration Execution time in seconds
     * @param int $rowsAffected Number of rows affected/returned
     */
    public static function trackQuery(string $query, float $duration, int $rowsAffected, ?string $tenantId = null): void {
        $fingerprint = self::normalizeQuery($query);
        $tenantId = $tenantId ?? TenantIsolation::getCurrentTenant();
        
        if (!isset(self::$queryStats[$tenantId][$fingerprint])) {
            self::$queryStats[$tenantId][$fingerprint] = [
                'count' => 0,
                'total_time' => 0,
                'max_time' => 0,
                'rows_affected' => 0,
                'samples' => []
            ];
        }

        self::$queryStats[$fingerprint]['count']++;
        self::$queryStats[$fingerprint]['total_time'] += $duration;
        self::$queryStats[$fingerprint]['max_time'] = max(
            self::$queryStats[$fingerprint]['max_time'], 
            $duration
        );
        self::$queryStats[$fingerprint]['rows_affected'] += $rowsAffected;
        self::$queryStats[$fingerprint]['samples'][] = $duration;
    }

    /**
     * Track memory usage
     * @param string $context Usage context (e.g., 'content_rendering')
     * @param int $bytes Memory used in bytes
     */
    public static function trackMemory(string $context, int $bytes, ?string $tenantId = null): void {
        $tenantId = $tenantId ?? TenantIsolation::getCurrentTenant();
        if (!isset(self::$memoryStats[$tenantId][$context])) {
            self::$memoryStats[$tenantId][$context] = [
                'count' => 0,
                'total_bytes' => 0,
                'max_bytes' => 0,
                'samples' => []
            ];
        }

        self::$memoryStats[$context]['count']++;
        self::$memoryStats[$context]['total_bytes'] += $bytes;
        self::$memoryStats[$context]['max_bytes'] = max(
            self::$memoryStats[$context]['max_bytes'],
            $bytes
        );
        self::$memoryStats[$context]['samples'][] = $bytes;
    }

    /**
     * Generate performance recommendations
     */
    public static function analyze(?string $tenantId = null): array {
        self::$recommendations = [];

        // Filter stats by tenant if specified
        $queryStats = $tenantId
            ? (self::$queryStats[$tenantId] ?? [])
            : array_merge(...array_values(self::$queryStats));
            
        $memoryStats = $tenantId
            ? (self::$memoryStats[$tenantId] ?? [])
            : array_merge(...array_values(self::$memoryStats));

        // Query optimization suggestions
        foreach ($queryStats as $query => $stats) {
            $avgTime = $stats['total_time'] / $stats['count'];
            if ($avgTime > 0.1) { // Threshold for slow queries
                self::$recommendations[] = [
                    'type' => 'query_optimization',
                    'tenant' => $tenantId ?? 'all',
                    'query' => $query,
                    'avg_time' => $avgTime,
                    'call_count' => $stats['count'],
                    'suggestion' => 'Consider adding indexes or optimizing query structure'
                ];
            }
        }

        // Memory usage suggestions
        foreach ($memoryStats as $context => $stats) {
            $avgBytes = $stats['total_bytes'] / $stats['count'];
            if ($avgBytes > 1024 * 1024) { // 1MB threshold
                self::$recommendations[] = [
                    'type' => 'memory_optimization',
                    'tenant' => $tenantId ?? 'all',
                    'context' => $context,
                    'avg_memory' => self::formatBytes($avgBytes),
                    'max_memory' => self::formatBytes($stats['max_bytes']),
                    'suggestion' => 'Consider implementing memory caching or reducing data loads'
                ];
            }
        }

        return self::$recommendations;
    }

    private static function normalizeQuery(string $query): string {
        // Basic normalization - remove values and whitespace
        $query = preg_replace('/\s+/', ' ', $query);
        return preg_replace('/=\s*\S+/', '=?', $query);
    }

    private static function formatBytes(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB'];
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
    }
}
