<?php
declare(strict_types=1);

class GeoDNSService {
    private static array $config = [];
    private static array $regionEndpoints = [];

    public static function init(array $config): void {
        self::$config = $config;
        self::$regionEndpoints = $config['region_endpoints'] ?? [];
    }

    public static function getOptimalEndpoint(string $ip): string {
        $region = self::detectRegion($ip);
        return self::$regionEndpoints[$region] ?? self::$config['default_endpoint'];
    }

    private static function detectRegion(string $ip): string {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('Invalid IP address');
        }

        $geoData = self::lookupGeoData($ip);
        return $geoData['region'] ?? 'default';
    }

    private static function lookupGeoData(string $ip): array {
        if (self::$config['local_testing'] ?? false) {
            return ['region' => 'eu']; // Test value
        }

        $ch = curl_init(self::$config['geo_api_url'] . '?ip=' . urlencode($ip));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: Bearer ' . self::$config['api_key']
            ]
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response ? json_decode($response, true) : [];
    }
}
