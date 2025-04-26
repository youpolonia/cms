<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentVersionRestored
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $contentId;
    public int $versionId;
    public int $userId;
    public string $reason;

    public function __construct(int $contentId, int $versionId, int $userId, string $reason = '')
    {
        $this->contentId = $contentId;
        $this->versionId = $versionId;
        $this->userId = $userId;
        $this->reason = $reason;
    }
}