<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Content;
use App\Models\ContentUserView;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContentAnalyticsDashboard extends Component
{
    public $timeRange = '7days'; // 7days, 30days, 90days
    public $contentId;
    public $stats = [];
    public $loading = false;

    protected $queryString = [
        'timeRange' => ['except' => '7days']
    ];

    public function mount($contentId = null)
    {
        $this->contentId = $contentId;
    }

    public function loadAnalytics()
    {
        $this->loading = true;
        
        $endDate = Carbon::now();
        $startDate = match($this->timeRange) {
            '30days' => $endDate->copy()->subDays(30),
            '90days' => $endDate->copy()->subDays(90),
            default => $endDate->copy()->subDays(7),
        };

        $query = ContentUserView::query()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as views')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date');

        if ($this->contentId) {
            $query->where('content_id', $this->contentId);
        }

        $this->stats = $query->get();

        $this->loading = false;
    }

    public function render()
    {
        $topContents = Content::withCount('views')
            ->orderBy('views_count', 'desc')
            ->limit(5)
            ->get();

        return view('livewire.content-analytics-dashboard', [
            'topContents' => $topContents,
            'timeRanges' => [
                '7days' => 'Last 7 Days',
                '30days' => 'Last 30 Days', 
                '90days' => 'Last 90 Days'
            ]
        ]);
    }
}