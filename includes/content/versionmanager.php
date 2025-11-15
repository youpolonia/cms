<?php

declare(strict_types=1);

namespace Includes\Content;

use PDO;
use PDOException;
use DiffMatchPatch\DiffMatchPatch; // Assuming a diff library like google/diff-match-patch might be used

/**
 * Manages content versions.
 */
class VersionManager
{
    private PDO $pdo;

    /**
     * Constructor.
     *
     * @param PDO $pdo The database connection.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Creates a new version for a piece of content.
     *
     * @param int $contentId The ID of the content item.
     * @param string $contentData The actual content data (e.g., JSON string, HTML).
     * @param int|null $userId The ID of the user creating this version (optional).
     * @param string|null $notes Optional notes for this version.
     * @return int|false The ID of the newly created version record, or false on failure.
     */
    public function createVersion(int $contentId, string $contentData, ?int $userId = null, ?string $notes = null): int|false
    {
        try {
            $this->pdo->beginTransaction();

            // Get the next version number
            $stmtVersion = $this->pdo->prepare("SELECT MAX(version_number) FROM content_versions WHERE content_id = :content_id");
            $stmtVersion->execute([':content_id' => $contentId]);
            $maxVersion = $stmtVersion->fetchColumn();
            $nextVersionNumber = ($maxVersion === false || $maxVersion === null) ? 1 : (int)$maxVersion + 1;

            $stmt = $this->pdo->prepare(
                "INSERT INTO content_versions (content_id, version_number, version_data, user_id, notes, created_at, updated_at)
                 VALUES (:content_id, :version_number, :version_data, :user_id, :notes, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)"
            );

            $success = $stmt->execute([
                ':content_id' => $contentId,
                ':version_number' => $nextVersionNumber,
                ':version_data' => $contentData,
                ':user_id' => $userId,
                ':notes' => $notes,
            ]);

            if ($success) {
                $versionId = (int)$this->pdo->lastInsertId();
                $this->pdo->commit();
                return $versionId;
            }

            $this->pdo->rollBack();
            return false;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            // Log error: $e->getMessage()
            return false;
        }
    }

    /**
     * Retrieves a specific version of a content item.
     *
     * @param int $contentId The ID of the content item.
     * @param int $versionNumber The specific version number to retrieve.
     * @return array|null The version data as an associative array, or null if not found.
     */
    public function getVersion(int $contentId, int $versionNumber): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM content_versions WHERE content_id = :content_id AND version_number = :version_number");
            $stmt->execute([':content_id' => $contentId, ':version_number' => $versionNumber]);
            $version = $stmt->fetch(PDO::FETCH_ASSOC);
            return $version ?: null;
        } catch (PDOException $e) {
            // Log error
            return null;
        }
    }

    /**
     * Retrieves the latest version of a content item.
     *
     * @param int $contentId The ID of the content item.
     * @return array|null The latest version data, or null if no versions exist.
     */
    public function getLatestVersion(int $contentId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM content_versions WHERE content_id = :content_id ORDER BY version_number DESC LIMIT 1");
            $stmt->execute([':content_id' => $contentId]);
            $version = $stmt->fetch(PDO::FETCH_ASSOC);
            return $version ?: null;
        } catch (PDOException $e) {
            // Log error
            return null;
        }
    }

    /**
     * Retrieves all versions for a specific content item, ordered by version number (descending).
     *
     * @param int $contentId The ID of the content item.
     * @param int $limit Number of versions to retrieve.
     * @param int $offset Offset for pagination.
     * @return array An array of version data.
     */
    public function getAllVersions(int $contentId, int $limit = 50, int $offset = 0): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT id, content_id, version_number, user_id, notes, created_at 
                 FROM content_versions 
                 WHERE content_id = :content_id 
                 ORDER BY version_number DESC
                 LIMIT :limit OFFSET :offset"
            );
            $stmt->bindValue(':content_id', $contentId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log error
            return [];
        }
    }

    /**
     * Counts the total number of versions for a content item.
     * @param int $contentId
     * @return int
     */
    public function countVersions(int $contentId): int
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM content_versions WHERE content_id = :content_id");
            $stmt->execute([':content_id' => $contentId]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            // Log error
            return 0;
        }
    }


    /**
     * Compares two versions of a content item and returns the differences.
     * This is a placeholder and would require a proper diffing library/algorithm.
     *
     * @param int $contentId The ID of the content item.
     * @param int $versionNumber1 The first version number.
     * @param int $versionNumber2 The second version number.
     * @return string|array|null A representation of the diff, or null if versions not found.
     *                           The format depends on the chosen diffing strategy.
     */
    public function compareVersions(int $contentId, int $versionNumber1, int $versionNumber2): mixed
    {
        $version1 = $this->getVersion($contentId, $versionNumber1);
        $version2 = $this->getVersion($contentId, $versionNumber2);

        if (!$version1 || !$version2) {
            return null; // One or both versions not found
        }

        $data1 = $version1['version_data'];
        $data2 = $version2['version_data'];

        // Basic string comparison for now.
        // For a real implementation, use a library like google/diff-match-patch
        // or a custom solution for structured data (e.g., JSON diff).
        if (class_exists(DiffMatchPatch::class)) {
            $dmp = new DiffMatchPatch();
            $diffs = $dmp->diff_main($data1, $data2);
            // $dmp->diff_cleanupSemantic($diffs); // Optional: make diffs more human-readable
            // return $dmp->diff_prettyHtml($diffs); // Example: HTML output
            return $diffs; // Raw diff array
        }

        // Fallback simple diff
        if ($data1 === $data2) {
            return "No differences found.";
        } else {
            // This is a very naive diff.
            // Consider implementing a more sophisticated line-by-line or word-by-word diff if a library is not available.
            return [
                "message" => "Content differs. Advanced diffing not available without a library.",
                "version1_length" => strlen($data1),
                "version2_length" => strlen($data2)
            ];
        }
    }

    /**
     * Reverts content to a specific version.
     * This typically means creating a NEW version that is a copy of the old version's data.
     * It does not usually mean deleting intermediate versions.
     *
     * @param int $contentId The ID of the content item.
     * @param int $versionNumberToRevertTo The version number to revert to.
     * @param int|null $userId The ID of the user performing the revert.
     * @param string|null $notes Notes for the new version created by the revert.
     * @return int|false The ID of the new version created by the revert, or false on failure.
     */
    public function revertToVersion(int $contentId, int $versionNumberToRevertTo, ?int $userId = null, ?string $notes = null): int|false
    {
        $versionToRevert = $this->getVersion($contentId, $versionNumberToRevertTo);

        if (!$versionToRevert) {
            return false; // Version to revert to not found
        }

        $revertNotes = "Reverted to version {$versionNumberToRevertTo}.";
        if ($notes) {
            $revertNotes .= " User notes: " . $notes;
        }

        return $this->createVersion(
            $contentId,
            $versionToRevert['version_data'],
            $userId,
            $revertNotes
        );
    }
}
