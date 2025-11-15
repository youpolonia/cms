<?php
/**
 * Task Logger - Records task execution details
 */
class TaskLogger {
    const LOG_DIR = __DIR__.'/logs/';
    const MAX_LOG_FILES = 30;

    /**
     * Log task execution result
     * @param string $taskName Name of the task
     * @param array $result From TaskRunner::execute()
     * @return bool True if logged successfully
     */
    public static function log(string $taskName, array $result): bool {
        if (!file_exists(self::LOG_DIR)) {
            mkdir(self::LOG_DIR, 0755, true);
        }

        $logFile = self::LOG_DIR.'tasks_'.date('Y-m-d').'.log';
        $logEntry = sprintf(
            "[%s] %s: %s | Time: %ss | Memory: %sMB\n",
            date('Y-m-d H:i:s'),
            $taskName,
            $result['success'] ? 'SUCCESS' : 'FAILED: '.$result['error'],
            $result['execution_time'] ?? 0,
            $result['memory_used'] ?? 0
        );

        file_put_contents($logFile, $logEntry, FILE_APPEND);
        self::rotateLogs();
        return true;
    }

    /**
     * Rotate old log files
     */
    private static function rotateLogs(): void {
        $files = glob(self::LOG_DIR.'tasks_*.log');
        if (count($files) > self::MAX_LOG_FILES) {
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            for ($i = 0; $i < count($files) - self::MAX_LOG_FILES; $i++) {
                unlink($files[$i]);
            }
        }
    }

    /**
     * Get recent logs for a task
     * @param string $taskName Optional task name filter
     * @param int $limit Number of entries to return
     * @return array Log entries
     */
    public static function getLogs(?string $taskName = null, int $limit = 100): array {
        $logs = [];
        $files = glob(self::LOG_DIR.'tasks_*.log');
        rsort($files);

        foreach ($files as $file) {
            $content = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($content as $line) {
                if ($taskName && strpos($line, $taskName) === false) {
                    continue;
                }
                $logs[] = $line;
                if (count($logs) >= $limit) {
                    break 2;
                }
            }
        }

        return $logs;
    }
}
