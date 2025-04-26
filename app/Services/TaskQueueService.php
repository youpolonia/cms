<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TaskQueueService
{
    protected $queueKey = 'autonomous_task_queue';
    protected $maxQueueSize = 100;
    protected $executionService;

    public function __construct(TaskExecutionService $executionService)
    {
        $this->executionService = $executionService;
    }

    public function addTask(array $task, int $priority = 5)
    {
        $queue = $this->getQueue();
        
        if (count($queue) >= $this->maxQueueSize) {
            throw new \RuntimeException("Task queue is full");
        }

        $task['priority'] = $priority;
        $task['created_at'] = now();
        $queue[] = $task;

        $this->saveQueue($this->sortQueue($queue));
    }

    public function processNextTask()
    {
        $queue = $this->getQueue();
        
        if (empty($queue)) {
            return null;
        }

        $task = array_shift($queue);
        $this->saveQueue($queue);

        try {
            return $this->executionService->execute($task);
        } catch (\Exception $e) {
            Log::error("Failed to process task", [
                'task' => $task,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    protected function getQueue(): array
    {
        return Cache::get($this->queueKey, []);
    }

    protected function saveQueue(array $queue)
    {
        Cache::put($this->queueKey, $queue);
    }

    protected function sortQueue(array $queue): array
    {
        usort($queue, function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        return $queue;
    }

    public function getQueueSize(): int
    {
        return count($this->getQueue());
    }
}