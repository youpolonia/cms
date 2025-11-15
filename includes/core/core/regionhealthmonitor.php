<?php
declare(strict_types=1);

class RegionHealthMonitor {
    private const CHECK_INTERVAL = 30; // seconds
    private static array $regionStatus = [];

    public static function startMonitoring(): void {
        // This would be called from a cron job or scheduled task
        while (true) {
            self::checkAllRegions();
            sleep(self::CHECK_INTERVAL);
        }
    }

    public static function checkAllRegions(): array {
        foreach (RegionSyncService::getRegions() as $regionId => $region) {
            self::$regionStatus[$regionId] = self::checkRegionHealth(
                $region['endpoint']
            );
        }
        return self::$regionStatus;
    }

    private static function checkRegionHealth(string $endpoint): array {
        $start = microtime(true);
        $ch = curl_init($endpoint . '/health');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $httpCode === 200 ? 'healthy' : 'unhealthy',
            'response_time' => microtime(true) - $start,
            'last_check' => time(),
            'response' => $response ? json_decode($response, true) : null
        ];
    }

    public static function getRegionStatus(string $regionId): ?array {
        return self::$regionStatus[$regionId] ?? null;
    }

    public static function triggerFailover(string $failedRegion): void {
        // Implementation would coordinate with DNS and caching systems
        // This is a simplified placeholder
        error_log("Failover triggered for region: $failedRegion");
    }
}
