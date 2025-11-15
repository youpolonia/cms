<?php
/**
 * API Rate Limiter
 */

declare(strict_types=1);

namespace CMS\API;

class RateLimiter
{
    private const LIMIT_PER_MINUTE = 60;
    private const LIMIT_PER_HOUR = 1000;
    private const STORAGE_DIR = __DIR__ . '/../../../storage/api_limits/';

    public function checkLimit(): void
    {
        $this->ensureStorageDirExists();
        
        $clientId = $this->getClientIdentifier();
        $minuteKey = $this->getMinuteKey();
        $hourKey = $this->getHourKey();

        $minuteCount = $this->getCount($minuteKey, $clientId);
        $hourCount = $this->getCount($hourKey, $clientId);

        if ($minuteCount >= self::LIMIT_PER_MINUTE) {
            throw new \RuntimeException('API rate limit exceeded (per minute)', 429);
        }

        if ($hourCount >= self::LIMIT_PER_HOUR) {
            throw new \RuntimeException('API rate limit exceeded (per hour)', 429);
        }

        $this->incrementCount($minuteKey, $clientId);
        $this->incrementCount($hourKey, $clientId);
    }

    private function getClientIdentifier(): string
    {
        $token = $_SERVER['HTTP_X_API_TOKEN'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        return $token ? hash('sha256', $token) : $ip;
    }

    private function getMinuteKey(): string
    {
        return 'minute_' . date('Y-m-d_H-i');
    }

    private function getHourKey(): string
    {
        return 'hour_' . date('Y-m-d_H');
    }

    private function ensureStorageDirExists(): void
    {
        if (!file_exists(self::STORAGE_DIR)) {
            mkdir(self::STORAGE_DIR, 0755, true);
        }
    }

    private function getCount(string $periodKey, string $clientId): int
    {
        $filePath = self::STORAGE_DIR . $periodKey . '_' . $clientId . '.cnt';

        if (file_exists($filePath)) {
            return (int)file_get_contents($filePath);
        }

        return 0;
    }

    private function incrementCount(string $periodKey, string $clientId): void
    {
        $filePath = self::STORAGE_DIR . $periodKey . '_' . $clientId . '.cnt';
        $count = $this->getCount($periodKey, $clientId) + 1;
        file_put_contents($filePath, (string)$count);
    }
}
