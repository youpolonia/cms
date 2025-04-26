<?php

namespace App\Traits;

use App\Services\QueryCacheService;
use Illuminate\Support\Facades\App;

trait CacheableRepository
{
    protected QueryCacheService $cacheService;
    protected string $cacheKeyPrefix;
    protected int $defaultTtl = 3600;

    public function __construct()
    {
        $this->cacheService = App::make(QueryCacheService::class);
        $this->cacheKeyPrefix = strtolower(class_basename($this)) . '_';
    }

    protected function cacheQuery($query, string $key, ?int $ttl = null, array $tags = [])
    {
        return $this->cacheService->remember(
            $query,
            $this->cacheKeyPrefix . $key,
            $ttl ?? $this->defaultTtl,
            $tags
        );
    }

    protected function invalidateCache(array $tags): void
    {
        $this->cacheService->flushByTag($tags);
    }
}