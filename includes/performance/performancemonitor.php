<?php
declare(strict_types=1);

class PerformanceMonitor {
    private static array $metrics = [];
    private static float $startTime;

    public static function start(): void {
        self::$startTime = microtime(true);
    }

    public static function record(string $metric, float $value): void {
        self::$metrics[$metric][] = $value;
    }

    public static function getReport(): array {
        $report = [
            'total_time' => microtime(true) - self::$startTime,
            'metrics' => []
        ];

        foreach (self::$metrics as $metric => $values) {
            $report['metrics'][$metric] = [
                'count' => count($values),
                'avg' => array_sum($values) / count($values),
                'max' => max($values),
                'min' => min($values)
            ];
        }

        return $report;
    }

    public static function logSlowQueries(float $threshold = 0.5): array {
        return array_filter(self::$metrics['query'] ?? [], 
            fn($time) => $time > $threshold);
    }
}
