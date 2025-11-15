<?php
class SelfHealingManager {
    const AI_STABILIZATION_PERIOD = 21600; // 6 hours
    const SCHEDULER_STABILIZATION_PERIOD = 14400; // 4 hours
    const MAX_LOG_LINES = 1500;

    public static function attemptAutoRestore() {
        $settingsFile = __DIR__ . '/../../config/settings.ini';
        if (!file_exists($settingsFile)) {
            return false;
        }

        // Avoid parsing external INI for env; use safe defaults or central config
        $settings = []; // parse_ini_file removed
        $telemetryLog = __DIR__ . '/../../logs/telemetry.log';
        $selfHealingLog = __DIR__ . '/../../logs/self-healing.log';
        $restored = false;

        // Check AI restoration conditions
        if (isset($settings['ai']['disabled']) && $settings['ai']['disabled']) {
            if (self::isSystemStable('ai', self::AI_STABILIZATION_PERIOD, $telemetryLog)) {
                $settings['ai']['disabled'] = false;
                self::logRestoration($selfHealingLog, 'ai');
                $restored = true;
            }
        }

        // Check Scheduler restoration conditions
        if (isset($settings['scheduler']['disabled']) && $settings['scheduler']['disabled']) {
            if (self::isSystemStable('scheduler', self::SCHEDULER_STABILIZATION_PERIOD, $telemetryLog)) {
                $settings['scheduler']['disabled'] = false;
                self::logRestoration($selfHealingLog, 'scheduler');
                $restored = true;
            }
        }

        // Save settings if any changes were made
        if ($restored) {
            self::writeSettings($settingsFile, $settings);
            self::sendNotification('System restored modules: ' . 
                ($settings['ai']['disabled'] ?? true ? '' : 'AI ') . 
                ($settings['scheduler']['disabled'] ?? true ? '' : 'Scheduler'));
        }

        return $restored;
    }

    private static function isSystemStable(string $module, int $period, string $logPath): bool {
        if (!file_exists($logPath)) {
            return false;
        }

        $now = time();
        $cutoff = $now - $period;
        $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_slice($lines, -self::MAX_LOG_LINES); // Only check recent entries

        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            if (!$entry || !isset($entry['timestamp'])) {
                continue;
            }

            $entryTime = strtotime($entry['timestamp']);
            if ($entryTime > $cutoff && 
                isset($entry['type']) && $entry['type'] === 'error' &&
                isset($entry['context']['module']) && $entry['context']['module'] === $module) {
                return false;
            }
        }

        return true;
    }

    private static function logRestoration(string $logPath, string $module) {
        $entry = json_encode([
            'timestamp' => gmdate('c'),
            'action' => 'auto-restore',
            'restored_module' => $module,
            'context' => [
                'reason' => 'stabilized',
                'previous_state' => 'disabled_by_self_healing'
            ]
        ]);

        file_put_contents($logPath, $entry . PHP_EOL, FILE_APPEND);
    }

    private static function writeSettings(string $path, array $settings) {
        $content = '';
        foreach ($settings as $section => $values) {
            $content .= "[$section]\n";
            foreach ($values as $key => $value) {
                $content .= "$key = " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "\n";
            }
            $content .= "\n";
        }

        file_put_contents($path, $content);
    }

    private static function sendNotification(string $message) {
        if (class_exists('NotificationManager')) {
            NotificationManager::addSystemNotification($message);
        }
    }
}
