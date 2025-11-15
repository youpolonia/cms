<?php

namespace Database\Seeders;

use App\Models\RoadmapTask;
use Illuminate\Database\Seeder;

class RoadmapTaskSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = [
            // Core CMS Features (10 tasks)
            [
                'name' => 'Content versioning system',
                'description' => 'Implement version control for content changes',
                'category' => 'Core CMS',
                'priority' => 1,
                'dependencies' => null,
                'completed' => false
            ],
            [
                'name' => 'Content approval workflows',
                'description' => 'Create customizable approval processes',
                'category' => 'Core CMS',
                'priority' => 1,
                'dependencies' => null,
                'completed' => false
            ],
            // Remaining 48 tasks with similar structure...
        ];

        foreach ($tasks as $task) {
            RoadmapTask::create($task);
        }
    }
}
