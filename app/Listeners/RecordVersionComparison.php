<?php

namespace App\Listeners;

use App\Events\ContentVersionCompared;
use App\Models\ContentComparisonAnalytics;

class RecordVersionComparison
{
    public function handle(ContentVersionCompared $event)
    {
        ContentComparisonAnalytics::create([
            'content_id' => $event->contentId,
            'version1_id' => $event->version1Id,
            'version2_id' => $event->version2Id,
            'granularity' => $event->granularity,
            'user_id' => $event->userId,
            'compared_at' => now()
        ]);
    }
}