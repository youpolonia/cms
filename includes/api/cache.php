<?php
/**
 * API Response Caching System
 * 
 * Implements tenant-aware caching for API responses with TTL support
 */

class ApiCache {
    private $tenantId;
    private $storage;
    private $defaultTtl = 300; // 5 minutes

    public function __construct($tenantId, $storage = null) {
        $this->tenantId = $tenantId;
        $this->storage = $storage ?? new FileCacheStorage();
    }

    public function get($key) {
        $cacheKey = $this->buildKey($key);
        $data = $this->storage->get($cacheKey);
        
        if ($data && $data['expires'] > time()) {
            return $data['value'];
        }
        
        return null;
    }

    public function set($key, $value, $ttl = null) {
        $cacheKey = $this->buildKey($key);
        $ttl = $ttl ?? $this->defaultTtl;
        
        $this->storage->set($cacheKey, [
            'value' => $value,
            'expires' => time() + $ttl
        ]);
    }

    public function delete($key) {
        $cacheKey = $this->buildKey($key);
        $this->storage->delete($cacheKey);
    }

    public function clear() {
        $this->storage->clear($this->tenantId);
    }

    private function buildKey($key) {
        return "{$this->tenantId}_{$key}";
    }
}

interface CacheStorage {
    public function get($key);
    public function set($key, $value);
    public function delete($key);
    public function clear($tenantId);
}

class FileCacheStorage implements CacheStorage {
    private $path;

    public function __construct($path = null) {
        $this->path = $path ?? __DIR__ . '/../../storage/cache/';
        if (!file_exists($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    public function get($key) {
        $file = $this->getFilePath($key);
        if (!file_exists($file)) return null;
        
        $data = json_decode(file_get_contents($file), true);
        return $data ?: null;
    }

    public function set($key, $value) {
        $file = $this->getFilePath($key);
        file_put_contents($file, json_encode($value));
    }

    public function delete($key) {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function clear($tenantId) {
        $files = glob($this->path . "{$tenantId}_*");
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    private function getFilePath($key) {
        return $this->path . md5($key) . '.json';
    }
}

class MemoryCacheStorage implements CacheStorage {
    private $cache = [];

    public function get($key) {
        return $this->cache[$key] ?? null;
    }

    public function set($key, $value) {
        $this->cache[$key] = $value;
    }

    public function delete($key) {
        unset($this->cache[$key]);
    }

    public function clear($tenantId) {
        foreach ($this->cache as $key => $value) {
            if (strpos($key, "{$tenantId}_") === 0) {
                unset($this->cache[$key]);
            }
        }
    }
}
