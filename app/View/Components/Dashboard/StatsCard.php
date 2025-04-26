<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;

class StatsCard extends Component
{
    public $title;
    public $value;
    public $icon;
    public $trend;
    public $trendDirection;

    public function __construct($title, $value, $icon = null, $trend = null, $trendDirection = 'up')
    {
        $this->title = $title;
        $this->value = $value;
        $this->icon = $icon;
        $this->trend = $trend;
        $this->trendDirection = $trendDirection;
    }

    public function render()
    {
        return view('components.dashboard.stats-card');
    }
}