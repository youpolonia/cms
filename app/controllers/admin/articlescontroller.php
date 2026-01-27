<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class ArticlesController
{
    public function index(Request $request): void
    {
        $pdo = db();
        $sort = $request->get('sort', 'updated_at');
        $order = strtoupper($request->get('order', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $status = $request->get('status', '');

        $allowed = ['id', 'title', 'slug', 'status', 'category_id', 'created_at', 'updated_at', 'published_at'];
        if (!in_array($sort, $allowed)) $sort = 'updated_at';

        $where = '';
        $params = [];
        if ($status && in_array($status, ['draft', 'published', 'archived'])) {
            $where = 'WHERE a.status = ?';
            $params[] = $status;
        }

        $sql = "SELECT a.*, c.name as category_name
                FROM articles a
                LEFT JOIN categories c ON a.category_id = c.id
                {$where}
                ORDER BY a.{$sort} {$order}";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get counts
        $counts = ['all' => 0, 'draft' => 0, 'published' => 0, 'archived' => 0];
        $countStmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM articles GROUP BY status");
        foreach ($countStmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $counts[$row['status']] = (int)$row['cnt'];
            $counts['all'] += (int)$row['cnt'];
        }

        render('admin/articles/index', [
            'articles' => $articles,
            'sort' => $sort,
            'order' => $order,
            'status' => $status,
            'counts' => $counts,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function create(Request $request): void
    {
        $categories = $this->getCategories();

        render('admin/articles/form', [
            'article' => null,
            'categories' => $categories,
            'action' => 'create'
        ]);
    }

    public function store(Request $request): void
    {
        $title = trim($request->post('title', ''));
        $slug = trim($request->post('slug', '')) ?: $this->generateSlug($title);
        $excerpt = trim($request->post('excerpt', ''));
        $content = $request->post('content', '');
        $status = in_array($request->post('status'), ['draft', 'published', 'archived']) ? $request->post('status') : 'draft';
        $category_id = (int)$request->post('category_id') ?: null;
        $meta_title = trim($request->post('meta_title', ''));
        $meta_description = trim($request->post('meta_description', ''));
        $meta_keywords = trim($request->post('meta_keywords', ''));
        $focus_keyword = trim($request->post('focus_keyword', ''));
        $featured_image = trim($request->post('featured_image', ''));
        $featured_image_alt = trim($request->post('featured_image_alt', ''));
        $featured_image_title = trim($request->post('featured_image_title', ''));
        $published_at = $status === 'published' ? date('Y-m-d H:i:s') : null;

        if (empty($title)) {
            Session::flash('error', 'Title is required.');
            Response::redirect('/admin/articles/create');
        }

        $pdo = db();

        $stmt = $pdo->prepare("SELECT id FROM articles WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            Session::flash('error', 'An article with this slug already exists.');
            Response::redirect('/admin/articles/create');
        }

        $author_id = Session::getAdminId();

        $stmt = $pdo->prepare("INSERT INTO articles (title, slug, excerpt, content, status, category_id, author_id, featured_image, featured_image_alt, featured_image_title, meta_title, meta_description, meta_keywords, focus_keyword, published_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$title, $slug, $excerpt, $content, $status, $category_id, $author_id, $featured_image, $featured_image_alt, $featured_image_title, $meta_title, $meta_description, $meta_keywords, $focus_keyword, $published_at]);

        $newId = $pdo->lastInsertId();

        Session::flash('success', 'Article created successfully.');
        
        // If draft, stay on edit page to continue working
        if ($status === 'draft') {
            Response::redirect("/admin/articles/{$newId}/edit");
        } else {
            Response::redirect('/admin/articles');
        }
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');
        $article = $this->findArticle($id);

        if (!$article) {
            Session::flash('error', 'Article not found.');
            Response::redirect('/admin/articles');
        }

        $categories = $this->getCategories();

        render('admin/articles/form', [
            'article' => $article,
            'categories' => $categories,
            'action' => 'edit'
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int)$request->param('id');
        $article = $this->findArticle($id);

        if (!$article) {
            Session::flash('error', 'Article not found.');
            Response::redirect('/admin/articles');
        }

        $title = trim($request->post('title', ''));
        $slug = trim($request->post('slug', '')) ?: $this->generateSlug($title);
        $excerpt = trim($request->post('excerpt', ''));
        $content = $request->post('content', '');
        $status = in_array($request->post('status'), ['draft', 'published', 'archived']) ? $request->post('status') : 'draft';
        $category_id = (int)$request->post('category_id') ?: null;
        $meta_title = trim($request->post('meta_title', ''));
        $meta_description = trim($request->post('meta_description', ''));
        $meta_keywords = trim($request->post('meta_keywords', ''));
        $focus_keyword = trim($request->post('focus_keyword', ''));
        $featured_image = trim($request->post('featured_image', ''));
        $featured_image_alt = trim($request->post('featured_image_alt', ''));
        $featured_image_title = trim($request->post('featured_image_title', ''));

        // Set published_at if publishing for first time
        $published_at = $article['published_at'];
        if ($status === 'published' && empty($published_at)) {
            $published_at = date('Y-m-d H:i:s');
        }

        if (empty($title)) {
            Session::flash('error', 'Title is required.');
            Response::redirect("/admin/articles/{$id}/edit");
        }

        $pdo = db();

        $stmt = $pdo->prepare("SELECT id FROM articles WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            Session::flash('error', 'An article with this slug already exists.');
            Response::redirect("/admin/articles/{$id}/edit");
        }

        $stmt = $pdo->prepare("UPDATE articles SET title = ?, slug = ?, excerpt = ?, content = ?, status = ?, category_id = ?, featured_image = ?, featured_image_alt = ?, featured_image_title = ?, meta_title = ?, meta_description = ?, meta_keywords = ?, focus_keyword = ?, published_at = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$title, $slug, $excerpt, $content, $status, $category_id, $featured_image, $featured_image_alt, $featured_image_title, $meta_title, $meta_description, $meta_keywords, $focus_keyword, $published_at, $id]);

        Session::flash('success', 'Article updated successfully.');
        
        // If draft, stay on edit page to continue working
        if ($status === 'draft') {
            Response::redirect("/admin/articles/{$id}/edit");
        } else {
            Response::redirect('/admin/articles');
        }
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'Article deleted successfully.');
        Response::redirect('/admin/articles');
    }

    private function findArticle(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
        $stmt->execute([$id]);
        $article = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $article ?: null;
    }

    private function getCategories(): array
    {
        $pdo = db();
        $stmt = $pdo->query("SELECT id, name, slug FROM categories ORDER BY name ASC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function generateSlug(string $title): string
    {
        $slug = mb_strtolower($title, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s_]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    /**
     * AJAX: Save article preview to session
     */
    public function preview(Request $request): void
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $articleId = (int)($input['article_id'] ?? 0);
        
        // Save preview content to session
        $_SESSION['article_preview_' . $articleId] = [
            'title' => $input['title'] ?? 'Untitled',
            'content' => $input['content'] ?? '',
            'excerpt' => $input['excerpt'] ?? '',
            'featured_image' => $input['featured_image'] ?? '',
            'category_name' => $input['category_name'] ?? '',
            'timestamp' => time()
        ];
        
        echo json_encode([
            'success' => true,
            'article_id' => $articleId
        ]);
    }
}
