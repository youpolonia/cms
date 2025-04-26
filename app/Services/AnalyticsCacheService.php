<?php

namespace App\Services;

use App\Models\AnalyticsCache;
use App\Models\ContentVersion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class AnalyticsCacheService
{
    protected $cacheTtl = 86400; // 24 hours

    public function getStats(ContentVersion $version1, ContentVersion $version2): ?array
    {
        $cacheKey = $this->getCacheKey($version1->id, $version2->id);

        // Try Redis first
        if ($stats = Redis::get($cacheKey)) {
            return json_decode($stats, true);
        }

        // Fallback to database cache
        $cache = AnalyticsCache::valid()
            ->where('version1_id', $version1->id)
            ->where('version2_id', $version2->id)
            ->first();

        if ($cache) {
            // Store in Redis for faster access
            Redis::setex($cacheKey, $this->cacheTtl, json_encode($cache->toArray()));
            return $cache->toArray();
        }

        return null;
    }

    public function storeStats(ContentVersion $version1, ContentVersion $version2, array $stats): void
    {
        $cacheKey = $this->getCacheKey($version1->id, $version2->id);

        // Store in Redis
        Redis::setex($cacheKey, $this->cacheTtl, json_encode($stats));

        // Store in database
        AnalyticsCache::updateOrCreate(
            [
                'version1_id' => $version1->id,
                'version2_id' => $version2->id
            ],
            array_merge($stats, [
                'expires_at' => now()->addSeconds($this->cacheTtl)
            ])
        );
    }

    protected function getCacheKey(int $version1Id, int $version2Id): string
    {
        return "analytics:{$version1Id}:{$version2Id}";
    }
}