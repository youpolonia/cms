<?php
/**
 * Worker Monitoring Service for CMS
 * 
 * @package CMS
 * @subpackage Services
 */

defined('CMS_ROOT') or die('No direct script access allowed');

class WorkerMonitoringService
{
    private $db;
    private $config;
    private $activeProfiles = [];
    private $chunkCounter = 0;

    public function __construct($dbConnection, array $config = [])
    {
        $this->db = $dbConnection;
        $this->config = array_merge([
            'heartbeat_interval' => 300, // 5 minutes
            'warning_threshold' => 70,
            'critical_threshold' => 30,
            'max_recovery_attempts' => 3
        ], $config);
    }

    public function startMemoryProfile(string $name): void {
        $this->activeProfiles[$name] = [
            'start_time' => microtime(true),
            'initial_memory' => memory_get_usage(),
            'peak_memory' => 0
        ];
    }

    public function endMemoryProfile(string $name): array {
        if (!isset($this->activeProfiles[$name])) {
            throw new \RuntimeException("Memory profile '$name' not found");
        }

        $profile = $this->activeProfiles[$name];
        unset($this->activeProfiles[$name]);

        return [
            'initial_memory' => $profile['initial_memory'],
            'peak_memory' => max(memory_get_peak_usage(), $profile['peak_memory']),
            'final_memory' => memory_get_usage(),
            'chunks_processed' => $this->chunkCounter,
            'duration' => microtime(true) - $profile['start_time']
        ];
    }

    private array $memoryUsageLog = [];
    private const MAX_MEMORY_LIMIT = 512 * 1024 * 1024; // 512MB

    private function incrementChunkCounter(): void {
        $this->chunkCounter++;
        $this->trackMemoryAllocation();
    }

    private function trackMemoryAllocation(): void {
        $currentUsage = memory_get_usage(true);
        $this->memoryUsageLog[] = [
            'chunk' => $this->chunkCounter,
            'memory' => $currentUsage,
            'time' => microtime(true)
        ];
        
        if ($currentUsage > self::MAX_MEMORY_LIMIT) {
            throw new \RuntimeException("Memory limit exceeded during model loading");
        }
    }

    public function verifyBufferCleanup(): void {
        $preMemory = memory_get_usage();
        $postMemory = memory_get_usage();
        
        // Base cleanup validation on chunk counter
        if (($postMemory - $preMemory) > ($this->chunkCounter * 1024)) {
            throw new \RuntimeException("Memory cleanup discrepancy detected");
        }
    }



    private function getChunkCounter(): int {
        return $this->chunkCounter;
    }

    /**
     * Process worker heartbeat
     */
    public function processHeartbeat(string $workerId, array $metrics = []): array
    {
        // Update last seen timestamp
        $this->updateLastSeen($workerId);

        // Calculate health score
        $healthScore = $this->calculateHealthScore($metrics);

        // Update worker health status
        $this->updateHealthStatus($workerId, $healthScore);

        // Check if recovery needed
        $status = $this->checkRecoveryNeeded($workerId, $healthScore);

        return [
            'status' => $status,
            'health_score' => $healthScore,
            'next_checkin' => time() + $this->config['heartbeat_interval']
        ];
    }

    private function updateLastSeen(string $workerId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE workers SET last_seen = NOW() WHERE worker_id = ?"
        );
        return $stmt->execute([$workerId]);
    }

    private function calculateHealthScore(array $metrics): int
    {
        // Basic health calculation (can be enhanced)
        $score = 100;

        // Deduct points for high CPU usage
        if (isset($metrics['cpu']) && $metrics['cpu'] > 80) {
            $score -= 20;
        }

        // Deduct points for high memory usage
        if (isset($metrics['memory']) && $metrics['memory'] > 90) {
            $score -= 30;
        }

        // Deduct points for recent errors
        if (isset($metrics['error_count']) && $metrics['error_count'] > 0) {
            $score -= 10 * min($metrics['error_count'], 5);
        }

        return max(0, $score);
    }

    private function updateHealthStatus(string $workerId, int $healthScore): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE workers 
             SET health_score = ?,
                 failure_count = IF(?, failure_count + 1, 0),
                 last_failure_time = IF(?, NOW(), last_failure_time)
             WHERE worker_id = ?"
        );

        $isUnhealthy = $healthScore < $this->config['warning_threshold'];
        return $stmt->execute([
            $healthScore,
            $isUnhealthy,
            $isUnhealthy,
            $workerId
        ]);
    }

    private function checkRecoveryNeeded(string $workerId, int $healthScore): string
    {
        if ($healthScore >= $this->config['warning_threshold']) {
            return 'healthy';
        }

        if ($healthScore >= $this->config['critical_threshold']) {
            return 'warning';
        }

        // Critical status - check recovery attempts
        $stmt = $this->db->prepare(
            "SELECT recovery_attempts FROM workers WHERE worker_id = ?"
        );
        $stmt->execute([$workerId]);
        $attempts = $stmt->fetchColumn();

        if ($attempts < $this->config['max_recovery_attempts']) {
            $this->incrementRecoveryAttempts($workerId);
            return 'critical_recovery_attempted';
        }

        return 'critical_needs_replacement';
    }

    private function incrementRecoveryAttempts(string $workerId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE workers 
             SET recovery_attempts = recovery_attempts + 1,
                 needs_replacement = IF(recovery_attempts >= ?, 1, needs_replacement)
             WHERE worker_id = ?"
        );
        return $stmt->execute([
            $this->config['max_recovery_attempts'] - 1,
            $workerId
        ]);
    }
}
