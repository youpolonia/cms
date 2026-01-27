<?php
require_once __DIR__.'/notificationservice.php';
/**
 * MemoryProfiler Service
 * Implements circular buffer pattern for memory monitoring
 */
class MemoryProfiler {
    private static $instance;
    private $buffer = [];
    private $bufferSize = 60;
    private $currentIndex = 0;
    private $notificationService;

    private function __construct() {
        // Initialize buffer with empty entries
        $this->buffer = array_fill(0, $this->bufferSize, null);
        $this->notificationService = NotificationService::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function recordMemoryUsage() {
        $usage = memory_get_usage(true);
        $this->buffer[$this->currentIndex] = [
            'timestamp' => time(),
            'memory' => $usage,
            'peak' => memory_get_peak_usage(true)
        ];
        
        $this->currentIndex = ($this->currentIndex + 1) % $this->bufferSize;
        return $this;
    }

    public function getMemoryStats() {
        $stats = [];
        for ($i = 0; $i < $this->bufferSize; $i++) {
            $index = ($this->currentIndex + $i) % $this->bufferSize;
            if ($this->buffer[$index] !== null) {
                $stats[] = $this->buffer[$index];
            }
        }
        return $stats;
    }

    public function checkThreshold($threshold) {
        $current = $this->buffer[($this->currentIndex - 1) % $this->bufferSize];
        $exceeded = $current and $current['memory'] > $threshold;
        
        if ($exceeded) {
            $this->notificationService->sendAlert(
                'Memory threshold exceeded',
                sprintf('Current memory usage: %d bytes (Threshold: %d bytes)',
                    $current['memory'], $threshold)
            );
        }
        
        return $exceeded;
    }
}
