<?php
namespace Core\Cache;

class MemoryCache implements CacheInterface {
    private array $cache = [];
    private array $expirations = [];

    public function set(string $key, $value, ?int $ttl = null): bool {
        $this->cache[$key] = $value;
        $this->expirations[$key] = $ttl ? time() + $ttl : null;
        return true;
    }

    public function get(string $key) {
        if (!$this->has($key)) {
            return null;
        }
        return $this->cache[$key];
    }

    public function delete(string $key): bool {
        unset($this->cache[$key]);
        unset($this->expirations[$key]);
        return true;
    }

    public function clear(): bool {
        $this->cache = [];
        $this->expirations = [];
        return true;
    }

    public function has(string $key): bool {
        if (!isset($this->cache[$key])) {
            return false;
        }

        if ($this->expirations[$key] && time() > $this->expirations[$key]) {
            $this->delete($key);
            return false;
        }

        return true;
    }
}
