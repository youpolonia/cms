<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class RoadmapTask extends Model
{
    protected $casts = [
        'dependencies' => 'array',
        'parameters' => 'array'
    ];

    public function execute(): void
    {
        try {
            // Execute task logic based on category/type
            $method = 'execute' . str_replace(' ', '', ucwords($this->category));
            if (method_exists($this, $method)) {
                $this->$method();
            } else {
                $this->executeDefault();
            }

            $this->markCompleted();
        } catch (\Exception $e) {
            Log::error("Failed executing roadmap task {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    protected function executeDefault(): void
    {
        // Default task execution logic
        $this->output = "Executed task: {$this->name}";
    }

    protected function markCompleted(): void
    {
        $this->update([
            'completed' => true,
            'completed_at' => now(),
        ]);
    }
}
