<?php
/**
 * SEO Feedback Manager
 * Tracks changes to SEO elements and provides AI feedback loop
 */
class FeedbackManager {
    private const LOG_DIR = __DIR__ . '/../../../logs/ai_feedback/';
    private const HISTORY_LIMIT = 50;

    /**
     * Track SEO change with feedback data
     * @param string $type Change type (title|description|canonical|slug)
     * @param array $oldValues Previous values
     * @param array $newValues New values
     * @param int $userId Editor ID
     * @param string|null $feedback AI feedback (optional)
     * @return bool Success status
     */
    public function trackChange(
        string $type,
        array $oldValues,
        array $newValues,
        int $userId,
        ?string $feedback = null
    ): bool {
        if (!file_exists(self::LOG_DIR)) {
            mkdir(self::LOG_DIR, 0755, true);
        }

        $logFile = self::LOG_DIR . date('Y-m-d') . '.json';
        $logData = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];

        $entry = [
            'timestamp' => time(),
            'type' => $type,
            'old' => $oldValues,
            'new' => $newValues,
            'user_id' => $userId,
            'feedback' => $feedback,
            'session_id' => session_id()
        ];

        array_unshift($logData, $entry);
        $logData = array_slice($logData, 0, self::HISTORY_LIMIT);

        return file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT)) !== false;
    }

    /**
     * Get recent changes for analysis
     * @param int $limit Number of entries to return
     * @return array Array of change entries
     */
    public function getRecentChanges(int $limit = 10): array {
        $files = glob(self::LOG_DIR . '*.json');
        rsort($files);
        
        $changes = [];
        foreach ($files as $file) {
            $fileData = json_decode(file_get_contents($file), true);
            $changes = array_merge($changes, $fileData);
            if (count($changes) >= $limit) {
                break;
            }
        }
        
        return array_slice($changes, 0, $limit);
    }

    /**
     * Validate user has permission to edit SEO
     * @param int $userId
     * @return bool
     */
    public function validateUser(int $userId): bool {
        // TODO: Implement role check
        return $userId > 0;
    }
}
