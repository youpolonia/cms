<?php

namespace App\Jobs;

use App\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ContentSchedulingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // Process content ready to be published
        Content::scheduledForPublish()
            ->chunkById(100, function ($contents) {
                $contents->each->publish();
            });

        // Process content ready to be expired
        Content::scheduledForExpire()
            ->chunkById(100, function ($contents) {
                $contents->each->expire();
            });
    }
}