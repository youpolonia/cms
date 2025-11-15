<?php
declare(strict_types=1);

namespace Includes\Cache;

class RedisCache {
    private \Redis $redis;
    private array $config;

    public function __construct(array $config) {
        $this->config = $config;
        $this->connect();
    }

    private function connect(): void {
        $this->redis = new \Redis();
        
        try {
            $connected = $this->redis->connect(
                $this->config['host'] ?? '127.0.0.1',
                (int)($this->config['port'] ?? 6379),
                (float)($this->config['timeout'] ?? 2.5)
            );

            if (!$connected) {
                throw new \RuntimeException('Failed to connect to Redis server');
            }

            if (isset($this->config['password'])) {
                if (!$this->redis->auth($this->config['password'])) {
                    throw new \RuntimeException('Redis authentication failed');
                }
            }

            if (isset($this->config['database'])) {
                $this->redis->select((int)$this->config['database']);
            }

            // Set client options
            foreach ($this->config['options'] ?? [] as $option => $value) {
                $this->redis->setOption($option, $value);
            }

        } catch (\RedisException $e) {
            throw new \RuntimeException("Redis connection error: " . $e->getMessage());
        }
    }

    public function get(string $key): mixed {
        try {
            $value = $this->redis->get($key);
            return $value !== false ? unserialize($value) : null;
        } catch (\RedisException $e) {
            throw new \RuntimeException("Redis get operation failed: " . $e->getMessage());
        }
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool {
        try {
            $serialized = serialize($value);
            return $ttl 
                ? $this->redis->setex($key, $ttl, $serialized)
                : $this->redis->set($key, $serialized);
        } catch (\RedisException $e) {
            throw new \RuntimeException("Redis set operation failed: " . $e->getMessage());
        }
    }

    public function delete(string $key): bool {
        try {
            return (bool)$this->redis->del($key);
        } catch (\RedisException $e) {
            throw new \RuntimeException("Redis delete operation failed: " . $e->getMessage());
        }
    }

    public function flush(): bool {
        try {
            return $this->redis->flushDB();
        } catch (\RedisException $e) {
            throw new \RuntimeException("Redis flush operation failed: " . $e->getMessage());
        }
    }
}
