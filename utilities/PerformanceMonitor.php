<?php
class PerformanceMonitor {
    private static $instance;
    private $pdo;
    private $metrics = [];
    private $thresholds = [];
    private $logFile = 'logs/performance.log';

    private function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public static function getInstance(\PDO $pdo): self {
        if (!isset(self::$instance)) {
            self::$instance = new self($pdo);
        }
        return self::$instance;
    }

    public function trackMetric(string $name, float $value): void {
        $this->metrics[$name] = $value;
        $this->checkThresholds($name, $value);
    }

    public function setThreshold(string $metric, float $warning, float $critical): void {
        $this->thresholds[$metric] = [
            'warning' => $warning,
            'critical' => $critical
        ];
    }

    private function checkThresholds(string $metric, float $value): void {
        if (!isset($this->thresholds[$metric])) {
            return;
        }

        $thresholds = $this->thresholds[$metric];
        $status = 'OK';
        
        if ($value >= $thresholds['critical']) {
            $status = 'CRITICAL';
        } elseif ($value >= $thresholds['warning']) {
            $status = 'WARNING';
        }

        if ($status !== 'OK') {
            $this->logAlert($metric, $value, $status);
        }
    }

    private function logAlert(string $metric, float $value, string $status): void {
        $message = sprintf(
            "[%s] %s: %s (Value: %.2f)",
            date('Y-m-d H:i:s'),
            $status,
            $metric,
            $value
        );

        file_put_contents(
            $this->logFile,
            $message . PHP_EOL,
            FILE_APPEND
        );
    }

    public function getMetrics(): array {
        return $this->metrics;
    }
}
