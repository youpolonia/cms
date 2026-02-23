<?php
declare(strict_types=1);

namespace Api;

require_once __DIR__ . '/../../../core/api_middleware.php';

use Core\Request;

/**
 * Pages API Controller
 *
 * Handles public read-only AND authenticated write API requests for pages
 *
 * @package JessieCMS
 * @since 2026-02-15
 */
class PagesApiController {

    public function __construct() {
        // Apply rate limiting to all methods
        \Core\api_rate_limit(60);
    }

    /**
     * GET /api/v1/pages
     * List published pages with pagination
     */
    public function index(?Request $request = null): void {
        try {
            $pdo = \core\Database::connection();

            // Get query parameters
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = max(1, min(100, (int)($_GET['per_page'] ?? 20))); // Cap at 100

            // Count total pages
            $countSql = "SELECT COUNT(*) FROM pages WHERE status = 'published'";
            $stmt = $pdo->prepare($countSql);
            $stmt->execute();
            $total = (int)$stmt->fetchColumn();

            // Get pages with pagination
            $offset = ($page - 1) * $perPage;
            $sql = "
                SELECT
                    id,
                    title,
                    slug,
                    excerpt,
                    content,
                    featured_image,
                    meta_title,
                    meta_description,
                    created_at,
                    updated_at
                FROM pages
                WHERE status = 'published'
                ORDER BY title ASC
                LIMIT $perPage OFFSET $offset
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $pages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Format pages
            $formattedPages = array_map(function($page) {
                $sanitized = \Core\api_sanitize_content($page['content'] ?? '');
                return [
                    'id' => (int)$page['id'],
                    'title' => $page['title'],
                    'slug' => $page['slug'],
                    'excerpt' => $page['excerpt'] ?? '',
                    'content' => $sanitized['content'],
                    'content_text' => $sanitized['content_text'],
                    'featured_image' => $page['featured_image'] ?? null,
                    'meta_title' => $page['meta_title'] ?? null,
                    'meta_description' => $page['meta_description'] ?? null,
                    'created_at' => $page['created_at'],
                    'updated_at' => $page['updated_at']
                ];
            }, $pages);

            \Core\api_json_response([
                'data' => $formattedPages,
                'meta' => \Core\api_paginate($page, $perPage, $total)
            ]);

        } catch (\Throwable $e) {
            error_log("Pages API error: " . $e->getMessage());
            \Core\api_error('Failed to fetch pages', 500);
        }
    }

