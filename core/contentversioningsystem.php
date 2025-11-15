<?php
class ContentVersioningSystem {
    public static function createVersion(
        \PDO $pdo,
        int $contentId,
        string $contentData,
        int $authorId,
        ?int $parentVersionId = null
    ): int {
        try {
            $pdo->beginTransaction();

            // Check for conflicts
            $conflictCheck = ConflictResolver::detectConflicts($pdo, $contentId, $contentData);
            $hasConflict = $conflictCheck['conflict'];

            // Mark current version as not current
            $pdo->prepare("
                UPDATE content_versions 
                SET is_current = FALSE 
                WHERE content_id = ? AND is_current = TRUE
            ")->execute([$contentId]);

            // Create new version
            $stmt = $pdo->prepare("
                INSERT INTO content_versions 
                (content_id, version_number, content_data, is_current)
                VALUES (?, 
                    COALESCE((SELECT MAX(version_number) FROM content_versions WHERE content_id = ?), 0) + 1,
                    ?, TRUE)
            ");
            $stmt->execute([$contentId, $contentId, $contentData]);
            $versionId = $pdo->lastInsertId();

            // Create version metadata
            $stmt = $pdo->prepare("
                INSERT INTO content_version_metadata
                (version_id, parent_version_id, conflict_status)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $versionId,
                $parentVersionId,
                $hasConflict ? 'detected' : 'none'
            ]);

            $pdo->commit();
            return $versionId;
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function rollbackVersion(
        \PDO $pdo,
        int $versionId,
        int $authorId
    ): bool {
        try {
            $pdo->beginTransaction();

            // Get version data
            $stmt = $pdo->prepare("
                SELECT content_id, content_data 
                FROM content_versions 
                WHERE id = ?
            ");
            $stmt->execute([$versionId]);
            $version = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$version) {
                throw new \Exception("Version not found");
            }

            // Create new version with rollback content
            $newVersionId = self::createVersion(
                $pdo,
                $version['content_id'],
                $version['content_data'],
                $authorId,
                $versionId
            );

            // Mark as rollback in metadata
            $pdo->prepare("
                UPDATE content_version_metadata
                SET is_rollback = TRUE,
                    rollback_author_id = ?,
                    rollback_timestamp = NOW()
                WHERE version_id = ?
            ")->execute([$authorId, $newVersionId]);

            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            error_log("Rollback failed: " . $e->getMessage());
            return false;
        }
    }

    public static function getVersionDiff(
        \PDO $pdo,
        int $versionId1,
        int $versionId2
    ): array {
        $stmt = $pdo->prepare("
            SELECT content_data FROM content_versions WHERE id IN (?, ?) ORDER BY id = ? DESC
        ");
        $stmt->execute([$versionId1, $versionId2, $versionId1]);
        $versions = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        if (count($versions) !== 2) {
            throw new \Exception("One or both versions not found");
        }

        return DiffVisualizer::textDiff($versions[0], $versions[1]);
    }

    public static function getVersionHistory(
        \PDO $pdo,
        int $contentId,
        int $limit = 10
    ): array {
        $stmt = $pdo->prepare("
            SELECT cv.id, cv.version_number, cv.created_at, 
                   cvm.conflict_status, cvm.resolution_author_id,
                   u.username as author_name
            FROM content_versions cv
            JOIN content_version_metadata cvm ON cv.id = cvm.version_id
            LEFT JOIN users u ON cvm.resolution_author_id = u.id
            WHERE cv.content_id = ?
            ORDER BY cv.version_number DESC
            LIMIT ?
        ");
        $stmt->execute([$contentId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
