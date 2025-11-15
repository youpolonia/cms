<?php

class WorkerMonitoringService {
    private static $instance = null;
    
    private function __construct() {
        // Private constructor to prevent direct instantiation
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function simulateModelLoading() {
        $scenarios = [
            'small_model' => $this->loadSmallModel(),
            'medium_model' => $this->loadMediumModel(),
            'large_model' => $this->loadLargeModel()
        ];
        
        return $scenarios;
    }

    private function loadSmallModel() {
        $startMemory = memory_get_usage();
        $dummyData = str_repeat('x', 1024 * 1024); // 1MB
        return $this->getMemoryStats($startMemory);
    }

    private function loadMediumModel() {
        $startMemory = memory_get_usage();
        $dummyData = str_repeat('x', 10 * 1024 * 1024); // 10MB
        return $this->getMemoryStats($startMemory);
    }

    private function loadLargeModel() {
        $startMemory = memory_get_usage();
        $dummyData = str_repeat('x', 100 * 1024 * 1024); // 100MB
        return $this->getMemoryStats($startMemory);
    }

    private function getMemoryStats($startMemory) {
        $currentMemory = memory_get_usage();
        $peakMemory = memory_get_peak_usage();
        
        return [
            'memory_used' => $currentMemory - $startMemory,
            'peak_memory' => $peakMemory,
            'memory_limit' => ini_get('memory_limit')
        ];
    }
}
