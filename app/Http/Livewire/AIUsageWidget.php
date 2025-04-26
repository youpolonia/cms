<?php

namespace App\Http\Livewire;

use App\Models\User;

class AIUsageWidget extends AnalyticsWidget
{
    public bool $thresholdExceeded = false;

    protected function fetchData(): array
    {
        $data = parent::fetchData();
        
        $usage = User::query()
            ->selectRaw('SUM(ai_usage_count) as requests, COUNT(id) as users')
            ->where('ai_usage_count', '>', 0)
            ->first();

        $data['metrics'][0]['value'] = $usage->requests ?? 0; // requests
        $data['metrics'][1]['value'] = $usage->requests * 100; // tokens estimate
        $data['metrics'][2]['value'] = $usage->users ?? 0; // users

        $this->thresholdExceeded = $usage->requests > ($this->config['threshold'] ?? 1000);

        return array_merge($data, [
            'chart_data' => [
                'labels' => array_column($data['metrics'], 'label'),
                'values' => array_column($data['metrics'], 'value')
            ]
        ]);
    }

    public function render()
    {
        return view('livewire.ai-usage-widget', [
            'chartType' => $this->config['chart_type'] ?? 'pie',
            'threshold' => $this->config['threshold'] ?? 1000
        ]);
    }
}