    /**
     * GET /api/v1/pages/{slug}
     * Get single page by slug
     */
    public function show(?Request $request = null): void {
        $slug = $request?->getParam('slug') ?? $_GET['slug'] ?? null;

        if (!$slug) {
            \Core\api_error('Page slug is required', 400);
        }

        try {
            $pdo = \core\Database::connection();

            $sql = "
                SELECT
                    id,
                    title,
                    slug,
                    excerpt,
                    content,
                    featured_image,
                    meta_title,
                    meta_description,
                    created_at,
                    updated_at
                FROM pages
                WHERE slug = ? AND status = 'published'
                LIMIT 1
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$slug]);
            $page = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$page) {
                \Core\api_error('Page not found', 404);
            }

            // Format page
            $sanitized = \Core\api_sanitize_content($page['content'] ?? '');
            $formattedPage = [
                'id' => (int)$page['id'],
                'title' => $page['title'],
                'slug' => $page['slug'],
                'excerpt' => $page['excerpt'] ?? '',
                'content' => $sanitized['content'],
                'content_text' => $sanitized['content_text'],
                'featured_image' => $page['featured_image'] ?? null,
                'meta_title' => $page['meta_title'] ?? null,
                'meta_description' => $page['meta_description'] ?? null,
                'created_at' => $page['created_at'],
                'updated_at' => $page['updated_at']
            ];

            \Core\api_json_response([
                'data' => $formattedPage
            ]);

        } catch (\Throwable $e) {
            error_log("Page API error: " . $e->getMessage());
            \Core\api_error('Failed to fetch page', 500);
        }
    }

    /* ═══════════════════════════════════════════════════════
     * WRITE ENDPOINTS (Bearer token auth required)
     * ═══════════════════════════════════════════════════════ */

    /**
     * POST /api/v1/pages — Create page
     */
    public function store(?Request $request = null): void {
        \Core\api_authenticate('pages:write');

        $data = \Core\api_read_json();

        // Validate required fields
        $title = trim($data['title'] ?? '');
        if ($title === '') {
            \Core\api_error('Field "title" is required', 422);
        }

        $slug    = trim($data['slug'] ?? '');
        $content = $data['content'] ?? '';
        $excerpt = $data['excerpt'] ?? null;
        $status  = $data['status'] ?? 'published';

        if (!in_array($status, ['draft', 'published'], true)) {
            \Core\api_error('Invalid status. Allowed: draft, published', 422);
        }

        // Auto-generate slug from title if empty
        if ($slug === '') {
            $slug = $this->slugify($title);
        }

        try {
            $pdo = \core\Database::connection();

            // Check slug uniqueness
            $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ? LIMIT 1");
            $stmt->execute([$slug]);
            if ($stmt->fetch()) {
                \Core\api_error('A page with this slug already exists', 422);
            }

            $stmt = $pdo->prepare("
                INSERT INTO pages (title, slug, content, excerpt, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$title, $slug, $content, $excerpt, $status]);

            $id = (int)$pdo->lastInsertId();

            // Fetch the created page
            $stmt = $pdo->prepare("SELECT id, title, slug, excerpt, status, created_at, updated_at FROM pages WHERE id = ?");
            $stmt->execute([$id]);
            $page = $stmt->fetch(\PDO::FETCH_ASSOC);

            \Core\api_json_response([
                'data' => [
                    'id'         => (int)$page['id'],
                    'title'      => $page['title'],
                    'slug'       => $page['slug'],
                    'excerpt'    => $page['excerpt'],
                    'status'     => $page['status'],
                    'created_at' => $page['created_at'],
                    'updated_at' => $page['updated_at'],
                ],
                'message' => 'Page created successfully'
            ], 201);

        } catch (\Throwable $e) {
            error_log("Pages API create error: " . $e->getMessage());
            \Core\api_error('Failed to create page', 500);
        }
    }

    /**
     * PUT /api/v1/pages/{slug} — Update page
     */
    public function update(?Request $request = null): void {
        \Core\api_authenticate('pages:write');

        $slug = $request?->getParam('slug') ?? null;
        if (!$slug) {
            \Core\api_error('Page slug is required', 400);
        }

        $data = \Core\api_read_json();

        try {
            $pdo = \core\Database::connection();

            // Find page
            $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? LIMIT 1");
            $stmt->execute([$slug]);
            $page = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$page) {
                \Core\api_error('Page not found', 404);
            }

            // Build SET clause from provided fields
            $allowed = ['title', 'content', 'excerpt', 'status', 'slug'];
            $sets = [];
            $params = [];

            foreach ($allowed as $field) {
                if (array_key_exists($field, $data)) {
                    $value = $data[$field];
                    // Validate status
                    if ($field === 'status' && !in_array($value, ['draft', 'published'], true)) {
                        \Core\api_error('Invalid status. Allowed: draft, published', 422);
                    }
                    // Check slug uniqueness if changing
                    if ($field === 'slug' && $value !== $page['slug']) {
                        $chk = $pdo->prepare("SELECT id FROM pages WHERE slug = ? AND id != ? LIMIT 1");
                        $chk->execute([$value, $page['id']]);
                        if ($chk->fetch()) {
                            \Core\api_error('A page with this slug already exists', 422);
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
            $params[] = $page['id'];

            $sql = "UPDATE pages SET " . implode(', ', $sets) . " WHERE id = ?";
            $pdo->prepare($sql)->execute($params);

            // Fetch updated page
            $stmt = $pdo->prepare("SELECT id, title, slug, excerpt, status, created_at, updated_at FROM pages WHERE id = ?");
            $stmt->execute([$page['id']]);
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
                'message' => 'Page updated successfully'
            ]);

        } catch (\Throwable $e) {
            error_log("Pages API update error: " . $e->getMessage());
            \Core\api_error('Failed to update page', 500);
        }
    }

    /**
     * DELETE /api/v1/pages/{slug} — Delete page
     */
    public function destroy(?Request $request = null): void {
        \Core\api_authenticate('pages:write');

        $slug = $request?->getParam('slug') ?? null;
        if (!$slug) {
            \Core\api_error('Page slug is required', 400);
        }

        try {
            $pdo = \core\Database::connection();

            $stmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ? LIMIT 1");
            $stmt->execute([$slug]);
            $page = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$page) {
                \Core\api_error('Page not found', 404);
            }

            $pdo->prepare("DELETE FROM pages WHERE id = ?")->execute([$page['id']]);

            // 204 No Content
            \Core\api_cors_headers();
            http_response_code(204);
            exit;

        } catch (\Throwable $e) {
            error_log("Pages API delete error: " . $e->getMessage());
            \Core\api_error('Failed to delete page', 500);
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
