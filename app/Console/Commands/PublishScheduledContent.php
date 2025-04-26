<?php

namespace App\Console\Commands;

use App\Models\Content;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ContentPublishedNotification;

class PublishScheduledContent extends Command
{
    protected $signature = 'content:publish-scheduled';
    protected $description = 'Publish scheduled content and unpublish expired content';

    public function handle()
    {
        // Publish scheduled content
        $toPublish = Content::where('publish_status', 'scheduled')
            ->where('scheduled_publish_at', '<=', now())
            ->get();

        foreach ($toPublish as $content) {
            $content->update([
                'publish_status' => 'published',
                'status' => 'published',
                'published_at' => now()
            ]);

            // Send notification to author
            Notification::send($content->user, new ContentPublishedNotification($content));
        }

        // Unpublish expired content
        $toUnpublish = Content::where('publish_status', 'published')
            ->whereNotNull('scheduled_unpublish_at')
            ->where('scheduled_unpublish_at', '<=', now())
            ->get();

        foreach ($toUnpublish as $content) {
            $content->update([
                'publish_status' => 'unpublished',
                'status' => 'unpublished'
            ]);
        }

        $this->info("Published {$toPublish->count()} items and unpublished {$toUnpublish->count()} items");
    }
}