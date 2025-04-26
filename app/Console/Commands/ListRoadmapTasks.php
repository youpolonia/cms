<?php

namespace App\Console\Commands;

use App\Models\RoadmapTask;
use Illuminate\Console\Command;

class ListRoadmapTasks extends Command
{
    protected $signature = 'roadmap:list {--priority= : Filter by priority} {--limit=5 : Limit results}';
    protected $description = 'List roadmap tasks';

    public function handle()
    {
        $query = RoadmapTask::query()
            ->where('completed', false)
            ->orderBy('priority')
            ->orderBy('created_at');

        if ($this->option('priority')) {
            $query->where('priority', $this->option('priority'));
        }

        $tasks = $query->limit($this->option('limit'))->get();

        $this->table(
            ['ID', 'Name', 'Category', 'Priority', 'Dependencies'],
            $tasks->map(function ($task) {
                return [
                    $task->id,
                    $task->name,
                    $task->category,
                    $task->priority,
                    $task->dependencies ? implode(',', json_decode($task->dependencies)) : 'None'
                ];
            })
        );

        return 0;
    }
}