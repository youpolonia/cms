<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\AIUsageThresholdAlert;
use Symfony\Component\HttpFoundation\Response;

class TrackAIUsage
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->isSuccessful() && $request->user()) {
            $user = $request->user();
            $routeName = $request->route()->getName();
            
            // Only track AI-related routes
            if (str_starts_with($routeName, 'ai.')) {
                $this->trackRequest($user);
            }
        }

        return $response;
    }

    protected function trackRequest(User $user)
    {
        // Default to 1 token for simple tracking
        $tokens = 1;

        $user->increment('ai_usage_count', $tokens);
        $user->increment('ai_monthly_usage', $tokens);

        $thresholds = config('ai.thresholds');
        $currentUsage = $user->ai_monthly_usage;

        foreach ($thresholds as $limit => $type) {
            if ($currentUsage >= $limit && $currentUsage - $tokens < $limit) {
                $user->notify(new AIUsageThresholdAlert($type, $currentUsage));
            }
        }
    }
}