<?php

namespace App\Services;

use App\Models\ThemeVersionRollback;
use Illuminate\Support\Collection;

class RollbackAnalyticsService
{
    public function getRollbackStats(ThemeVersionRollback $rollback): array
    {
        return [
            'id' => $rollback->id,
            'status' => $rollback->status,
            'completed_at' => $rollback->completed_at,
            'started_at' => $rollback->started_at,
            'duration' => $rollback->completed_at 
                ? $rollback->started_at->diffInSeconds($rollback->completed_at)
                : null,
            'version' => [
                'id' => $rollback->version->id,
                'name' => $rollback->version->name,
            ],
            'rollback_to_version' => [
                'id' => $rollback->rollbackToVersion->id,
                'name' => $rollback->rollbackToVersion->name,
            ],
            'error_message' => $rollback->error_message,
            'file_count' => $rollback->file_count,
            'file_size_kb' => $rollback->file_size_kb,
            'reason' => $rollback->reason,
            'performance_impact' => $rollback->performance_impact,
            'stability_impact' => $rollback->stability_impact,
            'notification_preferences' => $rollback->notification_preferences,
            'user_behavior_metrics' => $rollback->user_behavior_metrics,
            'system_metrics' => $rollback->system_metrics,
        ];
    }

    public function getRollbackReasons(int $themeId): array
    {
        return ThemeVersionRollback::query()
            ->whereHas('version', fn($q) => $q->where('theme_id', $themeId))
            ->select('reason')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('reason')
            ->orderByDesc('count')
            ->get()
            ->toArray();
    }

    public function getImpactAnalysis(int $themeId): array
    {
        return [
            'performance' => ThemeVersionRollback::query()
                ->whereHas('version', fn($q) => $q->where('theme_id', $themeId))
                ->avg('performance_impact->score') ?? 0,
            'stability' => ThemeVersionRollback::query()
                ->whereHas('version', fn($q) => $q->where('theme_id', $themeId))
                ->avg('stability_impact->score') ?? 0
        ];
    }

    public function getUserBehaviorPatterns(int $themeId): array
    {
        return ThemeVersionRollback::query()
            ->whereHas('version', fn($q) => $q->where('theme_id', $themeId))
            ->select('user_behavior_metrics')
            ->get()
            ->pluck('user_behavior_metrics')
            ->toArray();
    }

    public function getNotificationPreferences(int $themeId): array
    {
        return ThemeVersionRollback::query()
            ->whereHas('version', fn($q) => $q->where('theme_id', $themeId))
            ->select('notification_preferences')
            ->get()
            ->pluck('notification_preferences')
            ->toArray();
    }

    public function getRecentRollbacks(int $limit = 10): Collection
    {
        return ThemeVersionRollback::query()
            ->with(['version', 'rollbackToVersion'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($rollback) {
                return [
                    'id' => $rollback->id,
                    'status' => $rollback->status,
                    'created_at' => $rollback->created_at,
                    'version_name' => $rollback->version->name,
                    'rollback_to_version_name' => $rollback->rollbackToVersion->name,
                ];
            });
    }

    public function getSuccessRate(int $themeId): float
    {
        $total = ThemeVersionRollback::query()
            ->whereHas('version', fn($q) => $q->where('theme_id', $themeId))
            ->count();

        if ($total === 0) {
            return 0;
        }

        $successful = ThemeVersionRollback::query()
            ->whereHas('version', fn($q) => $q->where('theme_id', $themeId))
            ->completed()
            ->count();

        return round(($successful / $total) * 100, 2);
    }
}
