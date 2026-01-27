<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class PagesController
{
    public function __construct()
    {
        $this->ensureTableStructure();
    }
    
    private function ensureTableStructure(): void
    {
        $pdo = db();
        
        // Check if pages table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'pages'");
        if (!$stmt->fetch()) {
            $pdo->exec("CREATE TABLE pages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                content LONGTEXT,
                excerpt TEXT,
                featured_image VARCHAR(500),
                parent_id INT DEFAULT NULL,
                template VARCHAR(100) DEFAULT 'default',
                menu_order INT DEFAULT 0,
                status ENUM('draft', 'published') DEFAULT 'draft',
                meta_title VARCHAR(255),
                meta_description VARCHAR(500),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_slug (slug),
                INDEX idx_status (status),
                INDEX idx_parent (parent_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            return;
        }
        
        // Add missing columns
        $columns = [];
        $result = $pdo->query("SHOW COLUMNS FROM pages");
        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            $columns[] = $row['Field'];
        }
        
        $alterations = [];
        if (!in_array('excerpt', $columns)) $alterations[] = "ADD COLUMN excerpt TEXT AFTER content";
        if (!in_array('featured_image', $columns)) $alterations[] = "ADD COLUMN featured_image VARCHAR(500) AFTER excerpt";
        if (!in_array('parent_id', $columns)) $alterations[] = "ADD COLUMN parent_id INT DEFAULT NULL AFTER featured_image";
        if (!in_array('template', $columns)) $alterations[] = "ADD COLUMN template VARCHAR(100) DEFAULT 'default' AFTER parent_id";
        if (!in_array('menu_order', $columns)) $alterations[] = "ADD COLUMN menu_order INT DEFAULT 0 AFTER template";
        if (!in_array('meta_title', $columns)) $alterations[] = "ADD COLUMN meta_title VARCHAR(255) AFTER status";
        if (!in_array('meta_description', $columns)) $alterations[] = "ADD COLUMN meta_description VARCHAR(500) AFTER meta_title";
        
        if (!empty($alterations)) {
            $pdo->exec("ALTER TABLE pages " . implode(", ", $alterations));
        }
    }
    
    public function index(Request $request): void
    {
        $pdo = db();
        $sort = $request->get('sort', 'updated_at');
        $order = strtoupper($request->get('order', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $allowed = ['id', 'title', 'slug', 'status', 'created_at', 'updated_at', 'menu_order'];
        if (!in_array($sort, $allowed)) $sort = 'updated_at';

        $stmt = $pdo->query("SELECT * FROM pages ORDER BY {$sort} {$order}");
        $pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        render('admin/pages/index', [
            'pages' => $pages,
            'sort' => $sort,
            'order' => $order,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function create(Request $request): void
    {
        render('admin/pages/form', [
            'page' => null,
            'action' => 'create'
        ]);
    }

    public function store(Request $request): void
    {
        $title = trim($request->post('title', ''));
        $slug = trim($request->post('slug', '')) ?: $this->generateSlug($title);
        $content = $request->post('content', '');
        $excerpt = $request->post('excerpt', '');
        $featured_image = $request->post('featured_image', '');
        $parent_id = $request->post('parent_id') ? (int)$request->post('parent_id') : null;
        $template = $request->post('template', 'default');
        $menu_order = (int)$request->post('menu_order', 0);
        $status = in_array($request->post('status'), ['draft', 'published']) ? $request->post('status') : 'draft';
        $status_select = $request->post('status_select');
        if ($status_select && in_array($status_select, ['draft', 'published'])) {
            $status = $status_select;
        }
        $meta_title = $request->post('meta_title', '');
        $meta_description = $request->post('meta_description', '');

        if (empty($title)) {
            Session::flash('error', 'Title is required.');
            Response::redirect('/admin/pages/create');
        }

        $pdo = db();

        // Check slug uniqueness
        $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $slug = $slug . '-' . time();
        }

        $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, excerpt, featured_image, parent_id, template, menu_order, status, meta_title, meta_description, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$title, $slug, $content, $excerpt, $featured_image, $parent_id, $template, $menu_order, $status, $meta_title, $meta_description]);

        Session::flash('success', 'Page created successfully.');
        Response::redirect('/admin/pages');
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');
        $page = $this->findPage($id);

        if (!$page) {
            Session::flash('error', 'Page not found.');
            Response::redirect('/admin/pages');
        }

        render('admin/pages/form', [
            'page' => $page,
            'action' => 'edit'
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int)$request->param('id');
        $page = $this->findPage($id);

        if (!$page) {
            Session::flash('error', 'Page not found.');
            Response::redirect('/admin/pages');
        }

        $title = trim($request->post('title', ''));
        $slug = trim($request->post('slug', '')) ?: $this->generateSlug($title);
        $content = $request->post('content', '');
        $excerpt = $request->post('excerpt', '');
        $featured_image = $request->post('featured_image', '');
        $parent_id = $request->post('parent_id') ? (int)$request->post('parent_id') : null;
        $template = $request->post('template', 'default');
        $menu_order = (int)$request->post('menu_order', 0);
        $status = in_array($request->post('status'), ['draft', 'published']) ? $request->post('status') : 'draft';
        $status_select = $request->post('status_select');
        if ($status_select && in_array($status_select, ['draft', 'published'])) {
            $status = $status_select;
        }
        $meta_title = $request->post('meta_title', '');
        $meta_description = $request->post('meta_description', '');

        if (empty($title)) {
            Session::flash('error', 'Title is required.');
            Response::redirect("/admin/pages/{$id}/edit");
        }

        $pdo = db();

        // Check slug uniqueness (excluding current page)
        $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            $slug = $slug . '-' . time();
        }

        $stmt = $pdo->prepare("UPDATE pages SET title = ?, slug = ?, content = ?, excerpt = ?, featured_image = ?, parent_id = ?, template = ?, menu_order = ?, status = ?, meta_title = ?, meta_description = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$title, $slug, $content, $excerpt, $featured_image, $parent_id, $template, $menu_order, $status, $meta_title, $meta_description, $id]);

        Session::flash('success', 'Page updated successfully.');
        Response::redirect('/admin/pages');
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'Page deleted successfully.');
        Response::redirect('/admin/pages');
    }

    private function findPage(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
        $stmt->execute([$id]);
        $page = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $page ?: null;
    }

    private function generateSlug(string $title): string
    {
        $slug = mb_strtolower($title, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s_]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
