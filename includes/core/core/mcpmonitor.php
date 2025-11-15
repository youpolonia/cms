<?php
/**
 * MCP Monitoring System
 * Handles metrics collection and alerting
 */

class MCPMonitor {
    private static $metrics = [];
    private static $alerts = [];

    public static function getAverageResponseTime(string $server): float {
        // Get from cache or calculate
        return self::$metrics[$server]['avg_response'] ?? 0.0;
    }

    public static function getErrorRates(): array {
        return array_map(function($server) {
            return [
                'errors' => $server['errors'] ?? 0,
                'requests' => $server['requests'] ?? 0
            ];
        }, self::$metrics);
    }

    public static function getActiveConnections(): int {
        return array_sum(array_column(self::$metrics, 'connections')) ?? 0;
    }

    public static function logRequest(string $server, float $responseTime, bool $isError = false): void {
        if (!isset(self::$metrics[$server])) {
            self::$metrics[$server] = [
                'requests' => 0,
                'errors' => 0,
                'total_response' => 0,
                'connections' => 0
            ];
        }

        self::$metrics[$server]['requests']++;
        self::$metrics[$server]['total_response'] += $responseTime;
        self::$metrics[$server]['avg_response'] = 
            self::$metrics[$server]['total_response'] / self::$metrics[$server]['requests'];

        if ($isError) {
            self::$metrics[$server]['errors']++;
        }

        self::checkAlerts($server);
    }

    private static function checkAlerts(string $server): void {
        foreach (self::$alerts[$server] ?? [] as $alert) {
            if (self::shouldTriggerAlert($server, $alert)) {
                self::triggerAlert($server, $alert);
            }
        }
    }

    public static function configureAlert(string $server, array $config): void {
        self::$alerts[$server][] = $config;
    }
}
