<?php
/**
 * Alerting System Service
 * Framework-free implementation for CMS
 */
class AlertingSystem {
    private static $thresholds = [];
    private static $notificationHandlers = [];

    /**
     * Initialize with default thresholds
     */
    public static function init() {
        self::$thresholds = [
            'error_rate' => 5, // Max 5% error rate
            'response_time' => 2000, // Max 2000ms
            'concurrent_users' => 500 // Max 500 users
        ];
    }

    /**
     * Register notification handler
     */
    public static function registerHandler($type, callable $handler) {
        self::$notificationHandlers[$type] = $handler;
    }

    /**
     * Check metrics against thresholds
     */
    public static function checkMetrics(array $metrics) {
        $alerts = [];
        
        foreach (self::$thresholds as $metric => $threshold) {
            if (isset($metrics[$metric])) {
                if ($metrics[$metric] > $threshold) {
                    $alerts[] = self::triggerAlert($metric, $metrics[$metric]);
                }
            }
        }

        return $alerts;
    }

    /**
     * Trigger alert and dispatch notifications
     */
    private static function triggerAlert($metric, $value) {
        $alert = [
            'timestamp' => time(),
            'metric' => $metric,
            'value' => $value,
            'threshold' => self::$thresholds[$metric]
        ];

        foreach (self::$notificationHandlers as $type => $handler) {
            call_user_func($handler, $alert);
        }

        return $alert;
    }

    /**
     * Set custom threshold for a metric
     */
    public static function setThreshold($metric, $value) {
        if (array_key_exists($metric, self::$thresholds)) {
            self::$thresholds[$metric] = $value;
            return true;
        }
        return false;
    }
}

// Initialize on require_once
AlertingSystem::init();

// Register default notification handler
AlertingSystem::registerHandler('email', function($alert) {
    require_once 'services/notificationdispatcher.php';
    NotificationDispatcher::sendAlert($alert);
});
