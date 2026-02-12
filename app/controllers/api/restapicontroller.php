<?php
declare(strict_types=1);

namespace App\Controllers\Api;

/**
 * REST API v1 Controller — Headless CMS endpoints
 * 
 * Authentication: API key via header `X-API-Key` or query `?api_key=`
 * All responses: JSON with consistent envelope { data, meta, error }
 * CORS enabled for all origins (configurable)
 */
class RestApiController
{
    private ?\PDO $pdo = null;

    private function db(): \PDO
    {
        if (!$this->pdo) {
            $this->pdo = \Core\Database::connection();
        }
        return $this->pdo;
    }

    /**
     * Authenticate via API key
     * Returns true if valid, sends 401 and exits if not
     */
    private function authenticate(): bool
    {
        // Set CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: X-API-Key, Content-Type, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Content-Type: application/json; charset=utf-8');

        // Handle preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        // Get API key from header or query
        $apiKey = $_SERVER['HTTP_X_API_KEY'] 
            ?? $_GET['api_key'] 
            ?? '';

        if (empty($apiKey)) {
            $this->error(401, 'API key required. Pass via X-API-Key header or ?api_key= parameter.');
        }

        // Validate against database
        $stmt = $this->db()->prepare(
            "SELECT id, name, permissions, is_active FROM api_keys WHERE api_key = :key LIMIT 1"
        );
        $stmt->execute(['key' => $apiKey]);
        $key = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$key || !$key['is_active']) {
            $this->error(401, 'Invalid or inactive API key.');
        }

        // Update last_used
        $this->db()->prepare("UPDATE api_keys SET last_used_at = NOW(), request_count = request_count + 1 WHERE id = :id")
            ->execute(['id' => $key['id']]);

