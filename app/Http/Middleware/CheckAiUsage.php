<?php

namespace App\Http\Middleware;

use App\Services\AiUsageService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAiUsage
{
    protected AiUsageService $aiUsageService;

    public function __construct(AiUsageService $aiUsageService)
    {
        $this->aiUsageService = $aiUsageService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$this->aiUsageService->canUseAi($user)) {
            return response()->json([
                'error' => 'You have reached your AI usage limit for ' . 
                    ($this->aiUsageService->isDailyLimitExceeded($user) ? 'today' : 'this month')
            ], 429);
        }

        return $next($request);
    }
}