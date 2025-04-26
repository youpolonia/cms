<?php

namespace App\Console\Commands;

use App\Models\RoadmapTask;
use App\Jobs\ProcessRoadmapTask;
use Illuminate\Console\Command;

class DispatchRoadmapTasks extends Command
{
    protected $signature = 'roadmap:dispatch';
    protected $description = 'Dispatch all CMS roadmap tasks to the queue';

    public function handle()
    {
        $tasks = $this->getRoadmapTasks();

        foreach ($tasks as $taskData) {
            $task = RoadmapTask::firstOrCreate(
                ['name' => $taskData['name']],
                $taskData
            );
            
            if (!$task->completed) {
                dispatch(new ProcessRoadmapTask($task));
                $this->info("Dispatched: {$task->name}");
            } else {
                $this->line("Skipped completed: {$task->name}");
            }
        }

        $this->info("\nAll roadmap tasks processed");
    }

    protected function getRoadmapTasks(): array
    {
        return [
            [
                'name' => 'Database schema optimizations',
                'description' => 'Review and optimize database schema for performance',
                'category' => 'Database',
                'priority' => 1,
                'parameters' => ['tables' => ['contents', 'categories']]
            ],
            [
                'name' => 'API documentation generation',
                'description' => 'Generate OpenAPI docs for all endpoints',
                'category' => 'Documentation',
                'priority' => 2
            ],
            // Additional tasks would be added here...
        ];
    }
}
