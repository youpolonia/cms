<?php

namespace Includes\Performance;

/**
 * MetricsCollector - Performance metrics collection utility
 *
 * Collects and stores key performance indicators (KPIs) for the CMS
 */
class MetricsCollector {
    private static array $metrics = [
        'response_times' => [],
        'query_counts' => [],
        'memory_usage' => []
    ];

    /**
     * Record response time for a route
     * 
     * @param string $route The route being measured
     * @param float $time Response time in milliseconds
     */
    public static function recordResponseTime(string $route, float $time): void {
        self::$metrics['response_times'][$route][] = $time;
    }

    /**
     * Increment query count for a query type
     * 
     * @param string $queryType Type of query (select, insert, update, delete)
     */
    public static function incrementQueryCount(string $queryType): void {
        if (!isset(self::$metrics['query_counts'][$queryType])) {
            self::$metrics['query_counts'][$queryType] = 0;
        }
        self::$metrics['query_counts'][$queryType]++;
    }

    /**
     * Record memory usage at a specific context point
     * 
     * @param string $context Description of where memory was measured
     * @param int $memoryBytes Memory usage in bytes
     */
    public static function recordMemoryUsage(string $context, int $memoryBytes): void {
        self::$metrics['memory_usage'][$context][] = $memoryBytes;
    }

    /**
     * Get all collected metrics
     * 
     * @return array Structured metrics data
     */
    public static function getMetrics(): array {
        return self::$metrics;
    }

    /**
     * Reset all metrics
     */
    public static function reset(): void {
        self::$metrics = [
            'response_times' => [],
            'query_counts' => [],
            'memory_usage' => []
        ];
    }
}
