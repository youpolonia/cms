<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyticsRequest;
use App\Services\ContentApprovalAnalyticsService;
use Illuminate\Http\Request;

class ContentApprovalAnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(ContentApprovalAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display approval analytics dashboard with cached data
     * 
     * Checks MCP cache first, falls back to Redis cache and service call
     * Caches results in MCP for future requests
     */
    public function index(AnalyticsRequest $request)
    {
        $timeframe = $request->input('timeframe', 'month');
        $filters = $request->validated();
        $cacheKey = "approval_analytics:{$timeframe}:" . md5(json_encode($filters));

        // First try to get from cache
        $cachedData = $this->getFromCache($cacheKey);
        if ($cachedData) {
            return view('content.approval-analytics', $cachedData);
        }

        // Fall back to service call
        $data = [
            'completionRates' => $this->analyticsService->getCompletionRates($timeframe, $filters),
            'approvalTimes' => $this->analyticsService->getApprovalTimes($timeframe, $filters),
            'rejectionReasons' => $this->analyticsService->getRejectionReasons($timeframe, $filters),
            'statsSummary' => $this->analyticsService->getStatsSummary($timeframe, $filters),
            'timelineData' => $this->analyticsService->getTimelineData($timeframe, $filters),
            'timeframe' => $timeframe
        ];

        // Store in cache with 1 hour TTL
        $this->storeInCache($cacheKey, $data, 3600);

        return view('content.approval-analytics', $data);
    }

    /**
     * Get stats summary with caching
     */
    public function statsSummary(AnalyticsRequest $request)
    {
        $timeframe = $request->input('timeframe', 'month');
        $filters = $request->validated();
        $cacheKey = "stats_summary:{$timeframe}:" . md5(json_encode($filters));

        // First try to get from cache
        $cachedData = $this->getFromCache($cacheKey);
        if ($cachedData) {
            return response()->json($cachedData);
        }

        // Fall back to service call
        $data = $this->analyticsService->getStatsSummary($timeframe, $filters);

        // Store in cache with 1 hour TTL
        $this->storeInCache($cacheKey, $data, 3600);

        return response()->json($data);
    }

    /**
     * Get timeline data with caching
     */
    public function timeline(AnalyticsRequest $request)
    {
        $timeframe = $request->input('timeframe', 'month');
        $filters = $request->validated();
        $cacheKey = "timeline_data:{$timeframe}:" . md5(json_encode($filters));

        // First try to get from cache
        $cachedData = $this->getFromCache($cacheKey);
        if ($cachedData) {
            return response()->json($cachedData);
        }

        // Fall back to service call
        $data = $this->analyticsService->getTimelineData($timeframe, $filters);

        // Store in cache with 1 hour TTL
        $this->storeInCache($cacheKey, $data, 3600);

        return response()->json($data);
    }

    /**
     * Export analytics data with caching
     */
    public function export(AnalyticsRequest $request, string $format)
    {
        $timeframe = $request->input('timeframe', 'month');
        $filters = $request->validated();
        $cacheKey = "export_data:{$timeframe}:{$format}:" . md5(json_encode($filters));

        // First try to get from cache
        $cachedData = $this->getFromCache($cacheKey);
        if ($cachedData) {
            return response()->make($cachedData['content'], 200, $cachedData['headers']);
        }

        // Fall back to service call
        $data = [
            'completion_rates' => $this->analyticsService->getCompletionRates($timeframe, $filters),
            'approval_times' => $this->analyticsService->getApprovalTimes($timeframe, $filters),
            'rejection_reasons' => $this->analyticsService->getRejectionReasons($timeframe, $filters),
            'stats_summary' => $this->analyticsService->getStatsSummary($timeframe, $filters),
            'timeline_data' => $this->analyticsService->getTimelineData($timeframe, $filters)
        ];

        $export = $this->analyticsService->exportData($data, $format);

        // Store in cache (only for successful exports)
        if ($export) {
            $this->storeInCache($cacheKey, [
                'content' => $export->getContent(),
                'headers' => $export->headers->all()
            ], 3600);
        }

        return $export;
    }

    /**
     * Get data from Laravel cache
     */
    protected function getFromCache(string $key)
    {
        return \Cache::get($key);
    }

    /**
     * Store data in Laravel cache
     */
    protected function storeInCache(string $key, $data, $ttl = 3600)
    {
        \Cache::put($key, $data, $ttl);
    }
}
