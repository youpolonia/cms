<?php

namespace App\Http\Controllers;

use App\Models\ThemeVersionComparisonStat;
use App\Models\ThemeVersion;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ThemeVersionComparisonAnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display comparison analytics dashboard
     */
    public function index()
    {
        $stats = ThemeVersionComparisonStat::with(['fromVersion', 'toVersion'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('themes.analytics.comparison', compact('stats'));
    }

    /**
     * Export comparison data
     */
    public function export(string $format): StreamedResponse
    {
        $filename = "theme-comparison-export-".now()->format('Y-m-d').".{$format}";
        $data = ThemeVersionComparisonStat::all();

        return $this->analyticsService->exportData($data, $filename, $format);
    }

    /**
     * Get comparison stats for API
     */
    public function getComparisonStats(Request $request)
    {
        $validated = $request->validate([
            'theme_id' => 'required|exists:themes,id',
            'from_version' => 'required|exists:theme_versions,id',
            'to_version' => 'required|exists:theme_versions,id'
        ]);

        $stats = ThemeVersionComparisonStat::where([
            'theme_id' => $validated['theme_id'],
            'from_version_id' => $validated['from_version'],
            'to_version_id' => $validated['to_version']
        ])->firstOrFail();

        return response()->json($stats);
    }

    /**
     * Get chart data for comparison
     */
    public function getChartData(Request $request)
    {
        $validated = $request->validate([
            'theme_id' => 'required|exists:themes,id',
            'metric' => 'required|in:file_count_diff,size_diff,quality_score_diff'
        ]);

        $data = ThemeVersionComparisonStat::where('theme_id', $validated['theme_id'])
            ->orderBy('created_at', 'asc')
            ->get(['created_at', $validated['metric']]);

        return response()->json($data);
    }
}
