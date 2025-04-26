<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\RoadmapTask;

class ProcessRoadmapTask implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public RoadmapTask $task,
        public int $taskNumber
    ) {}

    public function handle(): void
    {
        $this->task->execute();
        
        // Log progress
        \Log::info("Completed roadmap task {$this->taskNumber}: {$this->task->name}");
    }
}
