<?php
/**
 * Content Versioning Service
 * 
 * Manages content version history including:
 * - Version creation
 * - Version comparison
 * - Version restoration
 * - Version cleanup
 */
class ContentVersioner {
    private static $instance;
    private $pdo;

    private function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public static function getInstance(\PDO $pdo): self {
        if (!self::$instance) {
            self::$instance = new self($pdo);
        }
        return self::$instance;
    }

    public function createVersion(int $contentId, array $data, int $authorId, bool $isAutosave = false): bool {
        try {
            $this->pdo->beginTransaction();
            
            // Get next version number
            $versionNumber = $this->getNextVersionNumber($contentId);
            
            $stmt = $this->pdo->prepare(
                "INSERT INTO content_versions 
                (content_id, version_data, version_number, author_id, is_autosave)
                VALUES (:content_id, :version_data, :version_number, :author_id, :is_autosave)"
            );
            
            $stmt->execute([
                ':content_id' => $contentId,
                ':version_data' => json_encode($data),
                ':version_number' => $versionNumber,
                ':author_id' => $authorId,
                ':is_autosave' => $isAutosave
            ]);
            
            $this->pdo->commit();
            return true;
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            error_log("Version creation failed: " . $e->getMessage());
            return false;
        }
    }

    public function getVersions(int $contentId, int $limit = 10): array {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM content_versions 
                WHERE content_id = :content_id
                ORDER BY created_at DESC
                LIMIT :limit"
            );
            $stmt->bindValue(':content_id', $contentId, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Version retrieval failed: " . $e->getMessage());
            return [];
        }
    }

    public function restoreVersion(int $versionId, int $restoredBy): bool {
        try {
            $this->pdo->beginTransaction();
            
            // Get version data
            $stmt = $this->pdo->prepare(
                "SELECT content_id, version_data FROM content_versions 
                WHERE id = :version_id"
            );
            $stmt->execute([':version_id' => $versionId]);
            $version = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$version) {
                throw new \Exception("Version not found");
            }
            
            // Update restored timestamp
            $updateStmt = $this->pdo->prepare(
                "UPDATE content_versions 
                SET restored_at = CURRENT_TIMESTAMP, restored_by = :restored_by
                WHERE id = :version_id"
            );
            $updateStmt->execute([
                ':version_id' => $versionId,
                ':restored_by' => $restoredBy
            ]);
            
            $this->pdo->commit();
            return $version;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Version restoration failed: " . $e->getMessage());
            return false;
        }
    }

    private function getNextVersionNumber(int $contentId): string {
        $stmt = $this->pdo->prepare(
            "SELECT version_number FROM content_versions 
            WHERE content_id = :content_id
            ORDER BY created_at DESC
            LIMIT 1"
        );
        $stmt->execute([':content_id' => $contentId]);
        $lastVersion = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$lastVersion) {
            return "1.0.0";
        }
        
        $parts = explode('.', $lastVersion['version_number']);
        $parts[count($parts)-1]++;
        return implode('.', $parts);
    }

    public function cleanupOldVersions(int $contentId, int $keep = 5): bool {
        try {
            $this->pdo->beginTransaction();
            
            // Get IDs of versions to keep
            $stmt = $this->pdo->prepare(
                "SELECT id FROM content_versions 
                WHERE content_id = :content_id
                ORDER BY created_at DESC
                LIMIT :keep"
            );
            $stmt->bindValue(':content_id', $contentId, \PDO::PARAM_INT);
            $stmt->bindValue(':keep', $keep, \PDO::PARAM_INT);
            $stmt->execute();
            $keepIds = array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'id');
            
            // Delete old versions
            $deleteStmt = $this->pdo->prepare(
                "DELETE FROM content_versions 
                WHERE content_id = :content_id
                AND id NOT IN (" . implode(',', array_fill(0, count($keepIds), '?')) . ")"
            );
            $deleteStmt->execute($keepIds);
            
            $this->pdo->commit();
            return true;
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            error_log("Version cleanup failed: " . $e->getMessage());
            return false;
        }
    }
}
