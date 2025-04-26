<?php

namespace App\Console\Commands;

use App\Models\ScheduledContent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledContent extends Command
{
    protected $signature = 'content:process-scheduled';
    protected $description = 'Process scheduled content for publishing/depublishing';

    public function handle()
    {
        $this->processPublishing();
        $this->processDepublishing();
    }

    protected function processPublishing()
    {
        $toPublish = ScheduledContent::where('publish_at', '<=', now())
            ->where('status', 'pending')
            ->get();

        foreach ($toPublish as $scheduled) {
            try {
                $scheduled->content->update(['published_at' => now()]);
                $scheduled->update(['status' => 'published']);
                Log::info("Published content {$scheduled->content_id} as scheduled");
            } catch (\Exception $e) {
                Log::error("Failed to publish content {$scheduled->content_id}: " . $e->getMessage());
                $scheduled->update(['status' => 'failed']);
            }
        }
    }

    protected function processDepublishing()
    {
        $toDepublish = ScheduledContent::where('depublish_at', '<=', now())
            ->where('status', 'published')
            ->get();

        foreach ($toDepublish as $scheduled) {
            try {
                $scheduled->content->update(['published_at' => null]);
                $scheduled->update(['status' => 'depublished']);
                Log::info("Depublished content {$scheduled->content_id} as scheduled");
            } catch (\Exception $e) {
                Log::error("Failed to depublish content {$scheduled->content_id}: " . $e->getMessage());
                $scheduled->update(['status' => 'failed']);
            }
        }
    }
}
