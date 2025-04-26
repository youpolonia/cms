<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentVersionCompared
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $contentId,
        public int $version1Id,
        public int $version2Id,
        public string $granularity,
        public int $userId
    ) {}
}