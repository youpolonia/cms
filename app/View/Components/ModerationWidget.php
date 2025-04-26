<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\ModerationQueue;

class ModerationWidget extends Component
{
    public $pendingCount;

    public function __construct()
    {
        $this->pendingCount = ModerationQueue::where('status', 'pending')->count();
    }

    public function render()
    {
        return view('components.moderation-widget');
    }
}
