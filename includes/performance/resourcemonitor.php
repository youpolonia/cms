<?php
declare(strict_types=1);

/**
 * Performance - Resource Monitor
 * Tracks system resources and triggers alerts
 */
class ResourceMonitor {
    private static string $logFile = __DIR__ . '/../../logs/resource_monitor.log';
    private static array $thresholds = [
        'cpu' => 80,    // %
        'memory' => 90, // %
        'disk' => 85    // %
    ];
    private static int $collectionInterval = 60; // seconds

    /**
     * Collect and store current resource metrics
     */
    public static function collectMetrics(): void {
        $metrics = [
            'timestamp' => time(),
            'cpu' => self::getCpuUsage(),
            'memory' => self::getMemoryUsage(),
            'disk' => self::getDiskUsage(),
            'processes' => self::getProcessCount()
        ];

        self::storeMetrics($metrics);
        self::checkThresholds($metrics);
    }

    private static function getCpuUsage(): float {
        // Get CPU usage from /proc/stat or sys_getloadavg()
        $load = sys_getloadavg();
        return round($load[0] * 100 / sysconf(_SC_NPROCESSORS_ONLN), 2);
    }

    private static function getMemoryUsage(): float {
        $memInfo = file_get_contents('/proc/meminfo');
        preg_match_all('/(\w+):\s+(\d+)/', $memInfo, $matches);
        $memData = array_combine($matches[1], $matches[2]);

        $used = $memData['MemTotal'] - $memData['MemFree'] - $memData['Buffers'] - $memData['Cached'];
        return round(($used / $memData['MemTotal']) * 100, 2);
    }

    private static function getDiskUsage(): float {
        $diskInfo = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        return round((1 - ($diskInfo / $diskTotal)) * 100, 2);
    }

    private static function getProcessCount(): int {
        $processes = glob('/proc/[0-9]*');
        return count($processes);
    }

    private static function storeMetrics(array $metrics): void {
        // Implementation would save to database
        file_put_contents(
            __DIR__ . '/../../storage/metrics/' . $metrics['timestamp'] . '.json',
            json_encode($metrics)
        );
    }

    private static function checkThresholds(array $metrics): void {
        foreach (self::$thresholds as $resource => $threshold) {
            if ($metrics[$resource] > $threshold) {
                $message = sprintf(
                    "ALERT: %s usage at %.2f%% (threshold: %d%%)",
                    strtoupper($resource),
                    $metrics[$resource],
                    $threshold
                );
                self::logAlert($message);
                ScalingController::triggerScaleEvent($resource);
            }
        }
    }

    private static function logAlert(string $message): void {
        file_put_contents(
            self::$logFile,
            date('Y-m-d H:i:s') . " - $message\n",
            FILE_APPEND
        );
    }

    /**
     * Get recent metrics for dashboard
     */
    public static function getRecentMetrics(int $hours = 24): array {
        $files = glob(__DIR__ . '/../../storage/metrics/*.json');
        $metrics = [];
        $cutoff = time() - ($hours * 3600);

        foreach ($files as $file) {
            $timestamp = (int) basename($file, '.json');
            if ($timestamp >= $cutoff) {
                $metrics[] = json_decode(file_get_contents($file), true);
            }
        }

        return $metrics;
    }

    // BREAKPOINT: Continue with alerting and scaling integration
}
