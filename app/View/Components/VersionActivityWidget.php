<?php

namespace App\View\Components;

use App\Models\ContentVersionHistory;
use Illuminate\View\Component;

class VersionActivityWidget extends Component
{
    public $recentActivity;
    public $mostActiveContent;

    public function __construct()
    {
        $this->recentActivity = ContentVersionHistory::with(['version.content', 'user'])
            ->latest()
            ->take(5)
            ->get();

        $this->mostActiveContent = ContentVersionHistory::selectRaw('content_id, count(*) as count')
            ->groupBy('content_id')
            ->orderByDesc('count')
            ->with('version.content')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('components.version-activity-widget');
    }
}