<?php

namespace App\Http\Controllers;

use App\Models\ThemeVersionRollback;
use App\Services\RollbackAnalyticsService;
use Illuminate\Http\JsonResponse;

class RollbackAnalyticsController extends Controller
{
    public function __construct(
        private RollbackAnalyticsService $analyticsService
    ) {}

    public function show(ThemeVersionRollback $rollback): JsonResponse
    {
        return response()->json([
            'data' => $this->analyticsService->getRollbackStats($rollback)
        ]);
    }

    public function recent(): JsonResponse
    {
        return response()->json([
            'data' => $this->analyticsService->getRecentRollbacks()
        ]);
    }

    public function successRate(int $themeId): JsonResponse
    {
        return response()->json([
            'data' => [
                'success_rate' => $this->analyticsService->getSuccessRate($themeId)
            ]
        ]);
    }

    public function reasons(int $themeId): JsonResponse
    {
        return response()->json([
            'data' => $this->analyticsService->getRollbackReasons($themeId)
        ]);
    }

    public function impact(int $themeId): JsonResponse
    {
        return response()->json([
            'data' => $this->analyticsService->getImpactAnalysis($themeId)
        ]);
    }

    public function userBehavior(int $themeId): JsonResponse
    {
        return response()->json([
            'data' => $this->analyticsService->getUserBehaviorPatterns($themeId)
        ]);
    }

    public function notificationPreferences(int $themeId): JsonResponse
    {
        return response()->json([
            'data' => $this->analyticsService->getNotificationPreferences($themeId)
        ]);
    }
}
