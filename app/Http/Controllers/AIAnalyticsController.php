<?php

namespace App\Http\Controllers;

use App\Services\OpenAIService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AIAnalyticsController extends Controller
{
    protected OpenAIService $aiService;

    public function __construct(OpenAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $usageStats = $this->aiService->getUsageStats();

        $analytics = [
            'total_usage' => $usageStats['total_usage'] ?? 0,
            'last_used' => $usageStats['last_used'] ?? 'Never',
            'usage_by_type' => [
                'generate' => $user->ai_usage_generate_count ?? 0,
                'moderate' => $user->ai_usage_moderate_count ?? 0,
                'suggest' => $user->ai_usage_suggest_count ?? 0,
                'diff' => $user->ai_usage_diff_count ?? 0
            ],
            'rate_limits' => [
                'max_attempts' => 5,
                'decay_seconds' => 60
            ]
        ];

        return view('analytics.dashboard', compact('analytics'));
    }
}