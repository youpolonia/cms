<?php

namespace App\Services;

use App\Models\ContentVersion;
use App\Models\ContentUserView;
use Illuminate\Support\Facades\DB;

class VersionAnalyticsService
{
    public function trackView(ContentVersion $version, $userId)
    {
        ContentUserView::updateOrCreate(
            [
                'content_version_id' => $version->id,
                'user_id' => $userId
            ],
            [
                'viewed_at' => now()
            ]
        );
    }

    public function getVersionStats(ContentVersion $version)
    {
        return [
            'view_count' => ContentUserView::where('content_version_id', $version->id)->count(),
            'unique_viewers' => ContentUserView::where('content_version_id', $version->id)
                ->distinct('user_id')
                ->count('user_id'),
            'first_viewed_at' => ContentUserView::where('content_version_id', $version->id)
                ->min('viewed_at'),
            'last_viewed_at' => ContentUserView::where('content_version_id', $version->id)
                ->max('viewed_at')
        ];
    }

    public function getComparisonStats($version1, $version2)
    {
        return [
            'version1' => $this->getVersionStats($version1),
            'version2' => $this->getVersionStats($version2),
            'view_difference' => abs(
                ContentUserView::where('content_version_id', $version1->id)->count() -
                ContentUserView::where('content_version_id', $version2->id)->count()
            )
        ];
    }
}