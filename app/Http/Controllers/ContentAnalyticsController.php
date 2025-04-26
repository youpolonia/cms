<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Content;
use App\Models\ContentUserView;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContentAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $timeRange = $request->input('time_range', '7d');
        $chartType = $request->input('chart_type', 'views');
        $groupBy = $request->input('group_by', 'day');
        
        $query = ContentUserView::query();
        
        // Apply time range filter
        $this->applyTimeRange($query, $timeRange, $request);
        
        // Get aggregated stats
        $stats = $this->getAggregatedStats($query);
        
        // Get content stats
        $contentStats = $this->getContentStats($query);
        
        // Get chart data
        $chartData = $this->getChartData($query, $chartType, $groupBy);
        
        return response()->json([
            'stats' => $stats,
            'contents' => $contentStats,
            'chart' => $chartData
        ]);
    }
    
    public function export(Request $request)
    {
        // TODO: Implement export functionality
        return response()->json(['message' => 'Export functionality coming soon']);
    }
    
    private function applyTimeRange($query, $timeRange, $request)
    {
        $now = Carbon::now();
        
        switch ($timeRange) {
            case '7d':
                $query->where('created_at', '>=', $now->subDays(7));
                break;
            case '30d':
                $query->where('created_at', '>=', $now->subDays(30));
                break;
            case '90d':
                $query->where('created_at', '>=', $now->subDays(90));
                break;
            case 'custom':
                $startDate = Carbon::parse($request->input('start_date'));
                $endDate = Carbon::parse($request->input('end_date'));
                $query->whereBetween('created_at', [$startDate, $endDate]);
                break;
        }
    }
    
    private function getAggregatedStats($query)
    {
        $statsQuery = clone $query;
        
        return [
            'totalViews' => $statsQuery->count(),
            'uniqueVisitors' => $statsQuery->distinct('user_id')->count('user_id'),
            'avgTimeOnPage' => $statsQuery->avg('time_spent') ?? 0,
            'avgScrollDepth' => $statsQuery->avg('scroll_depth') ?? 0,
            'bounceRate' => $statsQuery->where('time_spent', '<', 5)->count() / max(1, $statsQuery->count()) * 100,
            'conversionRate' => $statsQuery->where('converted', true)->count() / max(1, $statsQuery->count()) * 100
        ];
    }
    
    private function getContentStats($query)
    {
        $contentQuery = clone $query;
        
        return $contentQuery
            ->select([
                'content_id',
                DB::raw('MAX(contents.title) as title'),
                DB::raw('COUNT(*) as views'),
                DB::raw('COUNT(DISTINCT user_id) as unique_visitors'),
                DB::raw('AVG(time_spent) as avg_time'),
                DB::raw('AVG(scroll_depth) as avg_scroll_depth'),
                DB::raw('SUM(CASE WHEN time_spent < 5 THEN 1 ELSE 0 END) / COUNT(*) * 100 as bounce_rate'),
                DB::raw('SUM(CASE WHEN converted = 1 THEN 1 ELSE 0 END) as conversions')
            ])
            ->join('contents', 'contents.id', '=', 'content_user_views.content_id')
            ->groupBy('content_id')
            ->orderByDesc('views')
            ->limit(10)
            ->get();
    }
    
    private function getChartData($query, $chartType, $groupBy)
    {
        $chartQuery = clone $query;
        
        $select = match($groupBy) {
            'day' => DB::raw('DATE(created_at) as date'),
            'week' => DB::raw('DATE_FORMAT(created_at, "%Y-%u") as date'),
            'month' => DB::raw('DATE_FORMAT(created_at, "%Y-%m") as date'),
        };
        
        $metric = match($chartType) {
            'views' => DB::raw('COUNT(*) as value'),
            'unique_visitors' => DB::raw('COUNT(DISTINCT user_id) as value'),
            'time' => DB::raw('AVG(time_spent) as value'),
            'scroll' => DB::raw('AVG(scroll_depth) as value'),
            'engagement' => DB::raw('AVG(time_spent * scroll_depth / 100) as value'),
        };
        
        return $chartQuery
            ->select([
                $select,
                $metric
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(fn($item) => [$item->date => $item->value]);
    }
}