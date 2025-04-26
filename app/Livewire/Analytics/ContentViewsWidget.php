<?php

namespace App\Livewire\Analytics;

use App\Models\ContentUserView;
use Livewire\Component;
use Livewire\Attributes\Computed;

class ContentViewsWidget extends Component
{
    public string $timeRange = '7d';

    #[Computed]
    public function viewsData()
    {
        return match($this->timeRange) {
            '24h' => $this->getViewsLast24Hours(),
            '7d' => $this->getViewsLast7Days(),
            '30d' => $this->getViewsLast30Days(),
            default => $this->getViewsLast7Days()
        };
    }

    protected function getViewsLast24Hours()
    {
        return ContentUserView::query()
            ->selectRaw('DATE_FORMAT(created_at, "%H:00") as hour, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDay())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour');
    }

    protected function getViewsLast7Days()
    {
        return ContentUserView::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');
    }

    protected function getViewsLast30Days()
    {
        return ContentUserView::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');
    }

    public function render()
    {
        return view('livewire.analytics.content-views-widget');
    }
}