<?php
class IndexBuilder {
    private \PDO $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function indexContent(array $content): bool {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                INSERT INTO search_index (
                    tenant_id,
                    content_id,
                    version_id,
                    title,
                    content,
                    metadata,
                    created_at,
                    updated_at,
                    metadata_updated_at
                ) VALUES (
                    :tenant_id,
                    :content_id,
                    :version_id,
                    :title,
                    :content,
                    :metadata,
                    :created_at,
                    :updated_at,
                    :metadata_updated_at
                ) ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    content = VALUES(content),
                    metadata = VALUES(metadata),
                    last_indexed = CURRENT_TIMESTAMP,
                    updated_at = VALUES(updated_at),
                    metadata_updated_at = VALUES(metadata_updated_at)
            ");

            foreach ($content as $item) {
                $stmt->execute([
                    ':tenant_id' => $item['tenant_id'],
                    ':content_id' => $item['content_id'],
                    ':version_id' => $item['version_id'],
                    ':title' => $item['title'],
                    ':content' => $this->normalizeContent($item['content']),
                    ':metadata' => json_encode($item['metadata'] ?? []),
                    ':created_at' => $item['created_at'] ?? date('Y-m-d H:i:s'),
                    ':updated_at' => $item['updated_at'] ?? date('Y-m-d H:i:s'),
                    ':metadata_updated_at' => !empty($item['metadata']) ? date('Y-m-d H:i:s') : null
                ]);
            }

            $this->pdo->commit();
            return true;
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            error_log("Indexing failed: " . $e->getMessage());
            return false;
        }
    }

    private function normalizeContent(string $content): string {
        $content = strip_tags($content);
        $content = html_entity_decode($content);
        return trim($content);
    }
}
