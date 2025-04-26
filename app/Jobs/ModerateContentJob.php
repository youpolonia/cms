<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\Content;
use App\Services\ModerationService;
use Illuminate\Support\Facades\Log;

class ModerateContentJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 600];

    public function __construct(
        public Content $content,
        public ?int $userId = null
    ) {}

    public function handle(ModerationService $moderationService): void
    {
        if ($this->userId) {
            $user = \App\Models\User::find($this->userId);
            $user?->incrementAiUsage();
        }

        try {
            $moderationService->moderateContent($this->content);
        } catch (\Exception $e) {
            Log::error("Failed to moderate content {$this->content->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Content moderation job failed for content {$this->content->id}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
