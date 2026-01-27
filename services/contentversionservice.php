<?php

class ContentVersionService {
    private \PDO $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function createVersion(int $contentId, array $versionData, int $userId): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO content_versions 
            (content_id, version_data, created_by) 
            VALUES (:content_id, :version_data, :user_id)
        ");
        return $stmt->execute([
            ':content_id' => $contentId,
            ':version_data' => json_encode($versionData),
            ':user_id' => $userId
        ]);
    }

    public function detectConflicts(int $contentId, array $newVersion): array {
        // Get current versions
        $stmt = $this->pdo->prepare("
            SELECT id, version_data 
            FROM content_versions 
            WHERE content_id = :content_id AND is_current = 1
        ");
        $stmt->execute([':content_id' => $contentId]);
        $currentVersions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $conflicts = [];
        foreach ($currentVersions as $version) {
            $existingData = json_decode($version['version_data'], true);
            $diff = $this->compareVersions($existingData, $newVersion);
            if (!empty($diff)) {
                $conflicts[] = [
                    'version_id' => $version['id'],
                    'conflict_fields' => $diff
                ];
            }
        }

        if (!empty($conflicts)) {
            $this->recordConflict($contentId, $conflicts);
        }

        return $conflicts;
    }

    private function compareVersions(array $old, array $new): array {
        $diff = [];
        foreach ($new as $key => $value) {
            if (isset($old[$key]) && $old[$key] !== $value) {
                $diff[$key] = [
                    'old_value' => $old[$key],
                    'new_value' => $value
                ];
            }
        }
        return $diff;
    }

    private function recordConflict(int $contentId, array $conflicts): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO version_conflicts 
            (content_id, version_ids, conflict_data) 
            VALUES (:content_id, :version_ids, :conflict_data)
        ");
        return $stmt->execute([
            ':content_id' => $contentId,
            ':version_ids' => json_encode(array_column($conflicts, 'version_id')),
            ':conflict_data' => json_encode($conflicts)
        ]);
    }

    public function resolveConflict(int $conflictId, int $resolvingUserId): bool {
        $stmt = $this->pdo->prepare("
            UPDATE version_conflicts 
            SET resolved_at = NOW() 
            WHERE id = :conflict_id
        ");
        return $stmt->execute([':conflict_id' => $conflictId]);
    }

    public function createBranch(int $contentId, string $branchName, int $baseVersionId, int $userId): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO version_branches 
            (content_id, branch_name, base_version_id, head_version_id) 
            VALUES (:content_id, :branch_name, :base_version_id, :head_version_id)
        ");
        return $stmt->execute([
            ':content_id' => $contentId,
            ':branch_name' => $branchName,
            ':base_version_id' => $baseVersionId,
            ':head_version_id' => $baseVersionId
        ]);
    }
}
