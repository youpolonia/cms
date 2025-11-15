<?php
/**
 * AI Feedback Logger - specialized logger for AI interaction tracking
 */
declare(strict_types=1);

namespace Includes\Core;

class AIFeedbackLogger extends Logger
{
    private const LOG_DIR = 'logs/ai_feedback/';
    private const LOG_FILE = 'ai_feedback.log';

    /**
     * Initialize logger with AI feedback configuration
     */
    public static function init(string $logPath, ?int $maxFileSize = null, ?int $maxAgeDays = null): void
    {
        parent::init(
            $logPath,
            $maxFileSize ?? 1048576, // 1MB default
            $maxAgeDays ?? 30        // 30 day default
        );
    }

    /**
     * Initialize with default AI feedback log path
     */
    public static function initDefault(): void
    {
        self::init(self::LOG_DIR . self::LOG_FILE);
    }

    /**
     * Log AI feedback data in JSON format
     */
    public static function logFeedback(array $data): void
    {
        $logEntry = json_encode([
            'timestamp' => date('c'),
            'type' => $data['type'] ?? 'unknown',
            'session_id' => $data['session_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'model' => $data['model'] ?? null,
            'interaction_id' => $data['interaction_id'] ?? null,
            'data' => $data
        ], JSON_UNESCAPED_SLASHES);

        parent::log($logEntry);
    }

    /**
     * Get log path (public to match parent visibility)
     */
    /**
     * Get log path specific to AI feedback logs
     */
    public static function getLogPath(): string
    {
        return self::LOG_DIR . self::LOG_FILE;
    }

    /**
     * Get enhanced archive listing with metadata for admin interface
     */
    public static function getArchivedLogs(): array
    {
        $archives = [];
        $files = glob(self::LOG_DIR . '*.gz');
        
        foreach ($files as $file) {
            $archives[] = [
                'name' => basename($file),
                'size' => filesize($file),
                'modified' => date('c', filemtime($file))
            ];
        }
        
        return $archives;
    }
}
