<?php
declare(strict_types=1);
/**
 * Theme Builder 4.0 Controller
 * Pure PHP page builder - NO React
 * Serves pages for visual editing with drag & drop
 */

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class Tb4BuilderController
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Show TB 4.0 page list dashboard
     */
    public function index(Request $request): void
    {
        $csrfToken = csrf_token();
        $user = Session::get('admin');

        render('admin/tb4-builder/index', [
            'pageTitle' => 'Theme Builder 4.0',
            'csrfToken' => $csrfToken,
            'user' => $user
        ]);
    }

    /**
     * Edit page - redirect to TB4 visual builder
     * Legacy route: /admin/tb4-builder/{id}/edit -> /admin/tb4/edit/{id}
     */
    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');

        // Redirect to the new TB4 editor route
        header('Location: /admin/tb4/edit/' . $id);
        exit;
    }

    /**
     * Create new page
     */
    public function create(Request $request): void
    {
        csrf_validate_or_403();

        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');

        if (empty($title)) {
            Session::flash('error', 'Page title is required');
            header('Location: /admin/tb4-builder');
            exit;
        }

        // Generate slug if empty
        if (empty($slug)) {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
            $slug = trim($slug, '-');
        }

        // Check for duplicate slug
        $stmt = $this->db->prepare("SELECT id FROM tb_pages WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }

        // Insert new page
        $stmt = $this->db->prepare(
            "INSERT INTO tb_pages (title, slug, status, content_json, created_at, updated_at) VALUES (?, ?, 'draft', '{}', NOW(), NOW())"
        );
        $stmt->execute([$title, $slug]);
        $pageId = (int)$this->db->lastInsertId();

        // Redirect to TB4 editor (dark theme)
        header('Location: /admin/tb4/edit/' . $pageId);
        exit;
    }

    /**
     * Save page content (API endpoint)
     */
    public function save(Request $request): void
    {
        header('Content-Type: application/json');
        
        // CSRF check
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
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }

        $title = $data['title'] ?? '';
        $content = json_encode($data['content'] ?? []);

        $stmt = $this->db->prepare(
            "UPDATE tb_pages SET title = ?, content_json = ?, updated_at = NOW() WHERE id = ?"
        );
        $stmt->execute([$title, $content, $id]);

        echo json_encode(['success' => true, 'message' => 'Page saved']);
    }
}
