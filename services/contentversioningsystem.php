<?php
declare(strict_types=1);

/**
 * Content Versioning System
 * 
 * Handles version control for content with tenant isolation
 * and integration with AuthService and NotificationService
 */
class ContentVersioningSystem {
    private static ?ContentVersioningSystem $instance = null;
    private PDO $pdo;

    private function __construct() {
        $this->pdo = \core\Database::connection();
    }

    public static function getInstance(): ContentVersioningSystem {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Create new content version
     */
    public function createVersion(
        int $contentId,
        array $data,
        int $authorId,
        string $tenantId,
        bool $isAutosave = false
    ): int {
        $auth = AuthService::getInstance();
        if (!$auth->checkPermission($authorId, 'content_version_create')) {
            throw new Exception('Permission denied');
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO content_versions (
                content_id,
                version_number,
                data,
                is_autosave,
                author_id,
                tenant_id,
                created_at
            ) VALUES (
                :content_id,
                (SELECT COALESCE(MAX(version_number), 0) + 1 
                 FROM content_versions 
                 WHERE content_id = :content_id AND tenant_id = :tenant_id),
                :data,
                :is_autosave,
                :author_id,
                :tenant_id,
                NOW()
            )
        ");

        $stmt->execute([
            ':content_id' => $contentId,
            ':data' => json_encode($data),
            ':is_autosave' => $isAutosave ? 1 : 0,
            ':author_id' => $authorId,
            ':tenant_id' => $tenantId
        ]);

        $versionId = (int)$this->pdo->lastInsertId();

        // Send notification
        NotificationService::getInstance()->sendNotification('version_create', [
            'content_id' => $contentId,
            'version_id' => $versionId,
            'author_id' => $authorId
        ]);

        return $versionId;
    }

    /**
     * Get version by ID with tenant validation
     */
    public function getVersion(int $versionId, string $tenantId): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM content_versions 
            WHERE id = :id AND tenant_id = :tenant_id
        ");
        $stmt->execute([':id' => $versionId, ':tenant_id' => $tenantId]);
        $version = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$version) {
            throw new Exception('Version not found');
        }

        $version['data'] = json_decode($version['data'], true);
        return $version;
    }

    /**
     * List versions for content with tenant validation
     */
    public function listVersions(int $contentId, string $tenantId): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM content_versions 
            WHERE content_id = :content_id AND tenant_id = :tenant_id
            ORDER BY version_number DESC
        ");
        $stmt->execute([':content_id' => $contentId, ':tenant_id' => $tenantId]);

        $versions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(function($version) {
            $version['data'] = json_decode($version['data'], true);
            return $version;
        }, $versions);
    }

    /**
     * Restore content version
     */
    public function restoreVersion(int $versionId, int $authorId, string $tenantId): int {
        $auth = AuthService::getInstance();
        if (!$auth->checkPermission($authorId, 'content_version_restore')) {
            throw new Exception('Permission denied');
        }

        $version = $this->getVersion($versionId, $tenantId);

        // Create new version with restored data
        $newVersionId = $this->createVersion(
            $version['content_id'],
            $version['data'],
            $authorId,
            $tenantId,
            false
        );

        // Send notification
        NotificationService::getInstance()->sendNotification('version_restore', [
            'content_id' => $version['content_id'],
            'version_id' => $versionId,
            'new_version_id' => $newVersionId,
            'author_id' => $authorId
        ]);

        return $newVersionId;
    }

    /**
     * Delete version with tenant validation
     */
    public function deleteVersion(int $versionId, int $authorId, string $tenantId): bool {
        $auth = AuthService::getInstance();
        if (!$auth->checkPermission($authorId, 'content_version_delete')) {
            throw new Exception('Permission denied');
        }

        $version = $this->getVersion($versionId, $tenantId);

        $stmt = $this->pdo->prepare("
            DELETE FROM content_versions 
            WHERE id = :id AND tenant_id = :tenant_id
        ");
        $result = $stmt->execute([':id' => $versionId, ':tenant_id' => $tenantId]);

        if ($result) {
            NotificationService::getInstance()->sendNotification('version_delete', [
                'content_id' => $version['content_id'],
                'version_id' => $versionId,
                'author_id' => $authorId
            ]);
        }

        return $result;
    }
}
