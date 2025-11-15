<?php
/**
 * Heartbeat monitoring system with configurable alerts and escalation
 */

class HeartbeatMonitor {
    private $db;
    private $notificationService;

    public function __construct($db) {
        $this->db = $db;
        $this->notificationService = new NotificationService($db);
    }

    public function checkWorkerHeartbeat($workerId) {
        // Get last heartbeat from worker_activity_logs
        $lastHeartbeat = $this->getLastHeartbeat($workerId);
        if (!$lastHeartbeat) {
            $this->handleFailure($workerId, "No heartbeat recorded");
            return false;
        }

        // Check if heartbeat is stale
        $threshold = $this->getFailureThreshold();
        $timeDiff = time() - strtotime($lastHeartbeat['created_at']);
        
        if ($timeDiff > $threshold) {
            $this->handleFailure($workerId, "Heartbeat stale by " . ($timeDiff - $threshold) . " seconds");
            return false;
        }

        // Heartbeat is healthy
        $this->resetFailureCount($workerId);
        return true;
    }

    private function getLastHeartbeat($workerId) {
        $stmt = $this->db->prepare("
            SELECT * FROM worker_activity_logs 
            WHERE worker_id = ? AND activity_type = 'heartbeat'
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$workerId]);
        return $stmt->fetch();
    }

    private function getFailureThreshold() {
        // Default to 5 minutes if not configured
        return 300; // 5 minutes in seconds
    }

    private function handleFailure($workerId, $reason) {
        // Get or create alert record
        $alert = $this->getOrCreateAlert($workerId);

        // Update failure count and timestamp
        $this->updateFailureCount($workerId, $alert['failure_count'] + 1);

        // Check if we should send alert
        $alertLevel = $this->determineAlertLevel($alert['failure_count'] + 1);
        if ($alertLevel > $alert['next_alert_level']) {
            $this->sendAlert($workerId, $alertLevel, $reason);
            $this->updateNextAlertLevel($workerId, $alertLevel + 1);
        }
    }

    private function getOrCreateAlert($workerId) {
        $stmt = $this->db->prepare("SELECT * FROM heartbeat_alerts WHERE worker_id = ?");
        $stmt->execute([$workerId]);
        $alert = $stmt->fetch();

        if (!$alert) {
            $this->db->prepare("
                INSERT INTO heartbeat_alerts (worker_id, failure_count, next_alert_level)
                VALUES (?, 0, 1)
            ")->execute([$workerId]);
            return ['failure_count' => 0, 'next_alert_level' => 1];
        }

        return $alert;
    }

    private function updateFailureCount($workerId, $count) {
        $this->db->prepare("
            UPDATE heartbeat_alerts 
            SET failure_count = ?, last_failure = NOW() 
            WHERE worker_id = ?
        ")->execute([$count, $workerId]);
    }

    private function updateNextAlertLevel($workerId, $level) {
        $this->db->prepare("
            UPDATE heartbeat_alerts 
            SET next_alert_level = ? 
            WHERE worker_id = ?
        ")->execute([$level, $workerId]);
    }

    private function determineAlertLevel($failureCount) {
        // Simple linear escalation - can be made configurable
        return min(ceil($failureCount / 3), 5); // Max level 5
    }

    private function sendAlert($workerId, $level, $reason) {
        $message = "Worker $workerId heartbeat failure: $reason (Level $level alert)";
        $this->notificationService->sendAlert($workerId, $level, $message);
    }

    private function resetFailureCount($workerId) {
        $this->db->prepare("
            UPDATE heartbeat_alerts 
            SET failure_count = 0, next_alert_level = 1 
            WHERE worker_id = ?
        ")->execute([$workerId]);
    }
}
