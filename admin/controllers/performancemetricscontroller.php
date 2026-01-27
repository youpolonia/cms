<?php
declare(strict_types=1);
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

namespace Admin\Controllers;
require_once __DIR__ . '/../../config.php';

use Includes\Performance\MetricsCollector;

class PerformanceMetricsController {
    public function index() {
        $metrics = MetricsCollector::getMetrics();
        
        // Format metrics for display
        $data = [
            'responseTimes' => $this->formatResponseTimes($metrics['response_times']),
            'queryCounts' => $metrics['query_counts'],
            'memoryUsage' => $this->formatMemoryUsage($metrics['memory_usage'])
        ];

        require_once __DIR__ . '/../views/performance_metrics.php';
    }

    private function formatResponseTimes(array $times): array {
        $formatted = [];
        foreach ($times as $route => $measurements) {
            $formatted[$route] = [
                'count' => count($measurements),
                'avg' => array_sum($measurements) / count($measurements),
                'min' => min($measurements),
                'max' => max($measurements)
            ];
        }
        return $formatted;
    }

    private function formatMemoryUsage(array $usage): array {
        $formatted = [];
        foreach ($usage as $context => $measurements) {
            $formatted[$context] = [
                'count' => count($measurements),
                'avg' => $this->formatBytes(array_sum($measurements) / count($measurements)),
                'min' => $this->formatBytes(min($measurements)),
                'max' => $this->formatBytes(max($measurements))
            ];
        }
        return $formatted;
    }

    private function formatBytes(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB'];
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
