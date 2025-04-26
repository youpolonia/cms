<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ContentUserView;
use Illuminate\Support\Facades\Cache;

class ProcessAnalyticsData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // Clear all analytics caches when new data is processed
        Cache::store('analytics')->flush();
        
        // Clear specific cache keys
        $keys = Cache::store('analytics')->getRedis()->keys('*approval_analytics:*');
        foreach ($keys as $key) {
            Cache::store('analytics')->forget($key);
        }
        
        Cache::forget('analytics:time_spent');
        Cache::forget('analytics:user_segments');
        Cache::forget('analytics:scroll_depth');

        // Clean up expired entries from AnalyticsCacheService
        $cacheService = app(\App\Services\AnalyticsCacheService::class);
        $now = (int) (microtime(true) * 1000); // Milliseconds timestamp to match JS
        $expiredCount = 0;
        
        foreach ($cacheService->entries() as $entry) {
            if ($entry['expiresAt'] < $now) {
                $cacheService->clear($entry['key']);
                $expiredCount++;
            }
        }
        
        \Log::info("ProcessAnalyticsData: Cleaned up {$expiredCount} expired cache entries");

        // Cache time spent data (24 hours in milliseconds)
        Cache::remember('analytics:time_spent', 86400 * 1000, function() {
            return ContentUserView::selectRaw('
                FLOOR(time_spent/10)*10 as time_bucket,
                COUNT(*) as count
            ')
            ->groupBy('time_bucket')
            ->orderBy('time_bucket')
            ->get();
        });

        // Cache user segments (24 hours in milliseconds)
        Cache::remember('analytics:user_segments', 86400 * 1000, function() {
            return [
                'logged_in' => ContentUserView::whereNotNull('user_id')->count(),
                'anonymous' => ContentUserView::whereNull('user_id')->count()
            ];
        });

        // Cache scroll depth (24 hours in milliseconds)
        Cache::remember('analytics:scroll_depth', 86400 * 1000, function() {
            return ContentUserView::selectRaw('
                FLOOR(scroll_depth/10)*10 as scroll_bucket,
                COUNT(*) as count
            ')
            ->groupBy('scroll_bucket')
            ->orderBy('scroll_bucket')
            ->get();
        });
    }
}
