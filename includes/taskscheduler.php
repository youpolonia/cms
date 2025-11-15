<?php
require_once __DIR__ . '/systemalert.php';
/**
 * Enhanced Task Scheduler for PHP CMS
 * Supports both file-based and database-registered tasks
 * Manages global and per-tenant scheduled tasks
 */
class TaskScheduler {
    private string $tenantId;
    private array $tasks = [];
    private string $logFile = 'logs/task_scheduler.log';
    private bool $initialized = false;

    /**
     * @param string $tenantId The tenant identifier
     */
    public function __construct(string $tenantId) {
        $this->tenantId = $tenantId;
    }

    /**
     * Initialize the scheduler by loading all tasks
     */
    public function initialize(): void {
        if ($this->initialized) {
            return;
        }

        $this->loadFileTasks();
        $this->loadDatabaseTasks();
        $this->initialized = true;
    }

    /**
     * Load tasks from files in tasks directory
     */
    private function loadFileTasks(): void {
        $taskFiles = glob('tasks/*.php');
        
        foreach ($taskFiles as $file) {
            try {
                $taskName = basename($file, '.php');
                $task = require_once $file;
                
                if (!isset($task['interval_minutes'], $task['callback'])) {
                    throw new RuntimeException("Invalid task structure in $file");
                }

                $this->tasks[$taskName] = [
                    'source' => 'file',
                    'file' => $file,
                    'interval_minutes' => (int)$task['interval_minutes'],
                    'last_run' => $task['last_run'] ?? null,
                    'active' => $task['active'] ?? true,
                    'callback' => $task['callback'],
                    'is_global' => $task['is_global'] ?? false
                ];
            } catch (Throwable $e) {
                $this->logError("Failed to load file task $file: " . $e->getMessage());
            }
        }
    }

