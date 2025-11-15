<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/database.php';

class ContentRepository {
    private PDO $db;

    public function __construct() {
        $this->db = \core\Database::connection();
    }

    public function getContentById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT c.*, 
                   v.version_number as current_version,
                   (SELECT COUNT(*) FROM content_versions WHERE content_id = c.id) as total_versions
            FROM contents c
            LEFT JOIN content_versions v ON c.current_version_id = v.id
            WHERE c.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getContentVersions(int $contentId): array {
        $stmt = $this->db->prepare("
            SELECT v.*, u.username as author_name
            FROM content_versions v
            LEFT JOIN users u ON v.author_id = u.id
            WHERE v.content_id = :content_id
            ORDER BY v.created_at DESC
        ");
        $stmt->execute([':content_id' => $contentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function compareVersions(int $contentId, int $version1Id, int $version2Id): array {
        $stmt = $this->db->prepare("
            SELECT 
                v1.content as version1_content,
                v2.content as version2_content
            FROM content_versions v1
            JOIN content_versions v2 ON v1.content_id = v2.content_id
            WHERE v1.id = :version1_id AND v2.id = :version2_id
            AND v1.content_id = :content_id
        ");
        $stmt->execute([
            ':version1_id' => $version1Id,
            ':version2_id' => $version2Id,
            ':content_id' => $contentId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateContentState(int $contentId, string $newState): bool {
        $stmt = $this->db->prepare("
            UPDATE contents 
            SET lifecycle_state = :state 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $contentId,
            ':state' => $newState
        ]);
    }

    public function getPossibleStates(string $currentState): array {
        // Define state transitions
        $transitions = [
            'draft' => ['pending_review', 'published', 'archived'],
            'pending_review' => ['published', 'draft', 'archived'],
            'published' => ['archived', 'draft'],
            'archived' => ['draft'],
            'scheduled' => ['published', 'draft', 'archived']
        ];

        return $transitions[$currentState] ?? [];
    }

    public function updateContent(int $contentId, array $data): bool {
        $allowedFields = ['title', 'content', 'lifecycle_state', 'access_level'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (empty($updateData)) {
            return false;
        }

        $setParts = [];
        $params = [':id' => $contentId];
        
        foreach ($updateData as $field => $value) {
            $setParts[] = "$field = :$field";
            $params[":$field"] = $value;
        }

        $sql = "UPDATE contents SET " . implode(', ', $setParts) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($params);
    }
}
