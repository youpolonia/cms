<?php

class AlertManager {
    private static ?AlertManager $instance = null;
    private $analyticsCollector;
    private $dashboardVisualizer;
    private $alerts = [];

    private function __construct() {
        $this->analyticsCollector = AnalyticsCollector::getInstance();
        $this->dashboardVisualizer = DashboardVisualizer::getInstance();
    }

    public static function getInstance(): AlertManager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function addAlert(string $type, string $message, array $conditions): bool {
        $alertId = uniqid('alert_');
        $this->alerts[$alertId] = [
            'type' => $type,
            'message' => $message,
            'conditions' => $conditions,
            'active' => true
        ];
        return true;
    }

    public function checkAlerts(): array {
        $triggeredAlerts = [];
        foreach ($this->alerts as $alertId => $alert) {
            if ($alert['active'] && $this->evaluateConditions($alert['conditions'])) {
                $triggeredAlerts[$alertId] = $alert;
                $this->dashboardVisualizer->logAlert($alert);
            }
        }
        return $triggeredAlerts;
    }

    private function evaluateConditions(array $conditions): bool {
        // Implementation depends on AnalyticsCollector methods
        $analyticsData = $this->analyticsCollector->getLatestMetrics();
        
        foreach ($conditions as $metric => $threshold) {
            if (!isset($analyticsData[$metric]) || $analyticsData[$metric] < $threshold) {
                return false;
            }
        }
        return true;
    }

    public function getActiveAlerts(): array {
        return array_filter($this->alerts, fn($alert) => $alert['active']);
    }

    public function toggleAlert(string $alertId, bool $active): bool {
        if (isset($this->alerts[$alertId])) {
            $this->alerts[$alertId]['active'] = $active;
            return true;
        }
        return false;
    }
}
