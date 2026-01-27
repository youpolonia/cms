<?php
declare(strict_types=1);
/**
 * TB4 Builder Controller
 * Manages pages with TB4 visual content
 * Pure PHP - NO React/Vue/npm
 */

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class Tb4Controller
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = db();
        $this->ensureTableExists();
    }

    /**
     * Ensure tb4_pages table exists
     */
    private function ensureTableExists(): void
    {
        $stmt = $this->db->query("SHOW TABLES LIKE 'tb4_pages'");
        if (!$stmt->fetch()) {
            $this->db->exec("CREATE TABLE tb4_pages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                status ENUM('draft', 'published') DEFAULT 'draft',
                content JSON,
                page_id INT DEFAULT NULL COMMENT 'Link to main pages table if needed',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_slug (slug),
                INDEX idx_status (status),
                INDEX idx_page_id (page_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
    }

    /**
     * List all TB4 pages
     */
    public function index(Request $request): void
    {
        $csrfToken = csrf_token();
        $user = Session::get('admin');

        // Fetch pages with optional join to main pages table
        $stmt = $this->db->prepare(
            "SELECT p.id, p.title, p.slug, p.status, p.page_id, p.created_at, p.updated_at
             FROM tb4_pages p
             ORDER BY p.updated_at DESC
             LIMIT 100"
        );
        $stmt->execute();
        $pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        render('admin/tb4/index', [
            'pageTitle' => 'TB4 Builder',
            'csrfToken' => $csrfToken,
            'user' => $user,
            'pages' => $pages,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    /**
     * Show create form or create new TB4 page
     */
    public function create(Request $request): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_validate_or_403();

            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');

            if (empty($title)) {
                Session::flash('error', 'Page title is required');
                Response::redirect('/admin/tb4');
                return;
            }

            // Generate slug if empty
            if (empty($slug)) {
                $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
                $slug = trim($slug, '-');
            }

            // Check for duplicate slug
            $stmt = $this->db->prepare("SELECT id FROM tb4_pages WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetch()) {
                $slug .= '-' . time();
            }

            // Insert new page with empty content
            $stmt = $this->db->prepare(
                "INSERT INTO tb4_pages (title, slug, status, content, created_at, updated_at)
                 VALUES (?, ?, 'draft', '{}', NOW(), NOW())"
            );
            $stmt->execute([$title, $slug]);
            $pageId = (int)$this->db->lastInsertId();

            Session::flash('success', 'Page created successfully');
            Response::redirect('/admin/tb4/edit/' . $pageId);
            return;
        }

        // Show create form
        render('admin/tb4/create', [
            'pageTitle' => 'Create TB4 Page',
            'csrfToken' => csrf_token()
        ]);
    }

    /**
     * Edit page in TB4 visual builder
     */
    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');

        // Fetch page
        $stmt = $this->db->prepare(
            "SELECT id, title, slug, status, content, page_id, created_at, updated_at
             FROM tb4_pages WHERE id = ?"
        );
        $stmt->execute([$id]);
        $page = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$page) {
            Session::flash('error', 'Page not found');
            Response::redirect('/admin/tb4');
            return;
        }

        // Decode content JSON
        $content = [];
        if (!empty($page['content'])) {
            $content = json_decode($page['content'], true) ?: [];
        }

        $csrfToken = csrf_token();
        $user = Session::get('admin');

        render('admin/tb4/edit', [
            'pageTitle' => 'Edit: ' . esc($page['title']),
            'csrfToken' => $csrfToken,
            'user' => $user,
            'page_id' => $id,
            'pageId' => $id,
            'page' => $page,
            'content' => $content
        ]);
    }

    /**
     * Delete TB4 content (keeps page in main pages table if linked)
     */
    public function delete(Request $request): void
    {
        csrf_validate_or_403();

        $id = (int)$request->param('id');

        // Check page exists
        $stmt = $this->db->prepare("SELECT id, title FROM tb4_pages WHERE id = ?");
        $stmt->execute([$id]);
        $page = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$page) {
            Session::flash('error', 'Page not found');
            Response::redirect('/admin/tb4');
            return;
        }

        // Delete from tb4_pages
        $stmt = $this->db->prepare("DELETE FROM tb4_pages WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'Page "' . esc($page['title']) . '" deleted successfully');
        Response::redirect('/admin/tb4');
    }

    /**
     * Save page content (API endpoint)
     */
    public function save(Request $request): void
    {
        header('Content-Type: application/json');

        // CSRF check via header
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!csrf_validate($csrfToken)) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid CSRF token']);
            return;
        }

        $id = (int)$request->param('id');
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON payload']);
            return;
        }

        $title = $data['title'] ?? '';
        $content = json_encode($data['content'] ?? []);

        $stmt = $this->db->prepare(
            "UPDATE tb4_pages SET title = ?, content = ?, updated_at = NOW() WHERE id = ?"
        );
        $stmt->execute([$title, $content, $id]);

        echo json_encode(['success' => true, 'message' => 'Page saved successfully']);
    }

    /**
     * Toggle page status (draft/published)
     */
    public function toggle(Request $request): void
    {
        csrf_validate_or_403();

        $id = (int)$request->param('id');

        $stmt = $this->db->prepare("SELECT status FROM tb4_pages WHERE id = ?");
        $stmt->execute([$id]);
        $page = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$page) {
            Session::flash('error', 'Page not found');
            Response::redirect('/admin/tb4');
            return;
        }

        $newStatus = $page['status'] === 'published' ? 'draft' : 'published';
        $stmt = $this->db->prepare("UPDATE tb4_pages SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$newStatus, $id]);

        Session::flash('success', 'Page status changed to ' . $newStatus);
        Response::redirect('/admin/tb4');
    }

    /**
     * API endpoint - delegates to core/tb4/api.php
     */
    public function api(Request $request): void
    {
        // Include and instantiate the TB4 API handler
        require_once CMS_ROOT . '/core/tb4/api.php';
        
        $api = new \Core\TB4\Api();
        $api->handle_request();
    }
}
