<?php

namespace App\Services;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Database\Eloquent\Builder;

class QueryCache
{
    protected $cache;
    protected $defaultTtl = 3600; // 1 hour

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function remember(Builder $query, $ttl = null, $key = null)
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $key = $key ?? $this->generateCacheKey($query);

        return $this->cache->remember($key, $ttl, function () use ($query) {
            return $query->get();
        });
    }

    public function rememberPaginated(Builder $query, $perPage, $ttl = null, $key = null)
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $key = $key ?? $this->generateCacheKey($query) . ':page:' . request()->get('page', 1);

        return $this->cache->remember($key, $ttl, function () use ($query, $perPage) {
            return $query->paginate($perPage);
        });
    }

    protected function generateCacheKey(Builder $query)
    {
        return md5(
            $query->toSql() . 
            serialize($query->getBindings()) . 
            request()->fullUrl()
        );
    }

    public function flushForModel($modelClass)
    {
        $this->cache->tags($modelClass)->flush();
    }
}