<?php
declare(strict_types=1);

class EdgeCacheService {
    private static array $config = [];
    private static string $fallbackDir = '/cdn_fallback';

    public static function init(array $config): void {
        self::$config = $config;
        self::ensureFallbackDir();
    }

    private static function ensureFallbackDir(): void {
        if (!file_exists(self::$fallbackDir)) {
            mkdir(self::$fallbackDir, 0755, true);
        }
    }

    public static function purge(string $url): bool {
        if (!self::validateUrl($url)) {
            throw new InvalidArgumentException('Invalid URL format');
        }

        $purgeResult = self::callCdnApi('purge', ['url' => $url]);
        
        if (!$purgeResult['success']) {
            return self::handlePurgeFailure($url);
        }

        return true;
    }

    private static function callCdnApi(string $endpoint, array $data): array {
        $ch = curl_init(self::$config['api_url'] . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . self::$config['api_key']
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'success' => $httpCode === 200,
            'response' => $response ? json_decode($response, true) : null
        ];
    }

    private static function handlePurgeFailure(string $url): bool {
        $fallbackPath = self::$fallbackDir . '/' . md5($url) . '.cache';
        if (file_exists($fallbackPath)) {
            unlink($fallbackPath);
        }
        return false;
    }

    private static function validateUrl(string $url): bool {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
