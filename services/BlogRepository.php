<?php
require_once __DIR__ . '/../core/database.php';

/**
 * Blog Repository - Handles database operations for blog posts
 */
class BlogRepository {
    private PDO $db;

    public function __construct() {
        $this->db = \core\Database::connection();
    }

    /**
     * Get all blog posts
     * @return array List of BlogPost objects
     */
    public function findAll(): array {
        $stmt = $this->db->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'BlogPost');
    }

    /**
     * Get single blog post by ID
     * @param int $id Post ID
     * @return BlogPost|null Post object or null if not found
     */
    public function findById(int $id): ?BlogPost {
        $stmt = $this->db->prepare("SELECT * FROM blog_posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetchObject('BlogPost') ?: null;
    }

    /**
     * Create new blog post
     * @param array $data Post data
     * @return BlogPost Created post
     */
    public function create(array $data): BlogPost {
        $stmt = $this->db->prepare("
            INSERT INTO blog_posts (title, slug, content, author_id, status, created_at, updated_at)
            VALUES (:title, :slug, :content, :author_id, :status, NOW(), NOW())
        ");
        
        $data['slug'] = $this->generateSlug($data['title']);
        $data['status'] = $data['status'] ?? 'published';
        
        $stmt->execute($data);
        $id = $this->db->lastInsertId();
        return $this->findById($id);
    }

    /**
     * Update existing blog post
     * @param int $id Post ID
     * @param array $data Update data
     * @return BlogPost Updated post
     */
    public function update(int $id, array $data): BlogPost {
        $stmt = $this->db->prepare("
            UPDATE blog_posts SET
                title = :title,
                slug = :slug,
                content = :content,
                status = :status,
                updated_at = NOW()
            WHERE id = :id
        ");
        
        $data['id'] = $id;
        $data['slug'] = $this->generateSlug($data['title']);
        $stmt->execute($data);
        
        return $this->findById($id);
    }

    /**
     * Delete blog post
     * @param int $id Post ID
     * @return bool True if deleted
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM blog_posts WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Generate URL slug from title
     * @param string $title Post title
     * @return string Generated slug
     */
    private function generateSlug(string $title): string {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return $slug;
    }
}
