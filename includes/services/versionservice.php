<?php

namespace Includes\Services;

use Includes\Database\Database;

class VersionService {
    public function createVersion(int $contentId, string $content, array $metadata = []): int {
        Database::execute(
            "INSERT INTO versions (content_id, content) VALUES (?, ?)",
            [$contentId, $content]
        );
        
        $versionId = Database::getLastInsertId();
        
        Database::execute(
            "INSERT INTO version_metadata (version_id, change_notes, content_type, tags, is_major_version) 
             VALUES (?, ?, ?, ?, ?)",
            [
                $versionId,
                $metadata['change_notes'] ?? null,
                $metadata['content_type'] ?? null,
                isset($metadata['tags']) ? json_encode($metadata['tags']) : null,
                $metadata['is_major_version'] ?? false
            ]
        );
        
        return $versionId;
    }

    public function getVersions(int $contentId): array {
        return Database::query(
            "SELECT v.id, v.created_at, v.content, 
                    vm.change_notes, vm.content_type, vm.tags, vm.is_major_version
             FROM versions v
             LEFT JOIN version_metadata vm ON v.id = vm.version_id
             WHERE v.content_id = ?
             ORDER BY v.created_at DESC",
            [$contentId]
        );
    }

    public function getVersion(int $versionId): ?array {
        $result = Database::query(
            "SELECT v.id, v.content_id, v.content, v.created_at,
                    vm.change_notes, vm.content_type, vm.tags, vm.is_major_version
             FROM versions v
             LEFT JOIN version_metadata vm ON v.id = vm.version_id
             WHERE v.id = ?",
            [$versionId]
        );
        
        return $result[0] ?? null;
    }

    public function deleteVersion(int $versionId): bool {
        Database::execute(
            "DELETE FROM version_metadata WHERE version_id = ?",
            [$versionId]
        );
        
        return Database::execute(
            "DELETE FROM versions WHERE id = ?",
            [$versionId]
        );
    }

    public function deleteVersions(array $versionIds): bool {
        $placeholders = implode(',', array_fill(0, count($versionIds), '?'));
        
        Database::execute(
            "DELETE FROM version_metadata WHERE version_id IN ($placeholders)",
            $versionIds
        );
        
        return Database::execute(
            "DELETE FROM versions WHERE id IN ($placeholders)",
            $versionIds
        );
    }
}
