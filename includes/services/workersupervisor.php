<?php
declare(strict_types=1);

class WorkerSupervisor {
    private $db;
    private $maxWorkers = 10;
    private $minWorkers = 2;
    private $cpuThreshold = 80;
    private $memoryThreshold = 90;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function registerWorker(string $workerId, array $capabilities): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO workers (worker_id, capabilities, last_seen, status) 
             VALUES (:worker_id, :capabilities, NOW(), 'active')
             ON DUPLICATE KEY UPDATE 
                capabilities = VALUES(capabilities),
                last_seen = VALUES(last_seen),
                status = 'active'"
        );

        return $stmt->execute([
            ':worker_id' => $workerId,
            ':capabilities' => json_encode($capabilities)
        ]);
    }

    public function processHeartbeat(string $workerId, array $metrics): bool {
        $stmt = $this->db->prepare(
            "UPDATE workers SET 
                last_seen = NOW(),
                metrics = :metrics
             WHERE worker_id = :worker_id"
        );

        $success = $stmt->execute([
            ':worker_id' => $workerId,
            ':metrics' => json_encode($metrics)
        ]);

        if ($success) {
            $this->logMetrics($workerId, $metrics);
        }

        return $success;
    }

    private function logMetrics(string $workerId, array $metrics): void {
        $stmt = $this->db->prepare(
            "INSERT INTO worker_metrics 
                (worker_id, cpu_usage, memory_usage, timestamp)
             VALUES 
                (:worker_id, :cpu_usage, :memory_usage, NOW())"
        );

        $stmt->execute([
            ':worker_id' => $workerId,
            ':cpu_usage' => $metrics['cpu'] ?? 0,
            ':memory_usage' => $metrics['memory'] ?? 0
        ]);
    }

    public function getActiveWorkers(): array {
        $stmt = $this->db->query(
            "SELECT worker_id, capabilities, metrics 
             FROM workers 
             WHERE status = 'active' 
             AND last_seen > DATE_SUB(NOW(), INTERVAL 5 MINUTE)"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function evaluateScaling(): array {
        $metrics = $this->getSystemMetrics();
        $actions = [];

        if ($metrics['cpu'] > $this->cpuThreshold || $metrics['memory'] > $this->memoryThreshold) {
            $actions[] = 'scale_up';
        } elseif ($metrics['cpu'] < ($this->cpuThreshold / 2) && 
                 $metrics['memory'] < ($this->memoryThreshold / 2)) {
            $actions[] = 'scale_down';
        }

        return $actions;
    }

    private function getSystemMetrics(): array {
        $stmt = $this->db->query(
            "SELECT 
                AVG(cpu_usage) as cpu,
                AVG(memory_usage) as memory
             FROM worker_metrics
             WHERE timestamp > DATE_SUB(NOW(), INTERVAL 5 MINUTE)"
        );
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['cpu' => 0, 'memory' => 0];
    }
}
