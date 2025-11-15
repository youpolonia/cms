<?php
namespace core\tasks;

class ClearLogsTask {
    public static function run(): bool {
        try {
            $files = [
                __DIR__ . '/../../logs/app_errors.log',
                __DIR__ . '/../../logs/extensions.log'
            ];
            foreach ($files as $file) {
                if (!file_exists($file)) { continue; }
                $lines = @file($file, FILE_IGNORE_NEW_LINES);
                if ($lines === false) { continue; }
                $threshold = time() - (30 * 86400);
                $new = [];
                foreach ($lines as $line) {
                    if (preg_match('/^(\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2})/', $line, $m)) {
                        $ts = strtotime($m[1]);
                        if ($ts !== false && $ts >= $threshold) {
                            $new[] = $line;
                        }
                    } else {
                        $new[] = $line;
                    }
                }
                @file_put_contents($file, implode(PHP_EOL, $new) . PHP_EOL);
            }
            return true;
        } catch (\Throwable $e) {
            \error_log("ClearLogsTask failed: ".$e->getMessage());
            return false;
        }
    }
}
