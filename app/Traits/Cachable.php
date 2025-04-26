<?php

namespace App\Traits;

use App\Services\QueryCache;
use Illuminate\Database\Eloquent\Builder;

trait Cachable
{
    public static function bootCachable()
    {
        static::saved(function ($model) {
            app(QueryCache::class)->flushForModel(get_class($model));
        });

        static::deleted(function ($model) {
            app(QueryCache::class)->flushForModel(get_class($model));
        });
    }

    public function scopeCached(Builder $query, $ttl = null, $key = null)
    {
        return app(QueryCache::class)->remember($query, $ttl, $key);
    }

    public function scopeCachedPaginate(Builder $query, $perPage, $ttl = null, $key = null)
    {
        return app(QueryCache::class)->rememberPaginated($query, $perPage, $ttl, $key);
    }
}