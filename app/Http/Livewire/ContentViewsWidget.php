<?php

namespace App\Http\Livewire;

use App\Models\ContentUserView;

class ContentViewsWidget extends AnalyticsWidget
{
    protected function fetchData(): array
    {
        $data = parent::fetchData();
        
        $views = ContentUserView::query()
            ->selectRaw('content_type, COUNT(*) as views, COUNT(DISTINCT user_id) as unique_views')
            ->whereBetween('created_at', [now()->subWeek(), now()])
            ->groupBy('content_type')
            ->get();

        foreach ($data['metrics'] as &$metric) {
            if ($metric['key'] === 'content_type') {
                $metric['value'] = $views->pluck('content_type')->implode(', ');
            } else {
                $metric['value'] = $views->sum($metric['key']);
            }
        }

        return array_merge($data, [
            'chart_data' => [
                'labels' => $views->pluck('content_type')->toArray(),
                'values' => $views->pluck('views')->toArray()
            ]
        ]);
    }

    public function render()
    {
        return view('livewire.content-views-widget', [
            'chartType' => $this->config['chart_type'] ?? 'bar'
        ]);
    }
}