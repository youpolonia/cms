<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Content;
use Carbon\Carbon;

class ScheduleWidget extends Component
{
    public $upcomingPublications;
    public $upcomingExpirations;

    public function __construct()
    {
        $this->upcomingPublications = Content::where('publish_at', '>', now())
            ->where('is_published', false)
            ->orderBy('publish_at')
            ->take(5)
            ->get();

        $this->upcomingExpirations = Content::where('expire_at', '>', now())
            ->where('is_published', true)
            ->orderBy('expire_at')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('components.schedule-widget');
    }
}
