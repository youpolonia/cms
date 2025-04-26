<?php

namespace App\Http\Controllers\Api\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AiUsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiUsageController extends Controller
{
    public function __construct(
        private AiUsageService $usageService
    ) {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get AI usage statistics for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'period' => 'sometimes|in:7d,30d'
        ]);

        $stats = $this->usageService->getUserStats(
            $request->user(),
            $request->input('period', '30d')
        );

        return response()->json([
            'data' => $stats,
            'total_tokens' => $request->user()->ai_usage_count,
            'estimated_cost' => $this->usageService->calculateCost($request->user()->ai_usage_count)
        ]);
    }

    /**
     * Track AI usage (called from other services)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string|max:50',
            'tokens_used' => 'required|integer|min:1'
        ]);

        $this->usageService->trackUsage(
            $request->user(),
            $request->endpoint,
            $request->tokens_used
        );

        return response()->json([
            'message' => 'Usage tracked successfully'
        ], 201);
    }
}
