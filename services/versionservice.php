<?php
/**
 * Version Service - Handles content versioning operations
 */
class VersionService {
    private \PDO $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Creates new version of content
     */
    public function createVersion(
        int $contentId,
        string $contentData,
        int $userId,
        string $changeSummary = ''
    ): int {
        try {
            $this->pdo->beginTransaction();

            // Get next version number
            $stmt = $this->pdo->prepare(
                "SELECT MAX(version_number) FROM content_versions WHERE content_id = ?"
            );
            $stmt->execute([$contentId]);
            $nextVersion = (int)$stmt->fetchColumn() + 1;

            // Insert new version
            $stmt = $this->pdo->prepare(
                "INSERT INTO content_versions 
                (content_id, version_number, content_data, created_by, change_summary)
                VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $contentId,
                $nextVersion,
                $contentData,
                $userId,
                $changeSummary
            ]);

            $this->pdo->commit();
            return $nextVersion;
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            error_log("Version creation failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gets specific version of content
     */
    public function getVersion(int $contentId, int $versionNumber): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM content_versions 
            WHERE content_id = ? AND version_number = ?"
        );
        $stmt->execute([$contentId, $versionNumber]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Lists all versions for content
     */
    public function listVersions(int $contentId, int $limit = 10): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM content_versions 
            WHERE content_id = ? 
            ORDER BY version_number DESC 
            LIMIT ?"
        );
        $stmt->execute([$contentId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Rolls back to specific version
     */
    public function rollbackToVersion(int $contentId, int $versionNumber): bool {
        $version = $this->getVersion($contentId, $versionNumber);
        if (!$version) {
            throw new \InvalidArgumentException("Version not found");
        }

        // In practice, this would call ContentService to update the content
        // For now just return the version data
        return true;
    }
}
