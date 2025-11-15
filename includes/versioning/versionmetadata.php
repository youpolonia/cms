<?php
/**
 * Version Metadata Storage
 * 
 * Provides CRUD operations for version metadata in content_versions table
 * 
 * Metadata Fields:
 * - author_id: User who created the version (INT)
 * - change_notes: Description of changes (TEXT)
 * - content_type: Content format metadata (VARCHAR)
 * - tags: Custom labels/tags (JSON array)
 * - is_major_version: Flag for significant changes (BOOLEAN)
 */

require_once __DIR__.'/../core/database.php';

class VersionMetadata {
    private $db;
    private $inTransaction = false;

    public function __construct($db = null) {
        $this->db = $db ?: new Database();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): void
    {
        $this->db->beginTransaction();
        $this->inTransaction = true;
    }

    /**
     * Commit transaction
     */
    public function commit(): void
    {
        $this->db->commit();
        $this->inTransaction = false;
    }

    /**
     * Rollback transaction
     */
    public function rollback(): void
    {
        $this->db->rollback();
        $this->inTransaction = false;
    }

    /**
     * Check if in transaction
     */
    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }

    /**
     * Get all metadata for a version
     */
    public function getMetadata($versionId) {
        $query = "SELECT
                    version_id, change_notes, content_type,
                    tags, is_major_version
                  FROM version_metadata
                  WHERE version_id = ?";
        
        $result = $this->db->query($query, [$versionId]);
        return $result[0] ?? null;
    }

    /**
     * Update metadata for a version
     */
    public function updateMetadata($versionId, array $metadata) {
        $this->validateMetadata($metadata);

        $query = "UPDATE version_metadata SET
                    change_notes = ?,
                    content_type = ?,
                    tags = ?,
                    is_major_version = ?
                  WHERE version_id = ?";

        $params = [
            $metadata['change_notes'] ?? null,
            $metadata['content_type'] ?? null,
            json_encode($metadata['tags'] ?? []),
            $metadata['is_major_version'] ?? false,
            $versionId
        ];

        return $this->db->execute($query, $params);
    }

    /**
     * Validate metadata fields
     */
    private function validateMetadata(array $metadata) {
        if (isset($metadata['content_type'])) {
            if (strlen($metadata['content_type']) > 50) {
                throw new InvalidArgumentException("content_type must be <= 50 chars");
            }
        }

        if (isset($metadata['tags'])) {
            if (!is_array($metadata['tags'])) {
                throw new InvalidArgumentException("tags must be an array");
            }
        }
    }

    /**
     * Get versions by author
     */
    public function getVersionsByAuthor($authorId) {
        $query = "SELECT v.* FROM versions v
                  JOIN version_metadata vm ON v.id = vm.version_id
                  WHERE vm.author_id = ?
                  ORDER BY v.created_at DESC";
        return $this->db->query($query, [$authorId]);
    }

    /**
     * Get versions by content type
     */
    public function getVersionsByContentType($contentType) {
        $query = "SELECT v.* FROM versions v
                  JOIN version_metadata vm ON v.id = vm.version_id
                  WHERE vm.content_type = ?
                  ORDER BY v.created_at DESC";
        return $this->db->query($query, [$contentType]);
    }

    /**
     * Get versions with specific tag
     */
    public function getVersionsWithTag($tag) {
        $query = "SELECT * FROM content_versions 
                  WHERE JSON_CONTAINS(tags, ?)
                  ORDER BY created_at DESC";
        return $this->db->query($query, [json_encode($tag)]);
    }
}
