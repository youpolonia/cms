<?php
declare(strict_types=1);

class CacheService {
    private static ?CacheService $instance = null;
    private array $config = [];
    private array $cache = [];

    private function __construct() {
        $this->loadConfiguration();
    }

    public static function getInstance(): CacheService {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfiguration(): void {
        $this->config = [
            'enabled' => true,
            'default_ttl' => 3600, // 1 hour
            'max_items' => 1000
        ];
    }

    public function get(string $key): mixed {
        if (!$this->config['enabled'] || !isset($this->cache[$key])) {
            return null;
        }

        $item = $this->cache[$key];
        if ($item['expires'] < time()) {
            unset($this->cache[$key]);
            return null;
        }

        return $item['value'];
    }

    public function set(string $key, mixed $value, ?int $ttl = null): void {
        if (!$this->config['enabled']) {
            return;
        }

        $ttl = $ttl ?? $this->config['default_ttl'];
        $this->cache[$key] = [
            'value' => $value,
            'expires' => time() + $ttl
        ];

        $this->enforceLimits();
    }

    private function enforceLimits(): void {
        if (count($this->cache) > $this->config['max_items']) {
            // Remove oldest items first
            uasort($this->cache, fn($a, $b) => $a['expires'] <=> $b['expires']);
            $this->cache = array_slice($this->cache, 0, $this->config['max_items'], true);
        }
    }

    public function clear(?string $key = null): void {
        if ($key === null) {
            $this->cache = [];
        } else {
            unset($this->cache[$key]);
        }
    }
}
