<?php

namespace App\Jobs;

use App\Models\ContentSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessUnpublishContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ContentSchedule $schedule
    ) {}

    public function handle()
    {
        $this->schedule->content->update([
            'published_at' => null,
            'status' => 'draft'
        ]);

        $this->schedule->update([
            'status' => 'completed'
        ]);
    }
}