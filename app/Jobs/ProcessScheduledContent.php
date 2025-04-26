<?php

namespace App\Jobs;

use App\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessScheduledContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Publish scheduled content
        Content::scheduledForPublication()
            ->each(function ($content) {
                $content->publish();
            });

        // Expire scheduled content
        Content::scheduledForExpiration()
            ->each(function ($content) {
                $content->expire();
            });
    }
}