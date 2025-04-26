<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TaskExecutionService
{
    protected $maxExecutionTime = 300; // 5 minutes
    protected $maxRetries = 3;
    protected $currentTask = null;

    public function execute($task)
    {
        $this->currentTask = $task;
        $attempts = 0;
        $success = false;

        while ($attempts < $this->maxRetries && !$success) {
            try {
                $startTime = microtime(true);
                $result = $this->runTask($task);
                $success = true;
                
                Log::info("Task completed", [
                    'task' => $task,
                    'duration' => microtime(true) - $startTime,
                    'attempts' => $attempts + 1
                ]);
                
                return $result;
            } catch (\Exception $e) {
                $attempts++;
                Log::error("Task failed attempt {$attempts}", [
                    'task' => $task,
                    'error' => $e->getMessage()
                ]);
                
                if ($attempts >= $this->maxRetries) {
                    throw $e;
                }
                
                sleep(pow(2, $attempts)); // Exponential backoff
            }
        }
    }

    protected function runTask($task)
    {
        set_time_limit($this->maxExecutionTime);
        
        // Validate task structure
        if (!isset($task['command'])) {
            throw new \InvalidArgumentException("Task must contain a command");
        }

        // Check context size
        $estimatedTokens = $this->estimateTokenUsage($task);
        $maxAllowed = $task['max_context_size'] ?? 50000;
        
        if ($estimatedTokens > $maxAllowed) {
            throw new \RuntimeException(sprintf(
                "Task context too large (%d tokens, max %d)",
                $estimatedTokens,
                $maxAllowed
            ));
        }

        // Execute the command
        $output = [];
        $exitCode = 0;
        exec($task['command'], $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException("Command failed with exit code {$exitCode}");
        }

        return [
            'output' => $output,
            'exit_code' => $exitCode,
            'completed_at' => now()
        ];
    }

    public function estimateTokenUsage(array $task): int
    {
        // Simple token estimation based on command length
        $baseTokens = strlen($task['command']) * 0.75;
        
        // Add tokens for arguments if present
        if (isset($task['arguments'])) {
            $baseTokens += strlen(json_encode($task['arguments'])) * 0.75;
        }
        
        // Add tokens for description if present
        if (isset($task['description'])) {
            $baseTokens += strlen($task['description']) * 0.75;
        }
        
        return (int)ceil($baseTokens);
    }

    public function getCurrentTask()
    {
        return $this->currentTask;
    }
}