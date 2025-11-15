<?php
namespace System;

class TelemetryAlertManager {
    const LOG_FILE = __DIR__ . '/../../logs/telemetry.log';
    const MAX_LINES = 500;
    const ERROR_THRESHOLD = 10; // Errors in 15 minutes
    const MODULE_THRESHOLD = 3; // Errors per module in 1 hour

    public static function analyzeLogs(): void {
        if (!file_exists(self::LOG_FILE) || !is_readable(self::LOG_FILE)) {
            return;
        }

        $lines = self::readLastLines(self::LOG_FILE, self::MAX_LINES);
        $now = time();
        $recentErrors = [];
        $moduleErrors = [];

        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            if (!$entry || !isset($entry['type'], $entry['timestamp'])) {
                continue;
            }

            $entryTime = strtotime($entry['timestamp']);
            $ageMinutes = ($now - $entryTime) / 60;

            // Check for recent errors/warnings
            if (in_array($entry['type'], ['error', 'warn']) && $ageMinutes <= 15) {
                $recentErrors[] = $entry;
            }

            // Track module-specific errors
            if ($entry['type'] === 'error' && $ageMinutes <= 60) {
                $module = $entry['context']['module'] ?? 'unknown';
                $moduleErrors[$module] = ($moduleErrors[$module] ?? 0) + 1;
            }
        }

        // Check thresholds and trigger alerts
        if (count($recentErrors) >= self::ERROR_THRESHOLD) {
            self::triggerAlert('warning', 'High error rate detected', [
                'count' => count($recentErrors),
                'period' => '15 minutes'
            ]);
        }

        foreach ($moduleErrors as $module => $count) {
            if ($count >= self::MODULE_THRESHOLD) {
                self::triggerAlert('error', "Module {$module} error spike", [
                    'module' => $module,
                    'count' => $count,
                    'period' => '1 hour'
                ]);
            }
        }
    }

    private static function readLastLines(string $file, int $maxLines): array {
        $lines = [];
        $fp = fopen($file, 'r');
        if (!$fp) return [];

        // Read file backwards
        fseek($fp, 0, SEEK_END);
        $pos = ftell($fp);
        $buffer = '';
        $lineCount = 0;

        while ($pos > 0 && $lineCount < $maxLines) {
            $chunkSize = min(1024, $pos);
            $pos -= $chunkSize;
            fseek($fp, $pos);
            $buffer = fread($fp, $chunkSize) . $buffer;

            while (($newlinePos = strrpos($buffer, "\n")) !== false) {
                $line = substr($buffer, $newlinePos + 1);
                $buffer = substr($buffer, 0, $newlinePos);
                if ($line !== '') {
                    $lines[] = $line;
                    $lineCount++;
                    if ($lineCount >= $maxLines) break;
                }
            }
        }

        fclose($fp);
        return array_reverse($lines);
    }

    private static function triggerAlert(string $type, string $message, array $context): void {
        if (!class_exists('NotificationManager')) {
            require_once __DIR__ . '/notificationmanager.php';
        }

        $fullContext = array_merge($context, [
            'source' => 'TelemetryAlertManager',
            'detected' => 'error spike'
        ]);

        NotificationManager::createSystemNotification(
            $type,
            $message,
            $fullContext
        );
    }
}
