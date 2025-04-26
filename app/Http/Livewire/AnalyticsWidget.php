<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;

class AnalyticsWidget extends Component
{
    public string $widgetType;
    public array $config = [];
    public array $data = [];
    public bool $loading = true;
    public int $refreshInterval;

    public function mount(string $widgetType)
    {
        $this->widgetType = $widgetType;
        $this->config = config("analytics.widgets.types.{$widgetType}", []);
        $this->refreshInterval = config('analytics.widgets.default_refresh_interval', 60);
    }

    public function loadData()
    {
        $this->loading = true;
        $this->data = Cache::remember(
            "analytics_widget_{$this->widgetType}",
            $this->refreshInterval,
            fn() => $this->fetchData()
        );
        $this->loading = false;
    }

    protected function fetchData(): array
    {
        return [
            'metrics' => array_map(fn($metric) => [
                'key' => $metric,
                'label' => config("analytics.metrics.{$metric}", $metric),
                'value' => 0 // Will be implemented in child widgets
            ], $this->config['metrics'] ?? [])
        ];
    }

    public function render()
    {
        return view('livewire.analytics-widget');
    }
}