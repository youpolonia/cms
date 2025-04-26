<?php

namespace App\Services\Analytics;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AiUsageService
{
    protected const CACHE_KEY = 'ai_usage_stats';
    protected const CACHE_TTL = 3600; // 1 hour

    /**
     * Track AI usage for a user
     */
    public function trackUsage(User $user, string $endpoint, int $tokensUsed): void
    {
        DB::transaction(function () use ($user, $endpoint, $tokensUsed) {
            // Update user's total usage
            $user->increment('ai_usage_count', $tokensUsed);

            // Update daily usage stats
            DB::table('ai_usage_stats')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'date' => now()->format('Y-m-d'),
                    'endpoint' => $endpoint
                ],
                [
                    'tokens_used' => DB::raw("tokens_used + $tokensUsed"),
                    'updated_at' => now()
                ]
            );

            // Clear cached stats
            Cache::forget(self::CACHE_KEY.':'.$user->id);
        });
    }

    /**
     * Get usage statistics for a user
     */
    public function getUserStats(User $user, string $period = '30d'): array
    {
        return Cache::remember(
            self::CACHE_KEY.':'.$user->id.':'.$period,
            self::CACHE_TTL,
            function () use ($user, $period) {
                $query = DB::table('ai_usage_stats')
                    ->where('user_id', $user->id);

                if ($period === '7d') {
                    $query->where('date', '>=', now()->subDays(7));
                } elseif ($period === '30d') {
                    $query->where('date', '>=', now()->subDays(30));
                }

                return $query->select(
                    'date',
                    'endpoint',
                    DB::raw('SUM(tokens_used) as tokens_used')
                )
                ->groupBy('date', 'endpoint')
                ->orderBy('date')
                ->get()
                ->toArray();
            }
        );
    }

    /**
     * Get estimated cost for tokens used
     */
    public function calculateCost(int $tokens): float
    {
        // Using OpenAI's pricing model (approximate)
        $costPerThousandTokens = 0.002; // $0.002 per 1k tokens
        return ($tokens / 1000) * $costPerThousandTokens;
    }
}