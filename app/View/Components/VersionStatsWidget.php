<?php

namespace App\View\Components;

use App\Models\ContentVersionHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

class VersionStatsWidget extends Component
{
    public $activityData;
    public $userContributions;
    public $contentTypeDistribution;

    public function __construct()
    {
        // Version activity over last 30 days
        $this->activityData = ContentVersionHistory::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top contributors
        $this->userContributions = ContentVersionHistory::with('user')
            ->select('user_id', DB::raw('count(*) as count'))
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        // Content type distribution
        $this->contentTypeDistribution = ContentVersionHistory::with('version.contentType')
            ->select('content_type_id', DB::raw('count(*) as count'))
            ->groupBy('content_type_id')
            ->get();
    }

    public function render()
    {
        return view('components.version-stats-widget');
    }
}