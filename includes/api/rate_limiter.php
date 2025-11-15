<?php
/**
 * Tenant-specific API Rate Limiter
 * 
 * Implements sliding window algorithm for fair rate limiting per tenant
 */

class ApiRateLimiter {
    private static $limits = [
        'default' => [
            'requests' => 100,
            'window' => 60 // seconds
        ]
    ];

    private $storage;
    private $tenantId;

    public function __construct($tenantId, $storage = null) {
        $this->tenantId = $tenantId;
        $this->storage = $storage ?? new FileBasedStorage();
    }

    public function checkLimit() {
        $limitConfig = self::$limits[$this->tenantId] ?? self::$limits['default'];
        $current = $this->storage->getCount($this->tenantId);
        
        if ($current >= $limitConfig['requests']) {
            return false;
        }

        $this->storage->increment($this->tenantId, $limitConfig['window']);
        return true;
    }

    public function getRemaining() {
        $limitConfig = self::$limits[$this->tenantId] ?? self::$limits['default'];
        $current = $this->storage->getCount($this->tenantId);
        return max(0, $limitConfig['requests'] - $current);
    }
}

interface RateLimitStorage {
    public function getCount($tenantId);
    public function increment($tenantId, $window);
}

class FileBasedStorage implements RateLimitStorage {
    private $path;

    public function __construct($path = null) {
        $this->path = $path ?? __DIR__ . '/../../storage/rate_limits/';
        if (!file_exists($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    public function getCount($tenantId) {
        $file = $this->getFilePath($tenantId);
        if (!file_exists($file)) return 0;
        
        $data = json_decode(file_get_contents($file), true);
        if (time() - $data['timestamp'] > $data['window']) {
            return 0;
        }
        return $data['count'];
    }

    public function increment($tenantId, $window) {
        $file = $this->getFilePath($tenantId);
        $count = $this->getCount($tenantId) + 1;
        
        file_put_contents($file, json_encode([
            'count' => $count,
            'timestamp' => time(),
            'window' => $window
        ]));
    }

    private function getFilePath($tenantId) {
        return $this->path . md5($tenantId) . '.json';
    }
}
