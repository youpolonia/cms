<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ContentStatsWidget extends Component
{
    public $totalContent;
    public $totalVersions;
    public $recentActivity;

    public function render()
    {
        return view('components.content-stats-widget');
    }
}
