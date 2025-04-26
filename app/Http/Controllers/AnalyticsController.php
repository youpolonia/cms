<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function createExport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parameters' => 'required|array',
            'format' => 'required|string|in:csv,json,xlsx'
        ]);

        $result = $this->analyticsService->createExport($validated);
        
        return response()->json($result);
    }

    public function getVersionStats(int $versionId): JsonResponse
    {
        $stats = $this->analyticsService->getVersionViewStats($versionId);
        return response()->json(['data' => $stats]);
    }

    public function compareVersions(int $version1Id, int $version2Id): JsonResponse
    {
        $comparison = $this->analyticsService->compareVersions($version1Id, $version2Id);
        return response()->json(['data' => $comparison]);
    }

    public function getRecentExports(): JsonResponse
    {
        $exports = $this->analyticsService->getRecentExports();
        return response()->json(['data' => $exports]);
    }

    public function getContentAnalytics(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'range' => 'required|string|in:7d,30d,90d'
        ]);

        $data = $this->analyticsService->getContentAnalytics($validated['range']);
        
        return response()->json([
            'metrics' => $data['metrics'],
            'viewsData' => $data['viewsData'],
            'topContentData' => $data['topContentData']
        ]);
    }
}
