<?php
declare(strict_types=1);

/**
 * Content Archival System - Phase 14 Implementation
 * Handles automated content expiration and version-preserving archival
 */
class ContentArchivalSystem {
    private \PDO $pdo;
    private Logger $logger;

    public function __construct(\PDO $pdo, Logger $logger) {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    /**
     * Archive expired content while preserving versions
     */
    public function archiveExpiredContent(): int {
        $this->logger->log('Starting content archival process');
        $archivedCount = 0;

        try {
            $this->pdo->beginTransaction();
            
            // Get expired content that needs archiving
            $expiredContent = $this->getExpiredContent();
            
            foreach ($expiredContent as $content) {
                if ($this->archiveContent($content['id'])) {
                    $archivedCount++;
                }
            }
            
            $this->pdo->commit();
            $this->logger->log("Archived $archivedCount content items");
            return $archivedCount;
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            $this->logger->error("Archival failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function getExpiredContent(): array {
        $stmt = $this->pdo->prepare(
            "SELECT id FROM content 
             WHERE expiration_date < NOW() 
             AND is_archived = 0"
        );
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function archiveContent(int $contentId): bool {
        try {
            // Get content title for audit log
            $title = $this->getContentTitle($contentId);
            
            // Preserve all versions before archiving
            $versions = $this->getContentVersions($contentId);
            
            // Move content to archive table
            $stmt = $this->pdo->prepare(
                "INSERT INTO content_archive
                 SELECT * FROM content WHERE id = :id"
            );
            $stmt->execute([':id' => $contentId]);
            
            // Mark original as archived
            $stmt = $this->pdo->prepare(
                "UPDATE content SET is_archived = 1
                 WHERE id = :id"
            );
            $stmt->execute([':id' => $contentId]);
            
            // Create audit log entry
            $this->createAuditLog(
                $contentId,
                $title,
                count($versions),
                'ARCHIVED'
            );
            
            // Log archival action
            $this->logger->log("Archived '$title' (ID $contentId) with " .
                count($versions) . " versions preserved");
                
            return true;
        } catch (\PDOException $e) {
            $this->logger->error("Failed to archive content $contentId: " . $e->getMessage());
            throw $e;
        }
    }

    private function getContentTitle(int $contentId): string {
        $stmt = $this->pdo->prepare(
            "SELECT title FROM content WHERE id = :id"
        );
        $stmt->execute([':id' => $contentId]);
        return $stmt->fetchColumn() ?: 'Untitled';
    }

    private function createAuditLog(
        int $contentId,
        string $title,
        int $versionCount,
        string $action
    ): void {
        $stmt = $this->pdo->prepare(
            "INSERT INTO content_audit_log
             (content_id, title, version_count, action, timestamp)
             VALUES (:content_id, :title, :version_count, :action, NOW())"
        );
        $stmt->execute([
            ':content_id' => $contentId,
            ':title' => $title,
            ':version_count' => $versionCount,
            ':action' => $action
        ]);
    }

    public function getContentVersions(int $contentId): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM content_versions
             WHERE content_id = :content_id"
        );
        $stmt->execute([':content_id' => $contentId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
