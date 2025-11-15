<?php
/**
 * Version Manager for Deployment System
 * Handles version tracking and rollback functionality
 */
namespace CMS\Deployment;

class VersionManager {
    private $db;
    private $versionsTable = 'deployment_versions';
    private $filesTable = 'deployment_files';

    public function __construct(\PDO $db) {
        $this->db = $db;
        $this->ensureTablesExist();
    }

    /**
     * Create required tables if they don't exist
     */
    private function ensureTablesExist(): void {
        /* exec disabled: was $this->db->exec("
            CREATE TABLE IF NOT EXISTS {$this->versionsTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                version VARCHAR(50) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                notes TEXT,
                is_active BOOLEAN DEFAULT FALSE,
                UNIQUE KEY (version)
            )
        ") */

        /* exec disabled: was $this->db->exec("
            CREATE TABLE IF NOT EXISTS {$this->filesTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                version_id INT NOT NULL,
                file_path VARCHAR(255) NOT NULL,
                file_hash VARCHAR(64) NOT NULL,
                backup_path VARCHAR(255),
                FOREIGN KEY (version_id) REFERENCES {$this->versionsTable}(id) ON DELETE CASCADE,
                INDEX (file_path)
            )
        ") */
    }

    /**
     * Create new deployment version
     */
    public function createVersion(string $version, string $notes = ''): int {
        $this->db->beginTransaction();

        try {
            // Deactivate all other versions
            $stmt = $this->db->prepare("UPDATE {$this->versionsTable} SET is_active = FALSE");
            $stmt->execute();

            // Create new version
            $stmt = $this->db->prepare("
                INSERT INTO {$this->versionsTable} (version, notes, is_active)
                VALUES (:version, :notes, TRUE)
            ");
            $stmt->execute([
                ':version' => $version,
                ':notes' => $notes
            ]);

            $versionId = $this->db->lastInsertId();
            $this->db->commit();

            return $versionId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Record file changes for a version
     */
    public function recordFileChange(int $versionId, string $filePath, string $fileHash, string $backupPath = null): void {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->filesTable} (version_id, file_path, file_hash, backup_path)
            VALUES (:version_id, :file_path, :file_hash, :backup_path)
        ");
        $stmt->execute([
            ':version_id' => $versionId,
            ':file_path' => $filePath,
            ':file_hash' => $fileHash,
            ':backup_path' => $backupPath
        ]);
    }

    /**
     * Get list of all versions
     */
    public function getVersions(): array {
        $stmt = $this->db->query("
            SELECT id, version, created_at, notes, is_active
            FROM {$this->versionsTable}
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get files for a specific version
     */
    public function getVersionFiles(int $versionId): array {
        $stmt = $this->db->prepare("
            SELECT file_path, file_hash, backup_path
            FROM {$this->filesTable}
            WHERE version_id = :version_id
        ");
        $stmt->execute([':version_id' => $versionId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Rollback to a specific version
     */
    public function rollbackToVersion(int $versionId): bool {
        $files = $this->getVersionFiles($versionId);
        $success = true;

        foreach ($files as $file) {
            if (!empty($file['backup_path']) && file_exists($file['backup_path'])) {
                $success = $success && copy($file['backup_path'], $file['file_path']);
            }
        }

        if ($success) {
            $this->activateVersion($versionId);
        }

        return $success;
    }

    /**
     * Activate a specific version
     */
    private function activateVersion(int $versionId): void {
        $this->db->beginTransaction();

        try {
            // Deactivate all versions
            $stmt = $this->db->prepare("UPDATE {$this->versionsTable} SET is_active = FALSE");
            $stmt->execute();

            // Activate specified version
            $stmt = $this->db->prepare("
                UPDATE {$this->versionsTable} 
                SET is_active = TRUE
                WHERE id = :version_id
            ");
            $stmt->execute([':version_id' => $versionId]);

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get current active version
     */
    public function getActiveVersion(): ?array {
        $stmt = $this->db->query("
            SELECT id, version, created_at, notes
            FROM {$this->versionsTable}
            WHERE is_active = TRUE
            LIMIT 1
        ");
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Generate SHA256 hash of file contents
     */
    public function hashFile(string $filePath): string {
        return hash_file('sha256', $filePath);
    }
}
