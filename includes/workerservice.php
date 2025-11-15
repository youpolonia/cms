<?php
class WorkerService {
    private static $workers = [];
    private static $metrics = [];
    private static $healthStatus = [];

    public static function registerWorker(string $workerId, array $config): void {
        if (empty($workerId)) {
            throw new InvalidArgumentException('Worker ID cannot be empty');
        }

        self::$workers[$workerId] = $config;
        self::$healthStatus[$workerId] = 'healthy';
        self::$metrics[$workerId] = [
            'start_time' => time(),
            'last_heartbeat' => time(),
            'total_tasks' => 0
        ];
    }

    public static function recordHeartbeat(string $workerId): void {
        if (!isset(self::$metrics[$workerId])) {
            throw new RuntimeException('Worker not registered');
        }
        self::$metrics[$workerId]['last_heartbeat'] = time();
        self::$healthStatus[$workerId] = 'healthy';
    }

    public static function incrementTaskCount(string $workerId): void {
        if (!isset(self::$metrics[$workerId])) {
            throw new RuntimeException('Worker not registered');
        }
        self::$metrics[$workerId]['total_tasks']++;
    }

    public static function getWorkerStatus(string $workerId): array {
        if (!isset(self::$metrics[$workerId])) {
            throw new RuntimeException('Worker not registered');
        }
        return [
            'health' => self::$healthStatus[$workerId],
            'metrics' => self::$metrics[$workerId]
        ];
    }

    public static function getAllWorkers(): array {
        return [
            'workers' => self::$workers,
            'health_status' => self::$healthStatus,
            'metrics' => self::$metrics
        ];
    }
}
