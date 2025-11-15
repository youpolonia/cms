<?php
declare(strict_types=1);

class RollbackManager {
    private $dbConnection;
    private $versioningSystem;
    private $auditLogger;

    public function __construct() {
        $this->dbConnection = \core\Database::connection();
        $this->versioningSystem = ContentVersioningSystem::getInstance();
        $this->auditLogger = AuditLogger::getInstance();
    }

    public function restoreVersion(int $versionId, int $userId): bool {
        try {
            $this->dbConnection->beginTransaction();

            // Get version metadata
            $version = $this->versioningSystem->getVersionMetadata($versionId);
            if (!$version) {
                throw new Exception("Version not found");
            }

            // Create new version from current content
            $currentContent = $this->getCurrentContent($version['content_id']);
            $this->versioningSystem->createVersion(
                $version['content_id'],
                $userId,
                $currentContent,
                'Pre-restore backup'
            );

            // Restore the target version
            $restoredContent = $this->versioningSystem->getVersionContent($versionId);
            $this->updateContent($version['content_id'], $restoredContent);

            // Log the restoration
            $this->auditLogger->logAction(
                $userId,
                'version_restore',
                "Restored version {$versionId} of content {$version['content_id']}"
            );

            $this->dbConnection->commit();
            return true;
        } catch (Exception $e) {
            $this->dbConnection->rollBack();
            $this->auditLogger->logError(
                $userId,
                'version_restore_failed',
                "Failed to restore version {$versionId}: " . $e->getMessage()
            );
            return false;
        }
    }

    private function getCurrentContent(int $contentId): string {
        $stmt = $this->dbConnection->prepare(
            "SELECT content FROM contents WHERE content_id = ?"
        );
        $stmt->execute([$contentId]);
        return $stmt->fetchColumn();
    }

    private function updateContent(int $contentId, string $content): void {
        $stmt = $this->dbConnection->prepare(
            "UPDATE contents SET content = ? WHERE content_id = ?"
        );
        $stmt->execute([$content, $contentId]);
    }

    public function getRollbackCandidates(int $contentId, int $limit = 5): array {
        $stmt = $this->dbConnection->prepare(
            "SELECT v.*, u.username as author_name
            FROM content_versions v
            LEFT JOIN users u ON v.author_id = u.user_id
            WHERE v.content_id = ?
            ORDER BY v.created_at DESC
            LIMIT ?"
        );
        $stmt->execute([$contentId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
