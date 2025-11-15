<?php
namespace core;

require_once __DIR__ . '/tasks/cacherefreshertask.php';
require_once __DIR__ . '/tasks/clearlogstask.php';

class Scheduler {
    public static function runDue(int $maxRun = 5): array {
        $db = \core\Database::connection();
        $stats = ['ran' => 0, 'errors' => 0, 'skipped' => 0, 'total_found' => 0];

        $sql = "SELECT id, task_name, interval_str, last_run FROM scheduled_tasks WHERE is_active = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $tasks = $stmt->fetchAll();
        $stats['total_found'] = count($tasks);

        $executed = 0;
        foreach ($tasks as $task) {
            if ($executed >= $maxRun) {
                $stats['skipped'] += (count($tasks) - $executed);
                break;
            }

            $taskId = $task['id'];
            $className = $task['task_name'];
            $intervalStr = $task['interval_str'];
            $lastRun = $task['last_run'];

            $intervalSec = self::parseIntervalToSeconds($intervalStr);
            $now = time();
            $nextDue = ($lastRun ? strtotime($lastRun) : 0) + $intervalSec;

            if ($now < $nextDue) {
                continue;
            }

            $executed++;

            $updateSql = "UPDATE scheduled_tasks SET last_run = NOW() WHERE id = ?";
            $updateStmt = $db->prepare($updateSql);
            $updateStmt->execute([$taskId]);

            try {
                $cls = $className;
                if (class_exists($cls) && method_exists($cls, 'run')) {
                    $res = $cls::run();
                    if ($res !== false) {
                        $stats['ran']++;
                    } else {
                        \error_log("Scheduler: task '$cls' returned false");
                    }
                } else {
                    \error_log("Scheduler: task class '$cls' not found or missing run()");
                }
            } catch (\Throwable $e) {
                $stats['errors']++;
                \error_log("Scheduler error in '$cls': ".$e->getMessage());
            }
        }

        return $stats;
    }

    private static function parseIntervalToSeconds(string $interval): int {
        $s = \trim($interval);
        if ($s === '') return 300;
        // 1) czysta liczba — sekundy
        if (\ctype_digit($s)) {
            return (int)$s;
        }
        // 2) "+N UNIT" lub "N UNIT"
        if (\preg_match('/^\+?\s*(\d+)\s*(SECOND|SECONDS|MINUTE|MINUTES|HOUR|HOURS|DAY|DAYS)$/i', $s, $m)) {
            $n = (int)$m[1];
            $u = \strtoupper($m[2]);
            switch ($u) {
                case 'SECOND':
                case 'SECONDS': return $n;
                case 'MINUTE':
                case 'MINUTES': return $n * 60;
                case 'HOUR':
                case 'HOURS':   return $n * 3600;
                case 'DAY':
                case 'DAYS':    return $n * 86400;
            }
        }
        // 3) ISO8601 "PT#H#M#S" (subset)
        if (\preg_match('/^P(T(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?)$/i', $s, $m)) {
            $h = isset($m[2]) ? (int)$m[2] : 0;
            $mi = isset($m[3]) ? (int)$m[3] : 0;
            $se = isset($m[4]) ? (int)$m[4] : 0;
            return $h*3600 + $mi*60 + $se;
        }
        // fallback
        return 300;
    }
}
