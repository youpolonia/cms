<?php
declare(strict_types=1);

/**
 * Performance - Query Analyzer
 * Provides query performance analysis and optimization suggestions
 */
class QueryAnalyzer {
    private static string $logFile = __DIR__ . '/../../logs/query_performance.log';
    private static float $slowQueryThreshold = 1.0; // 1 second
    private static array $queryStats = [];
    private static array $tenantStats = [];

    /**
     * Analyze query performance with tenant context
     */
    public static function analyzeQuery(
        string $query,
        float $executionTime,
        array $explainResults,
        ?string $tenantId = null
    ): array {
        $analysis = [
            'execution_time' => $executionTime,
            'is_slow' => $executionTime > self::$slowQueryThreshold,
            'explain' => $explainResults,
            'tenant_id' => $tenantId,
            'suggestions' => []
        ];

        if ($analysis['is_slow']) {
            self::logSlowQuery($query, $executionTime, $tenantId);
            $analysis['suggestions'] = self::generateSuggestions($query, $explainResults);
        }

        self::trackQueryStats($query, $executionTime, $tenantId);
        return $analysis;
    }

    private static function generateSuggestions(string $query, array $explain): array {
        $suggestions = [];
        
        // Check for missing indexes
        if (str_contains($explain['Extra'] ?? '', 'Using filesort') || 
            str_contains($explain['Extra'] ?? '', 'Using temporary')) {
            $suggestions[] = 'Consider adding appropriate indexes';
        }

        // Check for full table scans
        if ($explain['type'] === 'ALL') {
            $suggestions[] = 'Full table scan detected - optimize with indexes';
        }

        return $suggestions;
    }

    private static function logSlowQuery(string $query, float $executionTime, ?string $tenantId = null): void {
        $logEntry = sprintf(
            "[%s] [Tenant: %s] Slow query (%.3fs): %s\n",
            date('Y-m-d H:i:s'),
            $tenantId ?? 'global',
            $executionTime,
            substr($query, 0, 500)
        );
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND);
    }

    private static function trackQueryStats(string $query, float $executionTime, ?string $tenantId = null): void {
        $queryHash = md5($query);
        
        // Track global query stats
        if (!isset(self::$queryStats[$queryHash])) {
            self::$queryStats[$queryHash] = [
                'query' => $query,
                'count' => 0,
                'total_time' => 0,
                'max_time' => 0
            ];
        }

        self::$queryStats[$queryHash]['count']++;
        self::$queryStats[$queryHash]['total_time'] += $executionTime;
        if ($executionTime > self::$queryStats[$queryHash]['max_time']) {
            self::$queryStats[$queryHash]['max_time'] = $executionTime;
        }

        // Track tenant-specific stats
        if ($tenantId !== null) {
            if (!isset(self::$tenantStats[$tenantId][$queryHash])) {
                self::$tenantStats[$tenantId][$queryHash] = [
                    'count' => 0,
                    'total_time' => 0,
                    'max_time' => 0
                ];
            }

            self::$tenantStats[$tenantId][$queryHash]['count']++;
            self::$tenantStats[$tenantId][$queryHash]['total_time'] += $executionTime;
            if ($executionTime > self::$tenantStats[$tenantId][$queryHash]['max_time']) {
                self::$tenantStats[$tenantId][$queryHash]['max_time'] = $executionTime;
            }
        }
    }

    /**
     * Get query performance statistics
     */
    public static function getQueryStats(?string $tenantId = null): array {
        if ($tenantId !== null) {
            return isset(self::$tenantStats[$tenantId]) ? 
                array_map(function($stats) {
                    $stats['avg_time'] = $stats['total_time'] / $stats['count'];
                    return $stats;
                }, self::$tenantStats[$tenantId]) : [];
        }

        return array_map(function($stats) {
            $stats['avg_time'] = $stats['total_time'] / $stats['count'];
            return $stats;
        }, self::$queryStats);
    }
}
