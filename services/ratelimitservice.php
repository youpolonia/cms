<?php
declare(strict_types=1);

class RateLimitService {
    private static ?RateLimitService $instance = null;
    private array $config = [];
    private array $counters = [];

    private function __construct() {
        $this->loadConfiguration();
    }

    public static function getInstance(): RateLimitService {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfiguration(): void {
        // TODO: Load rate limit configuration
        $this->config = [
            'global_limit' => 1000, // requests per minute
            'ip_limit' => 100, // requests per minute per IP
            'user_limit' => 50, // requests per minute per user
            'window_seconds' => 60
        ];
    }

    public function checkRateLimit(?string $ip = null, ?int $userId = null): bool {
        $currentTime = time();
        $windowStart = $currentTime - $this->config['window_seconds'];
        
        // Global rate limit check
        $globalCount = $this->getWindowCount('global', $windowStart);
        if ($globalCount >= $this->config['global_limit']) {
            return false;
        }

        // IP-based rate limit
        if ($ip !== null) {
            $ipCount = $this->getWindowCount("ip_$ip", $windowStart);
            if ($ipCount >= $this->config['ip_limit']) {
                return false;
            }
        }

        // User-based rate limit
        if ($userId !== null) {
            $userCount = $this->getWindowCount("user_$userId", $windowStart);
            if ($userCount >= $this->config['user_limit']) {
                return false;
            }
        }

        return true;
    }

    private function getWindowCount(string $key, int $windowStart): int {
        if (!isset($this->counters[$key])) {
            return 0;
        }

        // Filter out expired entries
        $this->counters[$key] = array_filter(
            $this->counters[$key],
            fn($timestamp) => $timestamp >= $windowStart
        );

        return count($this->counters[$key]);
    }

    public function incrementCounter(?string $ip = null, ?int $userId = null): void {
        $currentTime = time();
        
        // Global counter
        $this->counters['global'][] = $currentTime;
        
        // IP counter
        if ($ip !== null) {
            $this->counters["ip_$ip"][] = $currentTime;
        }
        
        // User counter
        if ($userId !== null) {
            $this->counters["user_$userId"][] = $currentTime;
        }
    }
}
