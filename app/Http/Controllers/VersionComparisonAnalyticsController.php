<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Services\VersionComparisonAnalyticsService;
use Illuminate\Http\JsonResponse;

class VersionComparisonAnalyticsController extends Controller
{
    public function __construct(
        protected VersionComparisonAnalyticsService $analyticsService
    ) {}

    public function getContentStats(int $contentId): JsonResponse
    {
        $content = Content::findOrFail($contentId);

        return response()->json([
            'data' => [
                'total_comparisons' => $this->analyticsService->getComparisonCount($contentId),
                'frequent_comparisons' => $this->analyticsService->getFrequentComparisons($contentId),
                'average_similarity' => $this->analyticsService->getAverageSimilarity($contentId),
                'change_distribution' => $this->analyticsService->getChangeDistribution($contentId),
            ]
        ]);
    }

    public function getSystemStats(): JsonResponse
    {
        return response()->json([
            'data' => $this->analyticsService->getSystemStats()
        ]);
    }

    public function getComparisonStats(int $versionAId, int $versionBId): JsonResponse
    {
        $stats = $this->analyticsService->getComparisonStats($versionAId, $versionBId);

        return response()->json([
            'data' => $stats
        ]);
    }
}
