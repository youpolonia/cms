<?php
declare(strict_types=1);

class PerformanceService {
    private static ?PerformanceService $instance = null;
    private array $metrics = [];
    private float $startTime;

    private function __construct() {
        $this->startTime = microtime(true);
    }

    public static function getInstance(): PerformanceService {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function startMeasurement(string $name): void {
        $this->metrics[$name] = [
            'start' => microtime(true),
            'memory' => memory_get_usage()
        ];
    }

    public function endMeasurement(string $name): array {
        if (!isset($this->metrics[$name])) {
            return [];
        }

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $result = [
            'time' => $endTime - $this->metrics[$name]['start'],
            'memory' => $endMemory - $this->metrics[$name]['memory'],
            'peak_memory' => memory_get_peak_usage()
        ];

        unset($this->metrics[$name]);
        return $result;
    }

    public function getSystemMetrics(): array {
        return [
            'total_time' => microtime(true) - $this->startTime,
            'current_memory' => memory_get_usage(),
            'peak_memory' => memory_get_peak_usage(),
            'included_files' => count(get_included_files())
        ];
    }

    public function logSlowOperation(string $name, float $threshold = 1.0): void {
        $metrics = $this->endMeasurement($name);
        if ($metrics['time'] > $threshold) {
            // TODO: Implement actual logging
        }
    }
}
