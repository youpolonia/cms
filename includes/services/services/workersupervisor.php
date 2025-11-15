<?php
/**
 * Worker Supervisor Service - Manages worker health monitoring
 */
class WorkerSupervisor {
    private $db;
    private $alertService;
    
    const HEALTH_CRITICAL = 30;
    const HEALTH_WARNING = 70;
    const MAX_FAILURES = 3;

    public function __construct($db, $alertService = null) {
        $this->db = $db;
        $this->alertService = $alertService;
    }

    /**
     * Processes worker heartbeat and updates health metrics
     */
    public function processHeartbeat(string $workerId, array $metrics): array {
        // Calculate health score based on metrics
        $healthScore = $this->calculateHealthScore($metrics);
        $isHealthy = $healthScore > self::HEALTH_WARNING;

        // Update worker status
        $this->updateWorkerStatus($workerId, $healthScore, $isHealthy);

        return [
            'health_score' => $healthScore,
            'status' => $isHealthy ? 'healthy' : 'warning'
        ];
    }

    private function calculateHealthScore(array $metrics): int {
        // Base score
        $score = 100;

        // Deduct points for high memory usage
        if (isset($metrics['memory_usage']) && $metrics['memory_usage'] > 0.8) {
            $score -= 20;
        }

        // Deduct points for high CPU
        if (isset($metrics['cpu_usage']) && $metrics['cpu_usage'] > 0.9) {
            $score -= 30;
        }

        return max(0, min(100, $score));
    }

    private function updateWorkerStatus(string $workerId, int $healthScore, bool $isHealthy) {
        $stmt = $this->db->prepare("
            UPDATE workers 
            SET health_score = ?,
                failure_count = IF(?, 0, failure_count + 1),
                last_failure_time = IF(?, NULL, NOW())
            WHERE worker_id = ?
        ");
        $stmt->execute([$healthScore, $isHealthy, $isHealthy, $workerId]);
    }

    /**
     * Checks all workers and initiates recovery for failed ones
     */
    public function checkWorkers() {
        $workers = $this->db->query("
            SELECT worker_id, health_score, failure_count 
            FROM workers 
            WHERE last_seen > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ")->fetchAll();

        foreach ($workers as $worker) {
            if ($worker['health_score'] < self::HEALTH_CRITICAL || 
                $worker['failure_count'] >= self::MAX_FAILURES) {
                $this->initiateRecovery($worker['worker_id']);
            }
        }
    }

    private function initiateRecovery(string $workerId) {
        $worker = $this->db->query("
            SELECT * FROM workers
            WHERE worker_id = ?
        ")->fetch([$workerId]);

        if (!$worker) {
            return;
        }

        // Attempt graceful restart first
        $restartSuccess = $this->attemptGracefulRestart($workerId);
        
        if ($restartSuccess) {
            $this->db->query("
                UPDATE workers
                SET failure_count = 0,
                    health_score = 80,
                    recovery_attempts = recovery_attempts + 1
                WHERE worker_id = ?
            ", [$workerId]);

            if ($this->alertService) {
                $this->alertService->send("Worker $workerId successfully restarted");
            }
            return;
        }

        // If restart failed, mark for replacement
        $this->db->query("
            UPDATE workers
            SET needs_replacement = 1,
                recovery_attempts = recovery_attempts + 1
            WHERE worker_id = ?
        ", [$workerId]);

        if ($this->alertService) {
            $this->alertService->send("Worker $workerId failed recovery and needs replacement");
        }
    }

    private function attemptGracefulRestart(string $workerId): bool {
        // In a real implementation, this would call the worker's restart endpoint
        // For now we'll simulate 80% success rate
        return rand(1, 100) <= 80;
    }
}
