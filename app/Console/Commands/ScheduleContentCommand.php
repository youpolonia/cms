<?php

namespace App\Console\Commands;

use App\Models\Content;
use Illuminate\Console\Command;

class ScheduleContentCommand extends Command
{
    protected $signature = 'content:schedule';
    protected $description = 'Process scheduled content publishing and expiration';

    public function handle()
    {
        $contents = Content::scheduled()->get();
        
        foreach ($contents as $content) {
            dispatch(new \App\Jobs\ContentSchedulingJob($content));
        }

        $this->info("Dispatched scheduling jobs for {$contents->count()} content items");
    }
}