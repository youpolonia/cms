<?php

namespace Includes\Services;

use Includes\Database\Database;

class ContentService {
    public function getContent(int $contentId): ?array {
        $result = Database::query(
            "SELECT * FROM contents WHERE id = ?",
            [$contentId]
        );
        return $result[0] ?? null;
    }

    public function updateContent(int $contentId, string $content): bool {
        return Database::execute(
            "UPDATE contents SET content = ?, updated_at = NOW() WHERE id = ?",
            [$content, $contentId]
        );
    }

    public function createContent(string $content): int {
        Database::execute(
            "INSERT INTO contents (content, status) VALUES (?, 'draft')",
            [$content]
        );
        return Database::getLastInsertId();
    }

    public function publishContent(int $contentId): bool {
        try {
            Database::beginTransaction();
            
            $content = $this->getContent($contentId);
            if (!$content) {
                throw new \RuntimeException("Content not found");
            }

            $result = Database::execute(
                "UPDATE contents SET status = 'published', published_at = NOW() WHERE id = ?",
                [$contentId]
            );

            Database::execute(
                "INSERT INTO status_transitions (content_id, from_status, to_status, transition_time)
                 VALUES (?, ?, ?, NOW())",
                [$contentId, $content['status'], 'published']
            );

            Database::commit();
            return $result;
        } catch (\Exception $e) {
            Database::rollBack();
            error_log("Publish failed: " . $e->getMessage());
            return false;
        }
    }

    public function unpublishContent(int $contentId): bool {
        try {
            Database::beginTransaction();
            
            $content = $this->getContent($contentId);
            if (!$content) {
                throw new \RuntimeException("Content not found");
            }

            $result = Database::execute(
                "UPDATE contents SET status = 'draft', published_at = NULL WHERE id = ?",
                [$contentId]
            );

            Database::execute(
                "INSERT INTO status_transitions (content_id, from_status, to_status, transition_time)
                 VALUES (?, ?, ?, NOW())",
                [$contentId, $content['status'], 'draft']
            );

            Database::commit();
            return $result;
        } catch (\Exception $e) {
            Database::rollBack();
            error_log("Unpublish failed: " . $e->getMessage());
            return false;
        }
    }
}
