<?php

class ContentController {
    private $db;
    private $session;

    public function __construct($db, $session) {
        $this->db = $db;
        $this->session = $session;
    }

    public function index() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM content WHERE status = 'published' ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ContentController::index error: " . $e->getMessage());
            return [];
        }
    }

    public function create() {
        // Return empty content structure for new content
        return [
            'title' => '',
            'slug' => '',
            'content' => '',
            'status' => 'draft',
            'author_id' => $this->session->get('user_id')
        ];
    }

    public function store(array $data) {
        require_once __DIR__ . '/../../csrf.php';
        csrf_validate_or_403();

        $this->sanitizeInput($data);
        $data['slug'] = $this->generateSlug($data['title']);
        $data['author_id'] = $this->session->get('user_id');
        $data['status'] = $data['status'] ?? 'draft';

        try {
            $stmt = $this->db->prepare("
                INSERT INTO content 
                (title, slug, content, status, author_id, created_at, updated_at) 
                VALUES (:title, :slug, :content, :status, :author_id, NOW(), NOW())
            ");
            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("ContentController::store error: " . $e->getMessage());
            return false;
        }
    }

    public function edit(int $id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM content WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ContentController::edit error: " . $e->getMessage());
            return null;
        }
    }

    public function update(int $id, array $data) {
        require_once __DIR__ . '/../../csrf.php';
        csrf_validate_or_403();

        $this->sanitizeInput($data);
        $data['id'] = $id;
        $data['slug'] = $this->generateSlug($data['title']);
        $data['updated_by'] = $this->session->get('user_id');

        try {
            $stmt = $this->db->prepare("
                UPDATE content SET 
                title = :title, 
                slug = :slug, 
                content = :content, 
                status = :status,
                updated_by = :updated_by,
                updated_at = NOW()
                WHERE id = :id
            ");
            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("ContentController::update error: " . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id) {
        require_once __DIR__ . '/../../csrf.php';
        csrf_validate_or_403();

        try {
            $stmt = $this->db->prepare("UPDATE content SET status = 'deleted', deleted_at = NOW() WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("ContentController::delete error: " . $e->getMessage());
            return false;
        }
    }

    private function sanitizeInput(array &$data) {
        $data['title'] = htmlspecialchars(strip_tags($data['title'] ?? ''));
        $data['content'] = htmlspecialchars($data['content'] ?? '');
        $data['status'] = in_array($data['status'] ?? '', ['draft', 'published', 'archived']) 
            ? $data['status'] 
            : 'draft';
    }

    private function generateSlug(string $title): string {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return substr($slug, 0, 100);
    }
}
