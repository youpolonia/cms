<?php
/**
 * Dashboard Integration Service - Handles tenant-aware metrics collection,
 * performance data aggregation, and alert threshold configuration
 */
class DashboardIntegrationService {
    private static $instance;
    private $tenantId;
    private $alertThresholds = [];

    private function __construct(string $tenantId) {
        $this->tenantId = $tenantId;
        $this->loadDefaultThresholds();
    }

    public static function getInstance(string $tenantId): self {
        if (!isset(self::$instance[$tenantId])) {
            self::$instance[$tenantId] = new self($tenantId);
        }
        return self::$instance[$tenantId];
    }

    private function loadDefaultThresholds(): void {
        $this->alertThresholds = [
            'cpu' => 80,    // % CPU usage
            'memory' => 90,  // % Memory usage
            'response_time' => 500, // ms
            'error_rate' => 1 // % errors
        ];
    }

    public function setThreshold(string $metric, float $value): void {
        if (isset($this->alertThresholds[$metric])) {
            $this->alertThresholds[$metric] = $value;
        }
    }

    public function getThreshold(string $metric): ?float {
        return $this->alertThresholds[$metric] ?? null;
    }

    public function collectMetrics(array $metrics): array {
        $filtered = array_filter($metrics, function($metric) {
            return $metric['tenant_id'] === $this->tenantId;
        });

        return $this->aggregateMetrics($filtered);
    }

    private function aggregateMetrics(array $metrics): array {
        $aggregated = [
            'cpu' => 0,
            'memory' => 0,
            'response_time' => 0,
            'error_rate' => 0,
            'count' => 0
        ];

        foreach ($metrics as $metric) {
            $aggregated['cpu'] += $metric['cpu'];
            $aggregated['memory'] += $metric['memory'];
            $aggregated['response_time'] += $metric['response_time'];
            $aggregated['error_rate'] += $metric['error_rate'];
            $aggregated['count']++;
        }

        if ($aggregated['count'] > 0) {
            $aggregated['cpu'] /= $aggregated['count'];
            $aggregated['memory'] /= $aggregated['count'];
            $aggregated['response_time'] /= $aggregated['count'];
            $aggregated['error_rate'] /= $aggregated['count'];
        }

        return $aggregated;
    }

    public function checkAlerts(array $metrics): array {
        $alerts = [];
        $aggregated = $this->collectMetrics($metrics);

        foreach ($this->alertThresholds as $metric => $threshold) {
            if (isset($aggregated[$metric]) && $aggregated[$metric] > $threshold) {
                $alerts[] = [
                    'metric' => $metric,
                    'value' => $aggregated[$metric],
                    'threshold' => $threshold,
                    'tenant_id' => $this->tenantId
                ];
            }
        }

        return $alerts;
    }
}
