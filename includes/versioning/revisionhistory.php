<?php
declare(strict_types=1);

class RevisionHistory {
    private $dbConnection;
    private $versioningSystem;

    public function __construct() {
        $this->dbConnection = \core\Database::connection();
        $this->versioningSystem = ContentVersioningSystem::getInstance();
    }

    public function getHistoryForContent(int $contentId, int $limit = 10): array {
        $stmt = $this->dbConnection->prepare(
            "SELECT v.*, u.username as author_name 
            FROM content_versions v
            LEFT JOIN users u ON v.author_id = u.user_id
            WHERE v.content_id = ?
            ORDER BY v.created_at DESC
            LIMIT ?"
        );
        $stmt->execute([$contentId, $limit]);
        
        $versions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->enrichVersionData($versions);
    }

    public function getVersionDetails(int $versionId): array {
        $metadata = $this->versioningSystem->getVersionMetadata($versionId);
        $content = $this->versioningSystem->getVersionContent($versionId);
        
        return [
            'metadata' => $metadata,
            'content' => $content,
            'size' => strlen($content),
            'lines' => substr_count($content, "\n") + 1
        ];
    }

    public function getVersionCount(int $contentId): int {
        $stmt = $this->dbConnection->prepare(
            "SELECT COUNT(*) FROM content_versions WHERE content_id = ?"
        );
        $stmt->execute([$contentId]);
        return (int)$stmt->fetchColumn();
    }

    public function getVersionTimeline(int $contentId): array {
        $stmt = $this->dbConnection->prepare(
            "SELECT 
                version_id,
                DATE(created_at) as date,
                COUNT(*) as versions_count,
                MIN(created_at) as first_version_time,
                MAX(created_at) as last_version_time
            FROM content_versions
            WHERE content_id = ?
            GROUP BY DATE(created_at)
            ORDER BY date DESC"
        );
        $stmt->execute([$contentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchVersions(string $query, int $contentId = 0): array {
        $sql = "SELECT v.* FROM content_versions v
                JOIN (
                    SELECT version_id FROM content_versions
                    WHERE content_id = IF(? = 0, content_id, ?)
                ) AS filtered ON v.version_id = filtered.version_id
                WHERE v.change_summary LIKE ?";
        
        $stmt = $this->dbConnection->prepare($sql);
        $searchTerm = "%{$query}%";
        $stmt->execute([$contentId, $contentId, $searchTerm]);
        
        $versions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->enrichVersionData($versions);
    }

    private function enrichVersionData(array $versions): array {
        return array_map(function($version) {
            $version['content_preview'] = $this->getContentPreview(
                $this->versioningSystem->getVersionContent($version['version_id'])
            );
            return $version;
        }, $versions);
    }

    private function getContentPreview(string $content): string {
        $lines = explode("\n", $content);
        $firstLines = array_slice($lines, 0, 3);
        return implode("\n", $firstLines);
    }
}
