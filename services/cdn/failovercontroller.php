<?php
declare(strict_types=1);

class FailoverController {
    private static array $config = [];
    private static array $healthStatus = [];

    public static function init(array $config): void {
        self::$config = $config;
    }

    public static function checkRegionHealth(string $region): bool {
        if (isset(self::$healthStatus[$region])) {
            return self::$healthStatus[$region];
        }

        $health = self::testEndpoint($region);
        self::$healthStatus[$region] = $health;
        return $health;
    }

    private static function testEndpoint(string $region): bool {
        $url = self::$config['regions'][$region]['health_check'] ?? '';
        if (empty($url)) {
            return false;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    public static function getFallbackRegion(string $failedRegion): string {
        if (!isset(self::$config['fallback_order'])) {
            return self::$config['default_region'] ?? 'global';
        }

        foreach (self::$config['fallback_order'] as $region) {
            if ($region !== $failedRegion && self::checkRegionHealth($region)) {
                return $region;
            }
        }
        return self::$config['default_region'] ?? 'global';
    }
}
