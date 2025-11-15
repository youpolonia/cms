<?php
declare(strict_types=1);

namespace AI;

use Core\Logger;

require_once __DIR__ . '/../../core/logger.php';

class SuggestionMetrics {
    private static array $metrics = [
        'total_suggestions' => 0,
        'accepted' => 0,
        'rejected' => 0,
        'response_times' => [],
        'errors' => []
    ];

    private static ?Logger $logger = null;

    public static function trackSuggestionGenerated(): void {
        self::$metrics['total_suggestions']++;
        self::getLogger()->info('AI suggestion generated');
    }

    public static function trackSuggestionAccepted(): void {
        self::$metrics['accepted']++;
        self::getLogger()->info('AI suggestion accepted');
    }

    public static function trackSuggestionRejected(): void {
        self::$metrics['rejected']++;
        self::getLogger()->info('AI suggestion rejected');
    }

    public static function trackResponseTime(float $milliseconds): void {
        self::$metrics['response_times'][] = $milliseconds;
        self::getLogger()->info('AI suggestion response time recorded', ['ms' => $milliseconds]);
    }

    public static function trackError(string $error): void {
        self::$metrics['errors'][] = $error;
        self::getLogger()->error("AI suggestion error: $error");
    }

    private static function getLogger(): Logger {
        if (self::$logger === null) {
            require_once __DIR__ . '/../core/logger/LoggerFactory.php';
            self::$logger = LoggerFactory::create('file', [
                'file_path' => __DIR__ . '/../../logs/ai_suggestions.log',
                'type' => 'file'
            ]);
        }
        return self::$logger;
    }

    public static function getMetrics(): array {
        return array_merge(self::$metrics, [
            'acceptance_rate' => self::$metrics['total_suggestions'] > 0 
                ? (self::$metrics['accepted'] / self::$metrics['total_suggestions']) * 100 
                : 0,
            'avg_response_time' => !empty(self::$metrics['response_times'])
                ? array_sum(self::$metrics['response_times']) / count(self::$metrics['response_times'])
                : 0
        ]);
    }
}
