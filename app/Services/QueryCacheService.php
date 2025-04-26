<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;

class QueryCacheService
{
    protected $defaultTtl = 3600; // 1 hour

    public function remember(Builder $query, string $key, ?int $ttl = null, array $tags = [])
    {
        $ttl = $ttl ?? $this->defaultTtl;
        
        return Cache::tags($tags)->remember(
            $key,
            $ttl,
            fn() => $query->get()
        );
    }

    public function flushByTag(string $tag): void
    {
        Cache::tags([$tag])->flush();
    }

    public function flushAll(): void
    {
        Cache::flush();
    }
}