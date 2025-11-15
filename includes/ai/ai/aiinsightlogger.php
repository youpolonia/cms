<?php
/**
 * AI Insight Logger - Logs and analyzes AI actions
 */
class AIInsightLogger {
    private static $logFile = __DIR__ . '/../../logs/ai_actions.jsonl';
    private static $maxLogs = 1000;

    /**
     * Get summary statistics
     * @return array [total_actions, type_breakdown, acceptance_rate]
     */
    public static function getSummaryStats(): array {
        $logs = self::getAllLogs();
        if (empty($logs)) return [];
        
        $stats = [
            'total_actions' => count($logs),
            'type_breakdown' => [],
            'acceptance_rate' => 0
        ];
        
        $accepted = 0;
        foreach ($logs as $log) {
            $action = $log['action'] ?? '';
            $stats['type_breakdown'][$action] = ($stats['type_breakdown'][$action] ?? 0) + 1;
            
            if (isset($log['details']['status']) && $log['details']['status'] === 'accepted') {
                $accepted++;
            }
        }
        
        if ($stats['total_actions'] > 0) {
            $stats['acceptance_rate'] = round(($accepted / $stats['total_actions']) * 100, 2);
        }
        
        return $stats;
    }
    
    /**
     * Get timeline data for chart
     * @param int $days Number of days to require_once
     * @return array [date => count]
     */
    public static function getTimelineData(int $days = 7): array {
        $logs = self::getAllLogs();
        if (empty($logs)) return [];
        
        $timeline = [];
        $cutoff = strtotime("-$days days");
        
        foreach ($logs as $log) {
            $timestamp = strtotime($log['timestamp']);
            if ($timestamp < $cutoff) continue;
            
            $date = date('Y-m-d', $timestamp);
            $timeline[$date] = ($timeline[$date] ?? 0) + 1;
        }
        
        // Fill missing dates with 0
        $result = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $result[$date] = $timeline[$date] ?? 0;
        }
        
        return $result;
    }
    
    /**
     * Get top used features
     * @param int $limit Number of top features to return
     * @return array [module => count]
     */
    public static function getTopUsage(int $limit = 5): array {
        $logs = self::getAllLogs();
        if (empty($logs)) return [];
        
        $usage = [];
        foreach ($logs as $log) {
            $module = $log['module'] ?? '';
            $usage[$module] = ($usage[$module] ?? 0) + 1;
        }
        
        arsort($usage);
        return array_slice($usage, 0, $limit, true);
    }
    
    /**
     * Get all logs from file
     * @return array All log entries
     */
    private static function getAllLogs(): array {
        if (!file_exists(self::$logFile)) return [];
        
        $lines = file(self::$logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) return [];
        
        $logs = [];
        foreach ($lines as $line) {
            $log = json_decode($line, true);
            if ($log) $logs[] = $log;
        }
        
        return $logs;
    }
    
    /**
     * Log an AI action
     * @param string $module Module name
     * @param string $action Action performed
     * @param array $details Additional details
     * @param string $userId Optional user ID
     * @return bool True if logged successfully
     */
    public static function logAction(string $module, string $action, array $details = [], ?string $userId = null): bool {
        if (empty($module)) return false;
        
        $logEntry = [
            'timestamp' => date('c'),
            'module' => $module,
            'action' => $action,
            'details' => $details,
            'user_id' => $userId
        ];
        
        if (!file_exists(dirname(self::$logFile))) {
            mkdir(dirname(self::$logFile), 0755, true);
        }
        
        $result = file_put_contents(
            self::$logFile, 
            json_encode($logEntry) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
        
        self::rotateLogs();
        return $result !== false;
    }
    
    /**
     * Get recent logs (most recent first)
     * @param int $limit Max number of logs to return
     * @return array Array of log entries
     */
    public static function getRecentLogs(int $limit = 50): array {
        $logs = self::getAllLogs();
        return array_slice($logs, -$limit);
    }
    
    /**
     * Rotate logs if they exceed max size
     */
    private static function rotateLogs(): void {
        if (!file_exists(self::$logFile)) return;
        
        $lines = file(self::$logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (count($lines) > self::$maxLogs) {
            $lines = array_slice($lines, -self::$maxLogs);
            file_put_contents(self::$logFile, implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX);
        }
    }
}
