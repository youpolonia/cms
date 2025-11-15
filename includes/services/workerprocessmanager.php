<?php
require_once __DIR__ . '/../../core/database.php';

/**
 * Worker Process Manager Service
 * Manages worker processes and job distribution
 */

class WorkerProcessManager {
    private $db;
    private $maxWorkers = 5;
    private $workerTypes = ['content', 'analytics', 'batch'];

    public function __construct() {
        $this->db = \core\Database::connection();
    }

    public function startWorker(string $type): string {
        $pdo = \core\Database::connection();
        
        // Check if we have available worker slots
        $activeWorkers = $pdo->query(
            "SELECT COUNT(*) FROM worker_processes WHERE status IN ('working', 'idle')"
        )->fetchColumn();

        if ($activeWorkers >= $this->maxWorkers) {
            throw new Exception("Maximum worker limit reached");
        }

        $workerId = uniqid();
        $pid = getmypid();

        $stmt = $pdo->prepare(
            "INSERT INTO worker_processes 
            (id, type, status, pid, created_at) 
            VALUES (?, ?, 'idle', ?, NOW())"
        );
        $stmt->execute([$workerId, $type, $pid]);

        return $workerId;
    }

    public function getNextJob(string $workerId): ?array {
        $pdo = \core\Database::connection();
        
        // Start transaction
        $pdo->beginTransaction();

        try {
            // Get next available job
            $stmt = $pdo->prepare(
                "SELECT * FROM worker_jobs 
                WHERE status = 'queued' 
                ORDER BY created_at ASC 
                LIMIT 1 FOR UPDATE"
            );
            $stmt->execute();
            $job = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($job) {
                // Update job status
                $updateStmt = $pdo->prepare(
                    "UPDATE worker_jobs SET 
                    status = 'processing',
                    process_id = ?,
                    started_at = NOW(),
                    attempts = attempts + 1
                    WHERE id = ?"
                );
                $updateStmt->execute([$workerId, $job['id']]);

                // Update worker status
                $workerStmt = $pdo->prepare(
                    "UPDATE worker_processes SET 
                    status = 'working',
                    last_job_id = ?,
                    last_heartbeat = NOW()
                    WHERE id = ?"
                );
                $workerStmt->execute([$job['id'], $workerId]);
            }

            $pdo->commit();
            return $job;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function completeJob(string $jobId, string $output = null): void {
        $pdo = \core\Database::connection();
        
        $stmt = $pdo->prepare(
            "UPDATE worker_jobs SET 
            status = 'completed',
            output = ?,
            completed_at = NOW()
            WHERE id = ?"
        );
        $stmt->execute([$output, $jobId]);

        // Update worker status back to idle
        $workerStmt = $pdo->prepare(
            "UPDATE worker_processes SET 
            status = 'idle',
            last_heartbeat = NOW()
            WHERE last_job_id = ?"
        );
        $workerStmt->execute([$jobId]);
    }

    public function failJob(string $jobId, string $error): void {
        $pdo = \core\Database::connection();
        
        $stmt = $pdo->prepare(
            "UPDATE worker_jobs SET 
            status = CASE 
                WHEN attempts >= max_attempts THEN 'failed' 
                ELSE 'queued' 
            END,
            output = ?
            WHERE id = ?"
        );
        $stmt->execute([$error, $jobId]);

        // Update worker status back to idle
        $workerStmt = $pdo->prepare(
            "UPDATE worker_processes SET 
            status = 'idle',
            last_heartbeat = NOW()
            WHERE last_job_id = ?"
        );
        $workerStmt->execute([$jobId]);
    }

    public function sendHeartbeat(string $workerId): void {
        $pdo = \core\Database::connection();
        
        $stmt = $pdo->prepare(
            "UPDATE worker_processes SET 
            last_heartbeat = NOW()
            WHERE id = ?"
        );
        $stmt->execute([$workerId]);
    }

    public function cleanupStaleWorkers(int $timeoutSeconds = 300): void {
        $pdo = \core\Database::connection();
        
        $stmt = $pdo->prepare(
            "UPDATE worker_processes SET 
            status = 'stopped'
            WHERE status IN ('working', 'idle')
            AND last_heartbeat < DATE_SUB(NOW(), INTERVAL ? SECOND)"
        );
        $stmt->execute([$timeoutSeconds]);
    }
}
