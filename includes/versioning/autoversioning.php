<?php
declare(strict_types=1);

class AutoVersioning {
    private $versioningSystem;
    private $dbConnection;
    private $config = [
        'auto_save_interval' => 300, // 5 minutes
        'max_versions_per_content' => 50,
        'min_changes_for_version' => 1
    ];

    public function __construct() {
        $this->versioningSystem = ContentVersioningSystem::getInstance();
        $this->dbConnection = \core\Database::connection();
    }

    public function configure(array $config): void {
        $this->config = array_merge($this->config, $config);
    }

    public function checkAndCreateVersion(int $contentId, string $currentContent, int $authorId): ?int {
        $lastVersion = $this->getLastVersion($contentId);
        
        if (!$this->shouldCreateVersion($contentId, $currentContent, $lastVersion)) {
            return null;
        }

        return $this->versioningSystem->createVersion(
            $contentId,
            $currentContent,
            [
                'author_id' => $authorId,
                'change_summary' => 'Auto-saved version'
            ]
        );
    }

    private function getLastVersion(int $contentId): ?array {
        $stmt = $this->dbConnection->prepare(
            "SELECT * FROM content_versions 
            WHERE content_id = ? 
            ORDER BY created_at DESC 
            LIMIT 1"
        );
        $stmt->execute([$contentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function shouldCreateVersion(int $contentId, string $currentContent, ?array $lastVersion): bool {
        // No previous version exists - always create first version
        if (!$lastVersion) {
            return true;
        }

        // Check time interval
        $lastVersionTime = strtotime($lastVersion['created_at']);
        if (time() - $lastVersionTime < $this->config['auto_save_interval']) {
            return false;
        }

        // Check content changes
        $lastContent = $this->versioningSystem->getVersionContent($lastVersion['version_id']);
        $changes = $this->countChanges($lastContent, $currentContent);
        if ($changes < $this->config['min_changes_for_version']) {
            return false;
        }

        // Check version count limit
        $versionCount = $this->getVersionCount($contentId);
        if ($versionCount >= $this->config['max_versions_per_content']) {
            $this->pruneOldVersions($contentId);
        }

        return true;
    }

    private function countChanges(string $oldContent, string $newContent): int {
        similar_text($oldContent, $newContent, $percent);
        return 100 - (int)$percent;
    }

    private function getVersionCount(int $contentId): int {
        $stmt = $this->dbConnection->prepare(
            "SELECT COUNT(*) FROM content_versions WHERE content_id = ?"
        );
        $stmt->execute([$contentId]);
        return (int)$stmt->fetchColumn();
    }

    private function pruneOldVersions(int $contentId): void {
        $keep = (int)($this->config['max_versions_per_content'] * 0.8);
        $stmt = $this->dbConnection->prepare(
            "DELETE FROM content_versions 
            WHERE content_id = ? 
            AND version_id NOT IN (
                SELECT version_id FROM content_versions 
                WHERE content_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?
            )"
        );
        $stmt->execute([$contentId, $contentId, $keep]);
    }

    public function cleanupOldContentVersions(int $days = 30): int {
        $stmt = $this->dbConnection->prepare(
            "DELETE FROM content_versions 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)"
        );
        $stmt->execute([$days]);
        return $stmt->rowCount();
    }
}
