<?php

namespace App\Jobs;

use App\Models\Content;
use App\Models\ContentSchedule;
use App\Notifications\ContentPublished;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class HandleContentScheduling implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ContentSchedule $schedule
    ) {}

    public function handle(): void
    {
        try {
            $content = $this->schedule->content;
            
            // Publish the content
            $content->update([
                'status' => 'published',
                'published_at' => now()
            ]);

            // Notify content owner
            $content->user->notify(new ContentPublished($content));

            // Log successful publishing
            Log::info("Content {$content->id} published via schedule {$this->schedule->id}");

            // Clean up the schedule record
            $this->schedule->delete();

        } catch (\Exception $e) {
            Log::error("Failed to publish scheduled content: {$e->getMessage()}");
            $this->schedule->update([
                'status' => 'failed',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}