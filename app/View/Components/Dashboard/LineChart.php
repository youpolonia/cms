<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;

class LineChart extends Component
{
    public $title;
    public $chartId;
    public $labels;
    public $datasets;
    public $height;

    public function __construct(
        $title,
        $chartId = null,
        $labels = [],
        $datasets = [],
        $height = '300px'
    ) {
        $this->title = $title;
        $this->chartId = $chartId ?? 'chart-'.uniqid();
        $this->labels = $labels;
        $this->datasets = $datasets;
        $this->height = $height;
    }

    public function render()
    {
        return view('components.dashboard.line-chart');
    }
}