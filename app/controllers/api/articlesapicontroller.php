<?php
declare(strict_types=1);

namespace Api;

require_once __DIR__ . '/../../../core/api_middleware.php';

use Core\Request;

/**
 * Articles API Controller
 *
 * Handles public read-only AND authenticated write API requests for articles
 *
 * @package JessieCMS
 * @since 2026-02-15
 */
class ArticlesApiController {

    public function __construct() {
        // Apply rate limiting to all methods
        \Core\api_rate_limit(60);
    }

    /**
     * GET /api/v1/articles
     * List published articles with pagination and filtering
     */
    public function index(?Request $request = null): void {
        try {
            $pdo = \core\Database::connection();

            // Get query parameters
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = max(1, min(100, (int)($_GET['per_page'] ?? 20))); // Cap at 100
            $category = $_GET['category'] ?? null;

            // Build query
            $where = "WHERE a.status = 'published'";
            $params = [];

            if ($category) {
                $where .= " AND c.slug = ?";
                $params[] = $category;
            }

            // Count total articles
            $countSql = "
                SELECT COUNT(DISTINCT a.id)
                FROM articles a
                LEFT JOIN categories c ON a.category_id = c.id
                $where
            ";
            $stmt = $pdo->prepare($countSql);
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();

            // Get articles with pagination
            $offset = ($page - 1) * $perPage;
            $sql = "
                SELECT
                    a.id,
                    a.title,
                    a.slug,
                    a.excerpt,
                    a.content,
                    a.featured_image,
                    a.created_at,
                    a.updated_at,
                    c.name as category_name,
                    c.slug as category_slug,
                    u.username as author
                FROM articles a
                LEFT JOIN categories c ON a.category_id = c.id
                LEFT JOIN users u ON a.author_id = u.id
                $where
                ORDER BY a.created_at DESC
                LIMIT $perPage OFFSET $offset
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $articles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Format articles
            $formattedArticles = array_map(function($article) {
                $sanitized = \Core\api_sanitize_content($article['content'] ?? '');
                return [
                    'id' => (int)$article['id'],
                    'title' => $article['title'],
                    'slug' => $article['slug'],
                    'excerpt' => $article['excerpt'] ?? '',
                    'content' => $sanitized['content'],
                    'content_text' => $sanitized['content_text'],
                    'featured_image' => $article['featured_image'] ?? null,
                    'category' => $article['category_name'] ? [
                        'name' => $article['category_name'],
                        'slug' => $article['category_slug']
                    ] : null,
                    'author' => $article['author'],
                    'created_at' => $article['created_at'],
                    'updated_at' => $article['updated_at']
                ];
            }, $articles);

            \Core\api_json_response([
                'data' => $formattedArticles,
                'meta' => \Core\api_paginate($page, $perPage, $total)
            ]);

        } catch (\Throwable $e) {
            error_log("Articles API error: " . $e->getMessage());
            \Core\api_error('Failed to fetch articles', 500);
        }
    }

