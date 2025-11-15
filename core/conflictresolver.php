<?php
class ConflictResolver {
    public static function detectConflicts(
        \PDO $pdo,
        int $contentId,
        string $newContent
    ): array {
        // Get current version
        $current = $pdo->prepare("
            SELECT cv.content_data, cvm.conflict_status
            FROM content_versions cv
            JOIN content_version_metadata cvm ON cv.id = cvm.version_id
            WHERE cv.content_id = ? AND cv.is_current = TRUE
        ");
        $current->execute([$contentId]);
        $currentVersion = $current->fetch(\PDO::FETCH_ASSOC);

        if (!$currentVersion) {
            return ['conflict' => false];
        }

        // Calculate diff
        $diff = DiffVisualizer::textDiff($currentVersion['content_data'], $newContent);
        $diffSize = count(array_filter($diff, fn($line) => $line['type'] !== 'unchanged'));

        return [
            'conflict' => $currentVersion['conflict_status'] !== 'none' || $diffSize > 50,
            'diff_size' => $diffSize,
            'diff_preview' => array_slice($diff, 0, 10)
        ];
    }

    public static function resolveConflict(
        \PDO $pdo,
        int $versionId,
        int $userId,
        string $resolutionStrategy,
        ?string $customContent = null
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

            // Apply resolution strategy
            $resolvedContent = match($resolutionStrategy) {
                'keep_current' => $version['content_data'],
                'use_incoming' => $customContent,
                'merge' => self::mergeContent($pdo, $version['content_id'], $customContent),
                default => throw new \Exception("Invalid resolution strategy")
            };

            // Update metadata
            $stmt = $pdo->prepare("
                UPDATE content_version_metadata
                SET conflict_status = 'resolved',
                    resolution_author_id = ?,
                    resolution_timestamp = NOW()
                WHERE version_id = ?
            ");
            $stmt->execute([$userId, $versionId]);

            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            error_log("Conflict resolution failed: " . $e->getMessage());
            return false;
        }
    }

    private static function mergeContent(\PDO $pdo, int $contentId, string $newContent): string {
        // Get current content
        $stmt = $pdo->prepare("
            SELECT content_data 
            FROM content_versions 
            WHERE content_id = ? AND is_current = TRUE
        ");
        $stmt->execute([$contentId]);
        $current = $stmt->fetchColumn();

        // Simple merge strategy - could be enhanced
        return $current . "\n\n--- MERGED CONTENT ---\n\n" . $newContent;
    }
}
