<?php

class RateLimiter {
    private static $logFile = __DIR__.'/../logs/rate_limit.log';
    private static $limit = 5; // 5 attempts
    private static $window = 60; // 60 seconds

    public static function check(string $ip): bool {
        self::cleanupOldEntries();
        
        $attempts = self::getAttempts($ip);
        if (count($attempts) >= self::$limit) {
            return false;
        }

        self::logAttempt($ip);
        return true;
    }

    private static function getAttempts(string $ip): array {
        if (!file_exists(self::$logFile)) {
            return [];
        }

        $data = file_get_contents(self::$logFile);
        $entries = explode("\n", trim($data));
        
        $attempts = [];
        foreach ($entries as $entry) {
            if (empty($entry)) continue;
            
            list($entryIp, $timestamp) = explode('|', $entry);
            if ($entryIp === $ip) {
                $attempts[] = (int)$timestamp;
            }
        }

        return $attempts;
    }

    private static function logAttempt(string $ip): void {
        $timestamp = time();
        file_put_contents(self::$logFile, "$ip|$timestamp\n", FILE_APPEND);
    }

    private static function cleanupOldEntries(): void {
        if (!file_exists(self::$logFile)) {
            return;
        }

        $data = file_get_contents(self::$logFile);
        $entries = explode("\n", trim($data));
        
        $currentTime = time();
        $validEntries = [];
        
        foreach ($entries as $entry) {
            if (empty($entry)) continue;
            
            list($ip, $timestamp) = explode('|', $entry);
            if ($currentTime - (int)$timestamp <= self::$window) {
                $validEntries[] = $entry;
            }
        }

        file_put_contents(self::$logFile, implode("\n", $validEntries)."\n");
    }
}
