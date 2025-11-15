<?php
/**
 * Tracks and reports system performance metrics
 */
class PerformanceMonitor {
    private $collector;
    private $startTime;
    private $memoryPeak = 0;
    private $metrics = [];

    public function __construct(EventCollector $collector) {
        $this->collector = $collector;
        $this->startTime = microtime(true);
        $this->trackInitialMetrics();
    }

    private function trackInitialMetrics(): void {
        $this->metrics = [
            'memory_usage' => memory_get_usage(),
            'memory_peak' => memory_get_peak_usage(),
            'system_load' => sys_getloadavg()[0] ?? 0,
            'db_queries' => 0
        ];
    }

    public function trackMetric(string $name, float $value, array $tags = []): void {
        $this->metrics[$name] = $value;
        $this->collector->trackPerformanceMetric($name, $value, $tags);
    }

    public function incrementCounter(string $name, int $amount = 1): void {
        $this->metrics[$name] = ($this->metrics[$name] ?? 0) + $amount;
    }

    public function recordRequestMetrics(): void {
        $duration = microtime(true) - $this->startTime;
        $this->trackMetric('request_duration', $duration);
        $this->trackMetric('memory_peak', memory_get_peak_usage());
        
        $this->collector->trackEvent('request_completed', [
            'duration' => $duration,
            'memory' => memory_get_peak_usage(),
            'metrics' => $this->metrics
        ]);
    }

    public function getMetrics(): array {
        return $this->metrics;
    }
}
