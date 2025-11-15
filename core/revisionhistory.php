<?php
declare(strict_types=1);

/**
 * Revision History - Tracks and displays detailed content revision history
 */
class RevisionHistory {
    private ContentVersioningSystem $versioningSystem;
    private string $historyStoragePath = __DIR__ . '/../storage/revision_history/';

    public function __construct() {
        $this->versioningSystem = ContentVersioningSystem::getInstance();
        $this->ensureHistoryDirectory();
    }

    private function ensureHistoryDirectory(): void {
        if (!is_dir($this->historyStoragePath)) {
            mkdir($this->historyStoragePath, 0755, true);
        }
    }

    public function logRevisionAction(
        int $contentId,
        string $action,
        string $userId,
        ?string $versionId = null,
        ?string $notes = null
    ): void {
        $logEntry = [
            'timestamp' => time(),
            'content_id' => $contentId,
            'action' => $action,
            'user_id' => $userId,
            'version_id' => $versionId,
            'notes' => $notes
        ];

        $logFile = $this->historyStoragePath . date('Y-m-d') . '.log';
        file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND);
    }

    public function getRevisionHistory(int $contentId, int $limit = 50): array {
        $history = [];
        $files = glob($this->historyStoragePath . '*.log');
        
        foreach ($files as $file) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $entry = json_decode($line, true);
                if ($entry['content_id'] === $contentId) {
                    $history[] = $entry;
                }
            }
        }

        usort($history, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
        return array_slice($history, 0, $limit);
    }

    public function getVersionHistory(int $contentId): array {
        $versions = $this->versioningSystem->listVersions($contentId);
        $history = $this->getRevisionHistory($contentId);
        
        return array_map(function($version) use ($history) {
            $version['actions'] = array_filter($history, 
                fn($entry) => $entry['version_id'] === $version['version_id']);
            return $version;
        }, $versions);
    }
}
