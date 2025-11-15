<?php

declare(strict_types=1);

namespace Includes\Config;

require_once __DIR__ . '/../FileCache.php';

use FileCache;

class ConfigCache implements ConfigInterface
{
    protected FileCache $cache;
    protected string $env;

    public function __construct(FileCache $cache, string $env = 'production') 
    {
        $this->cache = $cache;
        $this->env = $env;
    }

    public function get(string $key, $default = null)
    {
        $cacheKey = $this->getCacheKey($key);
        $cached = $this->cache->get($cacheKey);
        
        if ($cached !== false) {
            return $cached;
        }

        return $default;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function all(): array
    {
        throw new \RuntimeException('Not implemented - use ConfigLoader for full config');
    }

    public function set(string $key, $value, ?int $ttl = null): void
    {
        $cacheKey = $this->getCacheKey($key);
        $this->cache->set($cacheKey, $value, $ttl);
    }

    public function clear(string $key): void
    {
        $cacheKey = $this->getCacheKey($key);
        $this->cache->clear($cacheKey);
    }

    protected function getCacheKey(string $key): string
    {
        return 'config_'.$this->env.'_'.md5($key);
    }
}
