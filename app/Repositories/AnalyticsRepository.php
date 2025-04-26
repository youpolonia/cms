<?php

namespace App\Repositories;

use App\Models\AnalyticsExport;
use App\Models\ContentVersionView;
use Illuminate\Support\Facades\DB;

class AnalyticsRepository implements AnalyticsRepositoryInterface
{
    public function createExport(array $data): AnalyticsExport
    {
        return AnalyticsExport::create($data);
    }

    public function getViewStats(int $versionId): array
    {
        return ContentVersionView::where('content_version_id', $versionId)
            ->select([
                DB::raw('COUNT(*) as total_views'),
                DB::raw('COUNT(DISTINCT user_id) as unique_visitors'),
                DB::raw('AVG(time_spent) as avg_time_spent')
            ])
            ->first()
            ->toArray();
    }

    public function getComparisonStats(int $version1Id, int $version2Id): array
    {
        $stats1 = $this->getViewStats($version1Id);
        $stats2 = $this->getViewStats($version2Id);

        return [
            'version1' => $stats1,
            'version2' => $stats2,
            'comparison' => [
                'views_diff' => $stats1['total_views'] - $stats2['total_views'],
                'visitors_diff' => $stats1['unique_visitors'] - $stats2['unique_visitors']
            ]
        ];
    }

    public function getRecentExports(int $limit = 5): \Illuminate\Support\Collection
    {
        return AnalyticsExport::latest()
            ->limit($limit)
            ->get();
    }

    public function getContentMetrics(string $range): array
    {
        $days = (int) rtrim($range, 'd');
        $date = now()->subDays($days);

        return DB::table('content_views')
            ->select([
                DB::raw('COUNT(*) as total_views'),
                DB::raw('AVG(time_spent) as avg_time'),
                DB::raw('SUM(CASE WHEN time_spent < 10 THEN 1 ELSE 0 END) / COUNT(*) * 100 as bounce_rate'),
                DB::raw('COUNT(DISTINCT content_id) as content_count')
            ])
            ->where('created_at', '>=', $date)
            ->first()
            ->toArray();
    }

    public function getViewsTrendData(string $range): array
    {
        $days = (int) rtrim($range, 'd');
        $date = now()->subDays($days);

        return DB::table('content_views')
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as views')
            ])
            ->where('created_at', '>=', $date)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    public function getTopContent(string $range): array
    {
        $days = (int) rtrim($range, 'd');
        $date = now()->subDays($days);

        return DB::table('content_views')
            ->join('contents', 'content_views.content_id', '=', 'contents.id')
            ->select([
                'contents.id',
                'contents.title',
                DB::raw('COUNT(*) as views'),
                DB::raw('AVG(time_spent) as avg_time')
            ])
            ->where('content_views.created_at', '>=', $date)
            ->groupBy('contents.id', 'contents.title')
            ->orderByDesc('views')
            ->limit(10)
            ->get()
            ->toArray();
    }
}