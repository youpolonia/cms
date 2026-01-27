<?php
require_once __DIR__ . '/../core/database.php';

declare(strict_types=1);

class IndexBuilder {
    private static ?IndexBuilder $instance = null;
    private array $index = [];

    private function __construct() {
        $this->rebuildIndex();
    }

    public static function getInstance(): IndexBuilder {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function rebuildIndex(): void {
        $this->index = [];
        
        // Query published content directly from database
        $pdo = \core\Database::connection();
        
        $query = "SELECT id, title, content, tags
                 FROM contents
                 WHERE tenant_id = :tenant_id
                 AND status = 'published'
                 AND deleted_at IS NULL";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':tenant_id' => $this->tenantId]);
        $contents = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($contents as $content) {
            $this->index[$content['id']] = [
                'title' => $content['title'],
                'content' => $content['content'],
                'tags' => $content['tags'],
                'created_at' => $content->getCreatedAt()
            ];
        }
    }

    public function getIndex(): array {
        return $this->index;
    }

    public function optimize(): void {
        // Optimize index structure
        $this->index = array_map(function($item) {
            return [
                'title' => $item['title'],
                'content' => $item['content'],
                'tags' => array_map('strtolower', $item['tags']),
                'created_at' => $item['created_at']
            ];
        }, $this->index);
    }
}
