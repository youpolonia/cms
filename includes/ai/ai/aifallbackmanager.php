<?php
class AIFallbackManager {
    const FALLBACK_LOG = __DIR__ . '/../../logs/ai-fallback.log';
    const LAST_FALLBACK_FILE = __DIR__ . '/../../storage/ai_last_fallback.json';

    public static function handleFailure(string $context, \Throwable $error): string {
        self::logFailure($context, $error);
        self::storeLastFallback($context);
        return ''; // Return empty string as safe fallback
    }

    private static function logFailure(string $context, \Throwable $error): void {
        $logEntry = sprintf(
            "[%s] Context: %s | Error: %s\n",
            date('Y-m-d H:i:s'),
            $context,
            $error->getMessage()
        );

        if (!is_dir(dirname(self::FALLBACK_LOG))) {
            mkdir(dirname(self::FALLBACK_LOG), 0755, true);
        }
        file_put_contents(self::FALLBACK_LOG, $logEntry, FILE_APPEND);
    }

    private static function storeLastFallback(string $context): void {
        $data = [
            'timestamp' => time(),
            'context' => $context,
            'fallback_used' => true
        ];

        if (!is_dir(dirname(self::LAST_FALLBACK_FILE))) {
            mkdir(dirname(self::LAST_FALLBACK_FILE), 0755, true);
        }
        file_put_contents(self::LAST_FALLBACK_FILE, json_encode($data));
    }

    public static function getLastFallback(): ?array {
        if (!file_exists(self::LAST_FALLBACK_FILE)) {
            return null;
        }
        return json_decode(file_get_contents(self::LAST_FALLBACK_FILE), true);
    }
}
