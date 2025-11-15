<?php
declare(strict_types=1);

class AnalyticsCollector {
    private const MAX_PAYLOAD_SIZE = 1024; // 1KB
    
    public static function track(array $data, string $tenantId): bool {
        if (self::validatePayload($data, $tenantId) === false) {
            return false;
        }

        $storagePath = self::getStoragePath($tenantId);
        $currentData = self::loadExistingData($storagePath);
        $currentData[] = [
            'timestamp' => time(),
            'data' => $data
        ];

        return file_put_contents($storagePath, json_encode($currentData)) !== false;
    }

    private static function validatePayload(array $data, string $tenantId): bool {
        if (strlen(json_encode($data)) > self::MAX_PAYLOAD_SIZE) {
            return false;
        }
        return !empty($tenantId);
    }

    private static function getStoragePath(string $tenantId): string {
        $tenantDir = "analytics/{$tenantId}";
        if (!is_dir($tenantDir)) {
            mkdir($tenantDir, 0755, true);
        }
        return "{$tenantDir}/" . date('Y-m-d') . '.json';
    }

    private static function loadExistingData(string $path): array {
        if (!file_exists($path)) {
            return [];
        }
        return json_decode(file_get_contents($path), true) ?: [];
    }
}
