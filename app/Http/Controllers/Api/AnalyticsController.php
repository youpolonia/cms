<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContentVersionComparison;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function versionComparisons(Request $request)
    {
        $range = $request->input('range', '30d');
        $endDate = Carbon::now();
        $startDate = $this->getStartDate($range, $endDate);

        // Get comparison stats
        $totalComparisons = ContentVersionComparison::whereBetween('created_at', [$startDate, $endDate])->count();
        $previousPeriodCount = ContentVersionComparison::whereBetween('created_at', [
            $startDate->copy()->subDays($this->getDaysForRange($range)),
            $startDate
        ])->count();

        $comparisonTrend = $previousPeriodCount > 0 
            ? round(($totalComparisons - $previousPeriodCount) / $previousPeriodCount * 100)
            : 0;

        // Get rollback stats
        $rollbacks = ContentVersionComparison::whereBetween('created_at', [$startDate, $endDate])
            ->where('was_rolled_back', true)
            ->count();

        $rollbackRate = $totalComparisons > 0 ? round($rollbacks / $totalComparisons * 100) : 0;

        // Get activity data
        $activityData = $this->getActivityData($startDate, $endDate);

        // Get change type distribution
        $changeTypeData = $this->getChangeTypeData($startDate, $endDate);

        // Get recent activity
        $recentActivity = ContentVersionComparison::with(['content', 'fromVersion', 'toVersion'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($comparison) {
                return [
                    'contentTitle' => $comparison->content->title,
                    'fromVersion' => $comparison->fromVersion->version_number,
                    'toVersion' => $comparison->toVersion->version_number,
                    'changes' => $comparison->changes_count,
                    'date' => $comparison->created_at
                ];
            });

        return response()->json([
            'stats' => [
                'totalComparisons' => $totalComparisons,
                'comparisonTrend' => $comparisonTrend,
                'avgChanges' => $this->getAverageChanges($startDate, $endDate),
                'rollbackRate' => $rollbackRate,
                'rollbackTrend' => $this->getRollbackTrend($startDate, $endDate, $range)
            ],
            'activity' => $activityData,
            'changeTypes' => $changeTypeData,
            'recentActivity' => $recentActivity
        ]);
    }

    private function getStartDate($range, $endDate)
    {
        switch ($range) {
            case '7d':
                return $endDate->copy()->subDays(7);
            case '30d':
                return $endDate->copy()->subDays(30);
            case '90d':
                return $endDate->copy()->subDays(90);
            case 'all':
                return Carbon::createFromTimestamp(0);
            default:
                return $endDate->copy()->subDays(30);
        }
    }

    private function getDaysForRange($range)
    {
        switch ($range) {
            case '7d': return 7;
            case '30d': return 30;
            case '90d': return 90;
            default: return 30;
        }
    }

    private function getActivityData($startDate, $endDate)
    {
        $activity = ContentVersionComparison::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate);

        $data = [];
        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $count = $activity->firstWhere('date', $dateString)->count ?? 0;
            $data[] = [
                'label' => $date->format('M j'),
                'value' => $count
            ];
        }

        return $data;
    }

    private function getChangeTypeData($startDate, $endDate)
    {
        $changes = ContentVersionComparison::selectRaw('change_type, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('change_type')
            ->get();

        return $changes->map(function ($item) {
            return [
                'label' => ucfirst(str_replace('_', ' ', $item->change_type)),
                'value' => $item->count
            ];
        });
    }

    private function getAverageChanges($startDate, $endDate)
    {
        return ContentVersionComparison::whereBetween('created_at', [$startDate, $endDate])
            ->avg('changes_count') ?? 0;
    }

    private function getRollbackTrend($startDate, $endDate, $range)
    {
        $currentRollbacks = ContentVersionComparison::whereBetween('created_at', [$startDate, $endDate])
            ->where('was_rolled_back', true)
            ->count();

        $previousRollbacks = ContentVersionComparison::whereBetween('created_at', [
            $startDate->copy()->subDays($this->getDaysForRange($range)),
            $startDate
        ])
        ->where('was_rolled_back', true)
        ->count();

        return $previousRollbacks > 0 
            ? round(($currentRollbacks - $previousRollbacks) / $previousRollbacks * 100)
            : 0;
    }
}