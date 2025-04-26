<?php

namespace App\Http\Controllers;

use App\Models\ContentUserView;
use App\Models\NotificationArchive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationAnalyticsController extends Controller
{
    public function getStats(Request $request)
    {
        $timeRange = $request->input('time_range', '7d');
        $userId = $request->input('user_id');
        $contentType = $request->input('content_type');

        $query = NotificationArchive::query()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN read_at IS NOT NULL THEN 1 ELSE 0 END) as read_count'),
                DB::raw('notification_type')
            )
            ->groupBy('date', 'notification_type')
            ->orderBy('date');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($contentType) {
            $query->where('notification_type', $contentType);
        }

        // Apply time range filter
        $this->applyTimeRange($query, $timeRange);

        $stats = $query->get();

        return response()->json([
            'stats' => $stats,
            'engagement_rate' => $this->calculateEngagementRate($stats)
        ]);
    }

    private function applyTimeRange($query, $timeRange)
    {
        $days = (int) substr($timeRange, 0, -1);
        $query->where('created_at', '>=', now()->subDays($days));
    }

    private function calculateEngagementRate($stats)
    {
        if ($stats->isEmpty()) {
            return 0;
        }

        $total = $stats->sum('total');
        $read = $stats->sum('read_count');

        return round(($read / $total) * 100, 2);
    }
}