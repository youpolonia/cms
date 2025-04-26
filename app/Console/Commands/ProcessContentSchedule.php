<?php

namespace App\Console\Commands;

use App\Jobs\ContentSchedulingJob;
use App\Models\Content;
use Illuminate\Console\Command;

class ProcessContentSchedule extends Command
{
    protected $signature = 'content:process-schedule';
    protected $description = 'Process scheduled content publishing and expiration';

    public function handle()
    {
        $this->info('Processing content schedule...');

        // Process content ready to publish
        // Process content ready to publish
        $publishContents = Content::getShouldPublish();
        foreach ($publishContents as $content) {
            ContentSchedulingJob::dispatch($content);
            $this->line("Dispatching publish job for content ID: {$content->id}");
        }

        // Process content ready to expire
        $expireContents = Content::getShouldExpire();
        foreach ($expireContents as $content) {
            ContentSchedulingJob::dispatch($content);
            $this->line("Dispatching expire job for content ID: {$content->id}");
        }

        $this->info('Content schedule processing completed');
    }
}