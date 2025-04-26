<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AiUsageService
{
    protected string $dailyCacheKey = 'ai_usage_daily_';
    protected string $monthlyCacheKey = 'ai_usage_monthly_';

    /**
     * Get user's current usage stats
     */
    public function getUserUsage(User $user): array
    {
        $daily = $this->getDailyUsage($user);
        $monthly = $this->getMonthlyUsage($user);

        return [
            'daily' => $daily,
            'daily_limit' => $user->ai_daily_limit,
            'daily_remaining' => max(0, $user->ai_daily_limit - $daily),
            'monthly' => $monthly,
            'monthly_limit' => $user->ai_monthly_limit,
            'monthly_remaining' => max(0, $user->ai_monthly_limit - $monthly),
        ];
    }

    /**
     * Check if user can perform AI operation
     */
    public function canUseAi(User $user): bool
    {
        return !$this->isDailyLimitExceeded($user) && 
               !$this->isMonthlyLimitExceeded($user);
    }

    /**
     * Check if daily limit is exceeded
     */
    public function isDailyLimitExceeded(User $user): bool
    {
        return $this->getDailyUsage($user) >= $user->ai_daily_limit;
    }

    /**
     * Check if monthly limit is exceeded
     */
    public function isMonthlyLimitExceeded(User $user): bool
    {
        return $this->getMonthlyUsage($user) >= $user->ai_monthly_limit;
    }

    /**
     * Record token usage
     */
    public function recordUsage(User $user, int $tokens): void
    {
        // Update cache
        Cache::increment($this->dailyCacheKey.$user->id, $tokens);
        Cache::increment($this->monthlyCacheKey.$user->id, $tokens);

        // Update database
        DB::transaction(function () use ($user, $tokens) {
            $user->increment('ai_daily_usage', $tokens);
            $user->increment('ai_monthly_usage', $tokens);
            $user->increment('ai_total_usage', $tokens);
        });
    }

    /**
     * Get current daily usage
     */
    protected function getDailyUsage(User $user): int
    {
        return Cache::remember($this->dailyCacheKey.$user->id, now()->endOfDay(), function() use ($user) {
            return $user->ai_daily_usage;
        });
    }

    /**
     * Get current monthly usage
     */
    protected function getMonthlyUsage(User $user): int
    {
        return Cache::remember($this->monthlyCacheKey.$user->id, now()->endOfMonth(), function() use ($user) {
            return $user->ai_monthly_usage;
        });
    }
}