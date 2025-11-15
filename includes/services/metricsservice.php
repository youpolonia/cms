<?php
declare(strict_types=1);

class MetricsService
{
    private static array $responseTimes = [];
    private static array $errors = [];

    public static function trackResponseTime(string $endpoint, float $timeMs): void
    {
        if (!isset(self::$responseTimes[$endpoint])) {
            self::$responseTimes[$endpoint] = [];
        }
        self::$responseTimes[$endpoint][] = $timeMs;
    }

    public static function trackError(string $type, string $message): void
    {
        if (!isset(self::$errors[$type])) {
            self::$errors[$type] = [];
        }
        if (!isset(self::$errors[$type][$message])) {
            self::$errors[$type][$message] = 0;
        }
        self::$errors[$type][$message]++;
    }

    public static function getMetrics(): array
    {
        return [
            'response_times' => self::$responseTimes,
            'errors' => self::$errors
        ];
    }

    public static function resetMetrics(): void
    {
        self::$responseTimes = [];
        self::$errors = [];
    }
}
