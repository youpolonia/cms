<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AiUsageService;
use Illuminate\Support\Facades\Auth;

class AiUsageController extends Controller
{
    protected $aiUsageService;

    public function __construct(AiUsageService $aiUsageService)
    {
        $this->aiUsageService = $aiUsageService;
    }

    /**
     * Get current usage stats for authenticated user
     */
    public function getUsage(Request $request)
    {
        $user = $request->user();
        $usage = $this->aiUsageService->getUserUsage($user);

        return response()->json([
            'daily' => $usage['daily'],
            'daily_limit' => $usage['daily_limit'],
            'daily_remaining' => $usage['daily_remaining'],
            'monthly' => $usage['monthly'],
            'monthly_limit' => $usage['monthly_limit'],
            'monthly_remaining' => $usage['monthly_remaining'],
        ]);
    }

    /**
     * Check if user can perform AI operation
     */
    public function checkUsage(Request $request)
    {
        $user = $request->user();
        $canUse = $this->aiUsageService->canUseAi($user);

        if (!$canUse) {
            return response()->json([
                'can_use' => false,
                'message' => 'You have reached your AI usage limit for ' . 
                    ($this->aiUsageService->isDailyLimitExceeded($user) ? 'today' : 'this month')
            ], 403);
        }

        return response()->json(['can_use' => true]);
    }
}