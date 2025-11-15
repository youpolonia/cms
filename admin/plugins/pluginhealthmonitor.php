<?php
/**
 * Plugin Health Monitoring System
 * Tracks plugin status, load, and activation
 */
class PluginHealthMonitor {
    private static $instance;
    private $pluginStatus = [];
    private $loadHistory = [];
    private $activationLog = [];

    private function __construct() {
        // Initialize with empty data
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Check plugin status
     * @param string $pluginName
     * @return array Status data
     */
    public function checkStatus($pluginName) {
        if (!isset($this->pluginStatus[$pluginName])) {
            $this->pluginStatus[$pluginName] = [
                'active' => false,
                'last_check' => time(),
                'errors' => []
            ];
        }
        return $this->pluginStatus[$pluginName];
    }

    /**
     * Record plugin load metrics
     * @param string $pluginName
     * @param float $memoryUsage MB
     * @param float $executionTime Seconds
     */
    public function recordLoad($pluginName, $memoryUsage, $executionTime) {
        $this->loadHistory[$pluginName][] = [
            'timestamp' => time(),
            'memory' => $memoryUsage,
            'time' => $executionTime
        ];
    }

    /**
     * Log plugin activation
     * @param string $pluginName
     * @param bool $activated
     */
    public function logActivation($pluginName, $activated) {
        $this->activationLog[$pluginName][] = [
            'timestamp' => time(),
            'activated' => $activated
        ];
    }

    /**
     * Get health summary
     * @return array
     */
    public function getHealthSummary() {
        return [
            'plugins' => array_keys($this->pluginStatus),
            'load_history' => $this->loadHistory,
            'activation_log' => $this->activationLog
        ];
    }
}
