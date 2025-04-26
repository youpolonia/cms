<?php

namespace App\Http\Livewire;

use App\Models\AnalyticsData;

class UsageStatsWidget extends AnalyticsWidget
{
    protected function fetchData(): array
    {
        $data = parent::fetchData();
        
        $stats = AnalyticsData::query()
            ->selectRaw('SUM(views) as views, SUM(sessions) as sessions, AVG(duration) as duration')
            ->whereBetween('created_at', [now()->subDay(), now()])
            ->first();

        foreach ($data['metrics'] as &$metric) {
            $metric['value'] = $stats->{$metric['key']} ?? 0;
            if ($metric['key'] === 'duration') {
                $metric['value'] = round($metric['value'], 1);
            }
        }

        return array_merge($data, [
            'chart_data' => [
                'labels' => array_column($data['metrics'], 'label'),
                'values' => array_column($data['metrics'], 'value')
            ]
        ]);
    }

    public function render()
    {
        return view('livewire.usage-stats-widget', [
            'chartType' => $this->config['chart_type'] ?? 'line'
        ]);
    }
}