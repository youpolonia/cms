<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\ContentVersion;
use App\Models\ModerationQueue;
use App\Models\AnalyticsExport;

class ActivityWidget extends Component
{
    public $recentActivities;

    public function __construct($limit = 5)
    {
        $contentEdits = ContentVersion::latest()
            ->take($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'edit',
                    'title' => $item->content->title,
                    'user' => $item->user->name,
                    'time' => $item->created_at,
                    'icon' => 'pencil'
                ];
            });

        $moderationActions = ModerationQueue::latest()
            ->take($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'moderation',
                    'title' => $item->content->title,
                    'user' => $item->moderator->name ?? 'System',
                    'time' => $item->updated_at,
                    'icon' => 'shield-check',
                    'status' => $item->status
                ];
            });

        $exports = AnalyticsExport::latest()
            ->take($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'export',
                    'title' => 'Analytics Export',
                    'user' => $item->user->name,
                    'time' => $item->created_at,
                    'icon' => 'download'
                ];
            });

        $this->recentActivities = $contentEdits
            ->merge($moderationActions)
            ->merge($exports)
            ->sortByDesc('time')
            ->take($limit);
    }

    public function render()
    {
        return view('components.activity-widget');
    }
}
