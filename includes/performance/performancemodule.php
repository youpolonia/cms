<?php
declare(strict_types=1);

require_once __DIR__ . '/cachemanager.php';
require_once __DIR__ . '/queryoptimizer.php';
require_once __DIR__ . '/assetoptimizer.php';
require_once __DIR__ . '/performancemonitor.php';

class PerformanceModule {
    public static function init(): void {
        PerformanceMonitor::start();
        register_shutdown_function([self::class, 'shutdownHandler']);
    }

    public static function shutdownHandler(): void {
        $report = PerformanceMonitor::getReport();
        self::logPerformance($report);
    }

    private static function logPerformance(array $report): void {
        $logEntry = sprintf(
            "[%s] Performance Report - Total: %.3fs | Queries: %d (Slow: %d)",
            date('Y-m-d H:i:s'),
            $report['total_time'],
            $report['metrics']['query']['count'] ?? 0,
            count(PerformanceMonitor::logSlowQueries())
        );
        
        file_put_contents(__DIR__ . '/../../logs/performance.log', $logEntry . PHP_EOL, FILE_APPEND);
    }

    public static function optimizeAsset(string $path): string {
        return AssetOptimizer::optimizeImage($path);
    }

    public static function analyzeQuery(string $query): array {
        return QueryOptimizer::analyze($query);
    }
}
