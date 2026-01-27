<?php
declare(strict_types=1);

namespace Services;

class PerformanceMonitor {
    private array $metrics = [];
    private array $thresholds = [];
    private array $alerts = [];

    public function track(string $metric, float $value): void {
        $this->metrics[$metric][] = [
            'value' => $value,
            'timestamp' => time()
        ];
    }

    public function setThreshold(string $metric, float $threshold, string $condition = '>'): void {
        $this->thresholds[$metric] = [
            'value' => $threshold,
            'condition' => $condition
        ];
    }

    public function checkThresholds(): array {
        $triggered = [];
        foreach ($this->thresholds as $metric => $threshold) {
            if (!empty($this->metrics[$metric])) {
                $latest = end($this->metrics[$metric]);
                $value = $latest['value'];
                $condition = $threshold['condition'];
                $thresholdValue = $threshold['value'];

                if (($condition === '>' && $value > $thresholdValue) ||
                    ($condition === '<' && $value < $thresholdValue) ||
                    ($condition === '=' && $value == $thresholdValue)) {
                    $triggered[$metric] = [
                        'value' => $value,
                        'threshold' => $thresholdValue
                    ];
                    $this->alerts[] = [
                        'metric' => $metric,
                        'value' => $value,
                        'threshold' => $thresholdValue,
                        'timestamp' => time()
                    ];
                }
            }
        }
        return $triggered;
    }

    public function getMetrics(): array {
        return $this->metrics;
    }

    public function getAlerts(): array {
        return $this->alerts;
    }
}