    /**
     * Load tasks from database
     */
    private function loadDatabaseTasks(): void {
        try {
            $pdo = $this->getDatabaseConnection();
            $stmt = $pdo->prepare("
                SELECT * FROM scheduled_tasks 
                WHERE is_active = 1 
                AND (is_global = 1 OR tenant_id = :tenantId)
            ");
            $stmt->execute(['tenantId' => $this->tenantId]);
            
            while ($task = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->tasks[$task['task_name']] = [
                    'source' => 'database',
                    'interval_minutes' => (int)$task['interval_minutes'],
                    'last_run' => $task['last_run_at'] ? strtotime($task['last_run_at']) : null,
                    'active' => (bool)$task['is_active'],
                    'callback' => [$task['callback_class'], $task['callback_method']],
                    'is_global' => (bool)$task['is_global']
                ];
            }
        } catch (Throwable $e) {
            $this->logError("Failed to load database tasks: " . $e->getMessage());
        }
    }

    /**
     * Execute all eligible tasks
     * @return array Execution results
     */
    /**
     * Get all tasks
     * @return array All tasks
     */
    public function getTasks(): array {
        $this->initialize();
        return $this->tasks;
    }

    /**
     * Get a specific task by name
     * @param string $name Task name
     * @return array|null Task data or null if not found
     */
    public function getTask(string $name): ?array {
        $this->initialize();
        return $this->tasks[$name] ?? null;
    }

    /**
     * Update task properties
     * @param string $name Task name
     * @param array $properties Properties to update
     * @return bool True if successful
     */
    public function updateTask(string $name, array $properties): bool {
        $this->initialize();
        
        if (!isset($this->tasks[$name])) {
            return false;
        }

        // Only allow updating certain properties
        $allowedProperties = ['interval_minutes', 'active'];
        foreach ($properties as $key => $value) {
            if (in_array($key, $allowedProperties) && isset($this->tasks[$name][$key])) {
                $this->tasks[$name][$key] = $value;
            }
        }

        // Persist changes based on task source
        if ($this->tasks[$name]['source'] === 'file') {
            return $this->updateFileTask($name);
        } else {
            return $this->updateDatabaseTask($name);
        }
    }

    /**
     * Update a file-based task
     */
    private function updateFileTask(string $name): bool {
        $task = $this->tasks[$name];
        $content = "<?php\nreturn [\n";
        $content .= "    'interval_minutes' => {$task['interval_minutes']},\n";
        $content .= "    'active' => " . ($task['active'] ? 'true' : 'false') . ",\n";
        $content .= "    'callback' => " . var_export($task['callback'], true) . ",\n";
        $content .= "    'is_global' => " . ($task['is_global'] ? 'true' : 'false') . ",\n";
        $content .= "];\n";

        return file_put_contents($task['file'], $content) !== false;
    }

    /**
     * Update a database-based task
     */
    private function updateDatabaseTask(string $name): bool {
        try {
            $pdo = $this->getDatabaseConnection();
            $stmt = $pdo->prepare("
                UPDATE scheduled_tasks
                SET interval_minutes = :interval,
                    is_active = :active,
                    updated_at = NOW()
                WHERE task_name = :name
                AND (is_global = 1 OR tenant_id = :tenantId)
            ");
            
            return $stmt->execute([
                'interval' => $this->tasks[$name]['interval_minutes'],
                'active' => (int)$this->tasks[$name]['active'],
                'name' => $name,
                'tenantId' => $this->tenantId
            ]);
        } catch (Throwable $e) {
            $this->logError("Failed to update database task $name: " . $e->getMessage());
            return false;
        }
    }

    public function executeEligibleTasks(): array {
        $this->initialize();
        $results = [];
        
        foreach ($this->tasks as $name => $task) {
            if (!$task['active']) {
                continue;
            }

            if ($this->isTaskDue($task)) {
                $results[$name] = $this->executeTask($name, $task);
            }
        }

        return $results;
    }

    /**
     * Check if a task is due for execution
     */
    private function isTaskDue(array $task): bool {
        if ($task['last_run'] === null) {
            return true;
        }

        $nextRun = strtotime("+{$task['interval_minutes']} minutes", $task['last_run']);
        $isOverdue = time() >= $nextRun;
        
        if ($isOverdue) {
            $overdueMinutes = floor((time() - $nextRun) / 60);
            log_alert('warning',
                "Task '{$task['name']}' is overdue by {$overdueMinutes} minutes",
                'TaskScheduler'
            );
        }
        
        return $isOverdue;
    }

    /**
     * Execute a single task
     */
    private function executeTask(string $name, array $task): array {
        try {
            $startTime = microtime(true);
            $result = call_user_func($task['callback'], $this->tenantId);
            
            $this->updateTaskLastRun($name, $task);
            $duration = round(microtime(true) - $startTime, 4);
            
            $this->logExecution($name, 'success', $duration);
            
            return [
                'status' => 'success',
                'output' => $result,
                'duration' => $duration
            ];
        } catch (Throwable $e) {
            $this->logExecution($name, 'error', 0, $e->getMessage());
            log_alert('error', "Task '$name' failed: " . $e->getMessage(), 'TaskScheduler');
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update task last run time in appropriate storage
     */
    private function updateTaskLastRun(string $name, array $task): void {
        $this->tasks[$name]['last_run'] = time();
        
        if ($task['source'] === 'database') {
            try {
                $pdo = $this->getDatabaseConnection();
                $pdo->prepare("
                    UPDATE scheduled_tasks 
                    SET last_run_at = NOW() 
                    WHERE task_name = :taskName
                ")->execute(['taskName' => $name]);
            } catch (Throwable $e) {
                $this->logError("Failed to update database task $name: " . $e->getMessage());
            }
        }
    }

    /**
     * Get database connection
     */
    private function getDatabaseConnection(): PDO {
        require_once __DIR__ . '/../core/database.php';
        return \core\Database::connection();
    }

    /**
     * Enhanced logging with structured data
     */
    private function logExecution(string $taskName, string $status, float $duration, string $error = null): void {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'tenant' => $this->tenantId,
            'task' => $taskName,
            'status' => $status,
            'duration' => $duration,
            'error' => $error
        ];

        file_put_contents($this->logFile, json_encode($logData) . PHP_EOL, FILE_APPEND);
    }

    /**
     * Log an error
     */
    private function logError(string $message): void {
        $this->logExecution('system', 'error', 0, $message);
    }
}