        // Store permissions for later use
        $GLOBALS['_api_key'] = $key;
        return true;
    }

    /**
     * Check if current API key has a specific permission
     */
    private function can(string $permission): bool
    {
        $perms = json_decode($GLOBALS['_api_key']['permissions'] ?? '[]', true) ?: [];
        return in_array('*', $perms) || in_array($permission, $perms);
    }

    /**
     * Send JSON success response
     */
    private function json(mixed $data, array $meta = [], int $code = 200): never
    {
        http_response_code($code);
        $response = ['data' => $data];
        if (!empty($meta)) {
            $response['meta'] = $meta;
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send JSON error response
     */
    private function error(int $code, string $message): never
    {
        http_response_code($code);
        echo json_encode(['error' => $message, 'code' => $code], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Parse pagination params
     */
    private function pagination(): array
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(100, max(1, (int)($_GET['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;
        return [$page, $perPage, $offset];
    }

    /**
     * Build pagination meta
     */
    private function paginationMeta(int $total, int $page, int $perPage): array
    {
        return [
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int)ceil($total / $perPage),
        ];
    }

    /**
     * Select specific fields if ?fields= is set
     */
    private function selectFields(array $item, ?string $fieldsParam): array
    {
        if (!$fieldsParam) return $item;
        $fields = array_map('trim', explode(',', $fieldsParam));
        return array_intersect_key($item, array_flip($fields));
    }

    // ─── PUBLIC ENDPOINTS (read-only, API key required) ───

    /**
     * GET /api/v1/site — Site metadata
     */
    public function site(): void
    {
        $this->authenticate();

        $settings = [];
        $rows = $this->db()->query("SELECT `key`, `value` FROM settings WHERE `key` IN ('site_name','site_description','site_url','admin_email','language')")->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $settings[$r['key']] = $r['value'];
        }

        $theme = get_active_theme();

        $this->json([
            'site_name' => $settings['site_name'] ?? 'Jessie AI-CMS',
            'site_description' => $settings['site_description'] ?? '',
            'site_url' => $settings['site_url'] ?? '',
            'language' => $settings['language'] ?? 'en',
            'active_theme' => $theme,
        ]);
    }

    /**
     * GET /api/v1/pages — List published pages
     */
    public function pages(): void
    {
        $this->authenticate();
        [$page, $perPage, $offset] = $this->pagination();
        $fields = $_GET['fields'] ?? null;
        $status = $_GET['status'] ?? 'published';

        // Count
        $countStmt = $this->db()->prepare("SELECT COUNT(*) FROM pages WHERE status = :status");
        $countStmt->execute(['status' => $status]);
        $total = (int)$countStmt->fetchColumn();

        // Fetch
        $stmt = $this->db()->prepare(
            "SELECT id, title, slug, excerpt, content, featured_image, status, template, 
                    meta_title, meta_description, parent_id, menu_order, theme_slug,
                    created_at, updated_at 
             FROM pages WHERE status = :status 
             ORDER BY menu_order ASC, created_at DESC 
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue('status', $status);
        $stmt->bindValue('limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if ($fields) {
            $rows = array_map(fn($r) => $this->selectFields($r, $fields), $rows);
        }

        $this->json($rows, $this->paginationMeta($total, $page, $perPage));
    }

    /**
     * GET /api/v1/pages/{slug} — Single page by slug
     */
    public function page(string $slug = ''): void
    {
        $this->authenticate();

        $stmt = $this->db()->prepare(
            "SELECT id, title, slug, excerpt, content, featured_image, status, template,
                    meta_title, meta_description, parent_id, menu_order, theme_slug,
                    created_at, updated_at 
             FROM pages WHERE slug = :slug LIMIT 1"
        );
        $stmt->execute(['slug' => $slug]);
        $page = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$page) {
            $this->error(404, "Page not found: {$slug}");
        }

        $fields = $_GET['fields'] ?? null;
        $this->json($fields ? $this->selectFields($page, $fields) : $page);
    }

    /**
     * GET /api/v1/articles — List published articles
     */
    public function articles(): void
    {
        $this->authenticate();
        [$page, $perPage, $offset] = $this->pagination();
        $fields = $_GET['fields'] ?? null;
        $category = $_GET['category'] ?? null;
        $status = $_GET['status'] ?? 'published';

        $where = "a.status = :status";
        $params = ['status' => $status];

        if ($category) {
            $where .= " AND c.slug = :cat";
            $params['cat'] = $category;
        }

        $countSql = "SELECT COUNT(*) FROM articles a LEFT JOIN article_categories c ON a.category_id = c.id WHERE {$where}";
        $countStmt = $this->db()->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sql = "SELECT a.id, a.title, a.slug, a.excerpt, a.content, a.featured_image, 
                       a.status, a.category_id, c.name AS category_name, c.slug AS category_slug,
                       a.meta_title, a.meta_description, a.theme_slug,
                       a.created_at, a.updated_at 
                FROM articles a 
                LEFT JOIN article_categories c ON a.category_id = c.id 
                WHERE {$where}
                ORDER BY a.created_at DESC 
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db()->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue('limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if ($fields) {
            $rows = array_map(fn($r) => $this->selectFields($r, $fields), $rows);
        }

        $this->json($rows, $this->paginationMeta($total, $page, $perPage));
    }

    /**
     * GET /api/v1/articles/{slug} — Single article
     */
    public function article(string $slug = ''): void
    {
        $this->authenticate();

        $stmt = $this->db()->prepare(
            "SELECT a.id, a.title, a.slug, a.excerpt, a.content, a.featured_image,
                    a.status, a.category_id, c.name AS category_name, c.slug AS category_slug,
                    a.meta_title, a.meta_description, a.theme_slug,
                    a.created_at, a.updated_at 
             FROM articles a 
             LEFT JOIN article_categories c ON a.category_id = c.id 
             WHERE a.slug = :slug LIMIT 1"
        );
        $stmt->execute(['slug' => $slug]);
        $article = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$article) {
            $this->error(404, "Article not found: {$slug}");
        }

        $fields = $_GET['fields'] ?? null;
        $this->json($fields ? $this->selectFields($article, $fields) : $article);
    }

    /**
     * GET /api/v1/menus/{location} — Menu items by location
     */
    public function menu(string $location = ''): void
    {
        $this->authenticate();

        // Find menu for location
        $stmt = $this->db()->prepare(
            "SELECT id, name, slug, location FROM menus WHERE location = :loc AND is_active = 1 LIMIT 1"
        );
        $stmt->execute(['loc' => $location]);
        $menu = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$menu) {
            $this->error(404, "No menu found for location: {$location}");
        }

        // Get items
        $itemStmt = $this->db()->prepare(
            "SELECT id, title, url, parent_id, sort_order AS position, css_class, target, icon 
             FROM menu_items WHERE menu_id = :mid ORDER BY sort_order ASC"
        );
        $itemStmt->execute(['mid' => $menu['id']]);
        $items = $itemStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Build tree
        $tree = $this->buildMenuTree($items);

        $this->json([
            'menu' => $menu,
            'items' => $tree,
        ]);
    }

    /**
     * GET /api/v1/menus — All menus
     */
    public function menus(): void
    {
        $this->authenticate();

        $rows = $this->db()->query(
            "SELECT id, name, slug, location, is_active, theme_slug FROM menus ORDER BY name ASC"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $this->json($rows);
    }

    /**
     * GET /api/v1/categories — All article categories
     */
    public function categories(): void
    {
        $this->authenticate();

        $rows = $this->db()->query(
            "SELECT id, name, slug, description, parent_id FROM article_categories ORDER BY name ASC"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $this->json($rows);
    }

    /**
     * GET /api/v1/media — Media library
     */
    public function media(): void
    {
        $this->authenticate();
        [$page, $perPage, $offset] = $this->pagination();
        $type = $_GET['type'] ?? null; // image, document, video

        $where = "1=1";
        $params = [];
        if ($type) {
            $where .= " AND mime_type LIKE :type";
            $params['type'] = $type . '/%';
        }

        $countStmt = $this->db()->prepare("SELECT COUNT(*) FROM media WHERE {$where}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sql = "SELECT id, filename, filepath, mime_type, alt_text, title, caption, filesize, created_at 
                FROM media WHERE {$where} ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db()->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue('limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->json($rows, $this->paginationMeta($total, $page, $perPage));
    }

    /**
     * GET /api/v1/search — Search across pages and articles
     */
    public function search(): void
    {
        $this->authenticate();
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            $this->error(400, 'Search query must be at least 2 characters.');
        }

        $searchTerm = '%' . $q . '%';
        $results = [];

        // Search pages
        $stmt = $this->db()->prepare(
            "SELECT 'page' AS type, id, title, slug, excerpt, updated_at 
             FROM pages WHERE status = 'published' AND (title LIKE :q1 OR content LIKE :q2 OR excerpt LIKE :q3) 
             ORDER BY updated_at DESC LIMIT 10"
        );
        $stmt->execute(['q1' => $searchTerm, 'q2' => $searchTerm, 'q3' => $searchTerm]);
        $results = array_merge($results, $stmt->fetchAll(\PDO::FETCH_ASSOC));

        // Search articles
        $stmt = $this->db()->prepare(
            "SELECT 'article' AS type, id, title, slug, excerpt, updated_at 
             FROM articles WHERE status = 'published' AND (title LIKE :q1 OR content LIKE :q2 OR excerpt LIKE :q3) 
             ORDER BY updated_at DESC LIMIT 10"
        );
        $stmt->execute(['q1' => $searchTerm, 'q2' => $searchTerm, 'q3' => $searchTerm]);
        $results = array_merge($results, $stmt->fetchAll(\PDO::FETCH_ASSOC));

        // Sort by relevance (title match first)
        usort($results, function($a, $b) use ($q) {
            $aTitle = stripos($a['title'], $q) !== false ? 0 : 1;
            $bTitle = stripos($b['title'], $q) !== false ? 0 : 1;
            return $aTitle - $bTitle;
        });

        $this->json($results, ['query' => $q, 'total' => count($results)]);
    }

    /**
     * GET /api/v1/theme — Current theme info + settings
     */
    public function theme(): void
    {
        $this->authenticate();
        $theme = get_active_theme();
        $themePath = \CMS_ROOT . '/themes/' . $theme;
        $themeJson = [];

        if (file_exists($themePath . '/theme.json')) {
            $themeJson = json_decode(file_get_contents($themePath . '/theme.json'), true) ?: [];
        }

        // Get Theme Studio overrides
        $stmt = $this->db()->prepare(
            "SELECT field_key AS `key`, field_value AS `value` FROM theme_customizations WHERE theme_slug = :theme"
        );
        $stmt->execute(['theme' => $theme]);
        $settings = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $settings[$row['key']] = $row['value'];
        }

        $this->json([
            'slug' => $theme,
            'name' => $themeJson['name'] ?? $theme,
            'version' => $themeJson['version'] ?? '1.0',
            'author' => $themeJson['author'] ?? '',
            'description' => $themeJson['description'] ?? '',
            'homepage_sections' => $themeJson['homepage_sections'] ?? [],
            'settings' => $settings,
        ]);
    }

    // ─── HELPERS ───

    private function buildMenuTree(array $items, int $parentId = 0): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ((int)($item['parent_id'] ?? 0) === $parentId) {
                $children = $this->buildMenuTree($items, (int)$item['id']);
                if ($children) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }
}