    /**
     * GET /api/v1/articles/{slug}
     * Get single article by slug
     */
    public function show(?Request $request = null): void {
        $slug = $request?->getParam('slug') ?? $_GET['slug'] ?? null;

        if (!$slug) {
            \Core\api_error('Article slug is required', 400);
        }

        try {
            $pdo = \core\Database::connection();

            $sql = "
                SELECT
                    a.id,
                    a.title,
                    a.slug,
                    a.excerpt,
                    a.content,
                    a.featured_image,
                    a.meta_title,
                    a.meta_description,
                    a.created_at,
                    a.updated_at,
                    c.name as category_name,
                    c.slug as category_slug,
                    u.username as author
                FROM articles a
                LEFT JOIN categories c ON a.category_id = c.id
                LEFT JOIN users u ON a.author_id = u.id
                WHERE a.slug = ? AND a.status = 'published'
                LIMIT 1
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$slug]);
            $article = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$article) {
                \Core\api_error('Article not found', 404);
            }

            // Format article
            $sanitized = \Core\api_sanitize_content($article['content'] ?? '');
            $formattedArticle = [
                'id' => (int)$article['id'],
                'title' => $article['title'],
                'slug' => $article['slug'],
                'excerpt' => $article['excerpt'] ?? '',
                'content' => $sanitized['content'],
                'content_text' => $sanitized['content_text'],
                'featured_image' => $article['featured_image'] ?? null,
                'meta_title' => $article['meta_title'] ?? null,
                'meta_description' => $article['meta_description'] ?? null,
                'category' => $article['category_name'] ? [
                    'name' => $article['category_name'],
                    'slug' => $article['category_slug']
                ] : null,
                'author' => $article['author'],
                'created_at' => $article['created_at'],
                'updated_at' => $article['updated_at']
            ];

            \Core\api_json_response([
                'data' => $formattedArticle
            ]);

        } catch (\Throwable $e) {
            error_log("Article API error: " . $e->getMessage());
            \Core\api_error('Failed to fetch article', 500);
        }
    }

    /* ═══════════════════════════════════════════════════════
     * WRITE ENDPOINTS (Bearer token auth required)
     * ═══════════════════════════════════════════════════════ */

    /**
     * POST /api/v1/articles — Create article
     */
    public function store(?Request $request = null): void {
        \Core\api_authenticate('articles:write');

        $data = \Core\api_read_json();

        // Validate required fields
        $title = trim($data['title'] ?? '');
        if ($title === '') {
            \Core\api_error('Field "title" is required', 422);
        }

        $slug    = trim($data['slug'] ?? '');
        $content = $data['content'] ?? '';
        $excerpt = $data['excerpt'] ?? '';
        $status  = $data['status'] ?? 'draft';

        if (!in_array($status, ['draft', 'published', 'archived'], true)) {
            \Core\api_error('Invalid status. Allowed: draft, published, archived', 422);
        }

        // Auto-generate slug from title if empty
        if ($slug === '') {
            $slug = $this->slugify($title);
        }

        try {
            $pdo = \core\Database::connection();

            // Check slug uniqueness
            $stmt = $pdo->prepare("SELECT id FROM articles WHERE slug = ? LIMIT 1");
            $stmt->execute([$slug]);
            if ($stmt->fetch()) {
                \Core\api_error('An article with this slug already exists', 422);
            }

            $stmt = $pdo->prepare("
                INSERT INTO articles (title, slug, content, excerpt, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$title, $slug, $content, $excerpt, $status]);

            $id = (int)$pdo->lastInsertId();

            // Fetch the created article
            $stmt = $pdo->prepare("SELECT id, title, slug, excerpt, status, created_at, updated_at FROM articles WHERE id = ?");
            $stmt->execute([$id]);
            $article = $stmt->fetch(\PDO::FETCH_ASSOC);

            \Core\api_json_response([
                'data' => [
                    'id'         => (int)$article['id'],
                    'title'      => $article['title'],
                    'slug'       => $article['slug'],
                    'excerpt'    => $article['excerpt'],
                    'status'     => $article['status'],
                    'created_at' => $article['created_at'],
                    'updated_at' => $article['updated_at'],
                ],
                'message' => 'Article created successfully'
            ], 201);

        } catch (\Throwable $e) {
            error_log("Articles API create error: " . $e->getMessage());
            \Core\api_error('Failed to create article', 500);
        }
    }

    /**
     * PUT /api/v1/articles/{slug} — Update article
     */
    public function update(?Request $request = null): void {
        \Core\api_authenticate('articles:write');

        $slug = $request?->getParam('slug') ?? null;
        if (!$slug) {
            \Core\api_error('Article slug is required', 400);
        }

        $data = \Core\api_read_json();

        try {
            $pdo = \core\Database::connection();

            // Find article
            $stmt = $pdo->prepare("SELECT * FROM articles WHERE slug = ? LIMIT 1");
            $stmt->execute([$slug]);
            $article = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$article) {
                \Core\api_error('Article not found', 404);
            }

            // Build SET clause from provided fields
            $allowed = ['title', 'content', 'excerpt', 'status', 'slug'];
            $sets = [];
            $params = [];

            foreach ($allowed as $field) {
                if (array_key_exists($field, $data)) {
                    $value = $data[$field];
                    // Validate status
                    if ($field === 'status' && !in_array($value, ['draft', 'published', 'archived'], true)) {
                        \Core\api_error('Invalid status. Allowed: draft, published, archived', 422);
                    }
                    // Check slug uniqueness if changing
                    if ($field === 'slug' && $value !== $article['slug']) {
                        $chk = $pdo->prepare("SELECT id FROM articles WHERE slug = ? AND id != ? LIMIT 1");
                        $chk->execute([$value, $article['id']]);
                        if ($chk->fetch()) {
                            \Core\api_error('An article with this slug already exists', 422);
                        }
                    }
                    $sets[] = "`{$field}` = ?";
                    $params[] = $value;
                }
            }

            if (empty($sets)) {
                \Core\api_error('No valid fields to update', 422);
            }

            $sets[] = "updated_at = NOW()";
            $params[] = $article['id'];

            $sql = "UPDATE articles SET " . implode(', ', $sets) . " WHERE id = ?";
            $pdo->prepare($sql)->execute($params);

            // Fetch updated article
            $stmt = $pdo->prepare("SELECT id, title, slug, excerpt, status, created_at, updated_at FROM articles WHERE id = ?");
            $stmt->execute([$article['id']]);
            $updated = $stmt->fetch(\PDO::FETCH_ASSOC);

            \Core\api_json_response([
                'data' => [
                    'id'         => (int)$updated['id'],
                    'title'      => $updated['title'],
                    'slug'       => $updated['slug'],
                    'excerpt'    => $updated['excerpt'],
                    'status'     => $updated['status'],
                    'created_at' => $updated['created_at'],
                    'updated_at' => $updated['updated_at'],
                ],
                'message' => 'Article updated successfully'
            ]);

        } catch (\Throwable $e) {
            error_log("Articles API update error: " . $e->getMessage());
            \Core\api_error('Failed to update article', 500);
        }
    }

    /**
     * DELETE /api/v1/articles/{slug} — Delete article
     */
    public function destroy(?Request $request = null): void {
        \Core\api_authenticate('articles:write');

        $slug = $request?->getParam('slug') ?? null;
        if (!$slug) {
            \Core\api_error('Article slug is required', 400);
        }

        try {
            $pdo = \core\Database::connection();

            $stmt = $pdo->prepare("SELECT id FROM articles WHERE slug = ? LIMIT 1");
            $stmt->execute([$slug]);
            $article = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$article) {
                \Core\api_error('Article not found', 404);
            }

            $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$article['id']]);

            // 204 No Content
            \Core\api_cors_headers();
            http_response_code(204);
            exit;

        } catch (\Throwable $e) {
            error_log("Articles API delete error: " . $e->getMessage());
            \Core\api_error('Failed to delete article', 500);
        }
    }

    /* ─── helpers ─── */

    private function slugify(string $text): string {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
}
