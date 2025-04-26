<?php

namespace App\Console\Commands;

use App\Models\AutopilotTask;
use Illuminate\Console\Command;

class SeedAutopilotTasks extends Command
{
    protected $signature = 'autopilot:seed-tasks';
    protected $description = 'Seed initial autopilot tasks';

    public function handle()
    {
        if (AutopilotTask::count() > 0) {
            $this->info('Autopilot tasks already exist');
            return;
        }

        $tasks = [
            [
                'name' => 'system_health_check',
                'payload' => [],
                'available_at' => now(),
                'max_attempts' => 3
            ],
            [
                'name' => 'content_approval_check',
                'payload' => [],
                'available_at' => now()->addMinutes(5),
                'max_attempts' => 3
            ],
            [
                'name' => 'analytics_export',
                'payload' => [],
                'available_at' => now()->addHours(1),
                'max_attempts' => 3
            ]
        ];

        foreach ($tasks as $task) {
            AutopilotTask::create($task);
        }

        $this->info('Successfully seeded autopilot tasks');
    }
}