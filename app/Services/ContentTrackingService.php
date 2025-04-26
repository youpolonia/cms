<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentUserView;
use Carbon\Carbon;

class ContentTrackingService
{
    public function trackView(int $contentId, int $userId): void
    {
        // Record individual view
        ContentUserView::create([
            'content_id' => $contentId,
            'user_id' => $userId,
            'viewed_at' => now()
        ]);

        // Update content stats
        $content = Content::find($contentId);
        if ($content) {
            $content->increment('views');
            $content->update([
                'last_viewed_at' => now(),
                'engagement_score' => $this->calculateEngagementScore($content)
            ]);
        }
    }

    protected function calculateEngagementScore(Content $content): float
    {
        $viewsLastWeek = ContentUserView::where('content_id', $content->id)
            ->where('viewed_at', '>', Carbon::now()->subWeek())
            ->count();

        $totalViews = $content->views;

        // Simple engagement formula - can be enhanced later
        return min(100, ($viewsLastWeek / max(1, $totalViews)) * 100);
    }

    public function getViewStats(Content $content): array
    {
        return [
            'total_views' => $content->views,
            'views_last_week' => ContentUserView::where('content_id', $content->id)
                ->where('viewed_at', '>', Carbon::now()->subWeek())
                ->count(),
            'engagement_score' => $content->engagement_score,
            'last_viewed' => $content->last_viewed_at?->diffForHumans()
        ];
    }
}