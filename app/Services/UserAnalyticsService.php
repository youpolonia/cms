<?php

namespace App\Services;

use App\Contracts\UserAnalyticsServiceInterface;
use App\Models\User;
use App\Models\AnalyticsExport;
use App\Services\ExportTagService;

class UserAnalyticsService implements UserAnalyticsServiceInterface
{
    protected $exportTagService;

    public function __construct(ExportTagService $exportTagService)
    {
        $this->exportTagService = $exportTagService;
    }

    public function trackUserActivity(int $userId, string $activityType): void
    {
        User::find($userId)
            ->analytics()
            ->create(['activity_type' => $activityType]);
    }

    public function getUserAnalytics(int $userId): array
    {
        return User::find($userId)
            ->analytics()
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function exportUserAnalytics(int $userId): string
    {
        $analytics = $this->getUserAnalytics($userId);
        $export = AnalyticsExport::create([
            'user_id' => $userId,
            'export_type' => 'user_analytics'
        ]);
        
        $this->exportTagService->tagExport($export->id, 'user_analytics');
        
        return $export->id;
    }
}