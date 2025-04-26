<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;

class AutopilotService
{
    protected $isRunning = false;
    protected $heartbeatKey = 'autopilot_heartbeat_v2';
    protected $maxRuntime = 3600; // 1 hour
    protected $cooldownPeriod = 60; // 1 minute

    public function start(): void
    {
        if ($this->isRunning) {
            throw new \RuntimeException('Autopilot is already running');
        }

        $this->isRunning = true;
        $startTime = time();

        Log::info('Autopilot v2 started');
        $this->recordHeartbeat();
        $this->seedInitialTasks();

        try {
            while ($this->shouldContinue($startTime)) {
                $this->processTasks();
                sleep(5); // Reduced polling frequency
            }
        } finally {
            $this->cleanup();
        }
    }

    protected function seedInitialTasks(): void
    {
        if (\App\Models\AutopilotTask::count() === 0) {
            $tasks = [
                [
                    'name' => 'content_approval_check',
                    'payload' => [],
                    'available_at' => now(),
                    'max_attempts' => 3
                ],
                [
                    'name' => 'theme_update_check',
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
                \App\Models\AutopilotTask::create($task);
            }
            
            Log::info('Seeded initial autopilot tasks');
        }
    }

    public function processTasks(): void
    {
        Log::info('Processing autopilot tasks');
        $this->recordHeartbeat();
        
        $this->checkSystemHealth();
        $this->processPendingTasks();
    }

    protected function checkSystemHealth(): void
    {
        $load = sys_getloadavg();
        $memory = memory_get_usage(true) / 1024 / 1024;
        
        Log::debug('System check', [
            'load' => $load,
            'memory' => round($memory, 2) . 'MB'
        ]);
    }

    protected function processPendingTasks(): void
    {
        $tasks = $this->getPendingTasks();
        
        foreach ($tasks as $taskData) {
            try {
                if (isset($taskData['handler']) && is_callable($taskData['handler'])) {
                    $taskData['handler']();
                } else {
                    $this->executeTask($taskData['name'], $taskData['payload'] ?? []);
                }
            } catch (\Exception $e) {
                Log::error("Task failed: {$taskData['name']}", [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    protected function getPendingTasks(): array
    {
        $tasks = [];
        
        // Add system health check if not run in last 5 minutes
        if (!\App\Models\AutopilotTask::where('name', 'system_health_check')
            ->where('completed_at', '>', now()->subMinutes(5))
            ->exists()) {
            
            $tasks[] = [
                'name' => 'system_health_check',
                'handler' => fn() => $this->checkSystemHealth()
            ];
        }

        // Add MCP verification if not run in last 15 minutes
        if (!\App\Models\AutopilotTask::where('name', 'mcp_server_verify')
            ->where('completed_at', '>', now()->subMinutes(15))
            ->exists()) {
            
            $tasks[] = [
                'name' => 'mcp_server_verify',
                'handler' => fn() => \Artisan::call('mcp:verify')
            ];
        }

        // Get any pending tasks from database
        $dbTasks = \App\Models\AutopilotTask::pending()
            ->available()
            ->orderBy('available_at')
            ->limit(5)
            ->get();

        foreach ($dbTasks as $task) {
            $tasks[] = [
                'name' => $task->name,
                'handler' => function() use ($task) {
                    $this->processNextTask();
                }
            ];
        }

        return $tasks;
    }

    protected function executeTask(string $taskName, array $payload = []): void
    {
        Log::info("Executing task: {$taskName}");
        $start = microtime(true);
        
        try {
            $this->processNextTask();
            
            $duration = round((microtime(true) - $start) * 1000, 2);
            Log::debug("Task completed", [
                'task' => $taskName,
                'duration_ms' => $duration
            ]);
        } catch (\Exception $e) {
            Log::error("Task execution failed", [
                'task' => $taskName,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function shouldContinue(int $startTime): bool
    {
        if (!$this->isRunning) {
            return false;
        }

        if ((time() - $startTime) > $this->maxRuntime) {
            Log::info('Autopilot completed maximum runtime');
            return false;
        }

        if ($this->isSystemOverloaded()) {
            Log::warning('System overload detected, pausing autopilot');
            sleep($this->cooldownPeriod);
            return true;
        }

        return true;
    }

    protected function isSystemOverloaded(): bool
    {
        $load = sys_getloadavg();
        $cores = (int) shell_exec('nproc') ?: 1;
        return $load[0] > (0.75 * $cores);
    }

    protected function dispatchTask(string $name, array $payload = []): void
    {
        \App\Models\AutopilotTask::create([
            'name' => $name,
            'payload' => json_encode($payload),
            'available_at' => now()
        ]);
    }

    protected function processNextTask(): bool
    {
        $task = \App\Models\AutopilotTask::where('available_at', '<=', now())
            ->where('status', 'pending')
            ->orderBy('available_at')
            ->first();

        if (!$task) {
            return false;
        }

        try {
            $task->update([
                'status' => 'processing',
                'started_at' => now()
            ]);

            // Task execution logic here
            $result = $this->executeTask($task->name, $task->payload);

            $task->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            event(new \App\Events\QueueStatusUpdated());
            return true;
        } catch (\Exception $e) {
            $attempts = $task->attempts + 1;
            $retryDelay = min(3600, pow(2, $attempts) * 60); // Exponential backoff with 1 hour max
            
            $task->update([
                'status' => ($attempts >= $task->max_attempts) ? 'failed' : 'pending',
                'error' => $e->getMessage(),
                'attempts' => $attempts,
                'available_at' => ($attempts < $task->max_attempts)
                    ? now()->addSeconds($retryDelay)
                    : null
            ]);
            return false;
        }
    }

    protected function recordHeartbeat(): void
    {
        Cache::put($this->heartbeatKey, now(), $this->cooldownPeriod * 2);
    }

    protected function cleanup(): void
    {
        $this->isRunning = false;
        Cache::forget($this->heartbeatKey);
        Log::info('Autopilot stopped');
    }

    public function stop(): void
    {
        $this->isRunning = false;
    }

    public function isActive(): bool
    {
        return Cache::has($this->heartbeatKey);
    }
}