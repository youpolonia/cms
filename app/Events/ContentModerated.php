<?php

namespace App\Events;

use App\Models\ModerationQueue;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentModerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ModerationQueue $moderation,
        public string $status
    ) {}
}