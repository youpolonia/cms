<?php
/**
 * Dynamic Context Manager
 * Manages dynamic data context for theme modules
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Dynamic_Context
{
    private static array $context = [];
    private static bool $initialized = false;

    /**
     * Initialize context from current request
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        self::$context = self::buildContext();
        self::$initialized = true;
    }

    /**
     * Set full context
     */
    public static function set(array $context): void
    {
        self::$context = $context;
        self::$initialized = true;
    }

    /**
     * Get context value
     */
    public static function get(?string $key = null, $default = null)
    {
        self::init();

        if ($key === null) {
            return self::$context;
        }

        // Support dot notation: post.title
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $value = self::$context;
            foreach ($keys as $k) {
                if (!isset($value[$k])) {
                    return $default;
                }
                $value = $value[$k];
            }
            return $value;
        }

        return self::$context[$key] ?? $default;
    }

    /**
     * Get post data
     */
    public static function getPost(): ?array
    {
        self::init();
        return self::$context['post'] ?? null;
    }

    /**
     * Get archive data
     */
    public static function getArchive(): ?array
    {
        self::init();
        return self::$context['archive'] ?? null;
    }

    /**
     * Get site data
     */
    public static function getSite(): array
    {
        self::init();
        return self::$context['site'] ?? self::loadSiteData();
    }

    /**
     * Get author data
     */
    public static function getAuthor(): ?array
    {
        self::init();
        return self::$context['author'] ?? null;
    }

    /**
     * Check if current context is archive
     */
    public static function isArchive(): bool
    {
        self::init();
        return self::$context['is_archive'] ?? false;
    }

    /**
     * Check if current context is search
     */
    public static function isSearch(): bool
    {
        self::init();
        return self::$context['is_search'] ?? false;
    }

    /**
     * Check if current context is 404
     */
    public static function is404(): bool
    {
        self::init();
        return self::$context['is_404'] ?? false;
    }

    /**
     * Check if current context is homepage
     */
    public static function isHomepage(): bool
    {
        self::init();
        return self::$context['is_homepage'] ?? false;
    }

    /**
     * Check if current context is single post
     */
    public static function isSinglePost(): bool
    {
        self::init();
        return self::$context['is_single'] ?? false;
    }

    /**
     * Check if current context is single page
     */
    public static function isSinglePage(): bool
    {
        self::init();
        return self::$context['is_page'] ?? false;
    }

    /**
     * Get page type
     */
    public static function getPageType(): string
    {
        self::init();
        return self::$context['page_type'] ?? 'unknown';
    }

    /**
     * Get current URL path
     */
    public static function getPath(): string
    {
        self::init();
        return self::$context['path'] ?? '/';
    }

    /**
     * Build context from current request
     */
    private static function buildContext(): array
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $qpos = strpos($uri, '?');
        if ($qpos !== false) {
            $uri = substr($uri, 0, $qpos);
        }

        $context = [
            'path' => $uri,
            'page_type' => 'unknown',
            'is_homepage' => false,
            'is_single' => false,
            'is_page' => false,
            'is_archive' => false,
            'is_search' => false,
            'is_404' => false,
            'post' => null,
            'archive' => null,
            'author' => null,
            'site' => self::loadSiteData(),
            'query' => $_GET
        ];

        // Detect page type from URI
        if ($uri === '/' || $uri === '/index.php' || $uri === '/home') {
            $context['page_type'] = 'homepage';
            $context['is_homepage'] = true;
            return $context;
        }

        // Search
        if (isset($_GET['s']) || preg_match('#^/search#', $uri)) {
            $context['page_type'] = 'search';
            $context['is_search'] = true;
            $context['search_query'] = $_GET['s'] ?? $_GET['q'] ?? '';
            return $context;
        }

        // Category archive
        if (preg_match('#^/category/([^/]+)/?$#', $uri, $m)) {
            $context['page_type'] = 'category_archive';
            $context['is_archive'] = true;
            $context['archive'] = self::loadCategory($m[1]);
            return $context;
        }

        // Tag archive
        if (preg_match('#^/tag/([^/]+)/?$#', $uri, $m)) {
            $context['page_type'] = 'tag_archive';
            $context['is_archive'] = true;
            $context['archive'] = self::loadTag($m[1]);
            return $context;
        }

        // Author archive
        if (preg_match('#^/author/([^/]+)/?$#', $uri, $m)) {
            $context['page_type'] = 'author_archive';
            $context['is_archive'] = true;
            $context['author'] = self::loadAuthorBySlug($m[1]);
            $context['archive'] = [
                'type' => 'author',
                'title' => $context['author']['name'] ?? 'Author',
                'description' => $context['author']['bio'] ?? ''
            ];
            return $context;
        }

        // Date archive
        if (preg_match('#^/(\d{4})(?:/(\d{2}))?(?:/(\d{2}))?/?$#', $uri, $m)) {
            $context['page_type'] = 'date_archive';
            $context['is_archive'] = true;
            $context['archive'] = [
                'type' => 'date',
                'year' => $m[1],
                'month' => $m[2] ?? null,
                'day' => $m[3] ?? null,
                'title' => self::formatDateArchiveTitle($m[1], $m[2] ?? null, $m[3] ?? null)
            ];
            return $context;
        }

        // Single article/post
        if (preg_match('#^/articles?/([^/]+)/?$#', $uri, $m)) {
            $context['page_type'] = 'single_post';
            $context['is_single'] = true;
            $context['post'] = self::loadArticleBySlug($m[1]);
            if ($context['post']) {
                $context['author'] = self::loadAuthorById($context['post']['author_id'] ?? null);
            }
            return $context;
        }

        // Blog index
        if (preg_match('#^/blog/?$#', $uri)) {
            $context['page_type'] = 'blog_archive';
            $context['is_archive'] = true;
            $context['archive'] = [
                'type' => 'blog',
                'title' => 'Blog',
                'description' => ''
            ];
            return $context;
        }

        // Single page (fallback)
        $slug = trim($uri, '/');
        $page = self::loadPageBySlug($slug);
        if ($page) {
            $context['page_type'] = 'single_page';
            $context['is_page'] = true;
            $context['post'] = $page;
            return $context;
        }

        // 404
        if (http_response_code() === 404) {
            $context['page_type'] = '404';
            $context['is_404'] = true;
        }

        return $context;
    }

    /**
     * Load site data
     */
    private static function loadSiteData(): array
    {
        try {
            $db = \core\Database::connection();
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_title', 'site_tagline', 'site_logo', 'site_url')");
            $settings = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }

            return [
                'title' => $settings['site_title'] ?? 'Site Title',
                'tagline' => $settings['site_tagline'] ?? '',
                'logo' => $settings['site_logo'] ?? '',
                'url' => $settings['site_url'] ?? ''
            ];
        } catch (\Exception $e) {
            return [
                'title' => 'Site Title',
                'tagline' => '',
                'logo' => '',
                'url' => ''
            ];
        }
    }

    /**
     * Load category by slug
     */
    private static function loadCategory(string $slug): ?array
    {
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("SELECT id, name, slug, description FROM categories WHERE slug = ?");
            $stmt->execute([$slug]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'type' => 'category',
                    'id' => (int)$row['id'],
                    'title' => $row['name'],
                    'slug' => $row['slug'],
                    'description' => $row['description'] ?? ''
                ];
            }
        } catch (\Exception $e) {}
        return null;
    }

    /**
     * Load tag by slug
     */
    private static function loadTag(string $slug): ?array
    {
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("SELECT id, name, slug, description FROM tags WHERE slug = ?");
            $stmt->execute([$slug]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'type' => 'tag',
                    'id' => (int)$row['id'],
                    'title' => $row['name'],
                    'slug' => $row['slug'],
                    'description' => $row['description'] ?? ''
                ];
            }
        } catch (\Exception $e) {}
        return null;
    }

    /**
     * Load author by slug
     */
    private static function loadAuthorBySlug(string $slug): ?array
    {
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("SELECT id, username, email, display_name, bio, avatar FROM users WHERE username = ?");
            $stmt->execute([$slug]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'id' => (int)$row['id'],
                    'username' => $row['username'],
                    'name' => $row['display_name'] ?? $row['username'],
                    'email' => $row['email'],
                    'bio' => $row['bio'] ?? '',
                    'avatar' => $row['avatar'] ?? ''
                ];
            }
        } catch (\Exception $e) {}
        return null;
    }

    /**
     * Load author by ID
     */
    private static function loadAuthorById(?int $id): ?array
    {
        if (!$id) return null;
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("SELECT id, username, email, display_name, bio, avatar FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'id' => (int)$row['id'],
                    'username' => $row['username'],
                    'name' => $row['display_name'] ?? $row['username'],
                    'email' => $row['email'],
                    'bio' => $row['bio'] ?? '',
                    'avatar' => $row['avatar'] ?? ''
                ];
            }
        } catch (\Exception $e) {}
        return null;
    }

    /**
     * Load article by slug
     */
    private static function loadArticleBySlug(string $slug): ?array
    {
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("
                SELECT a.*, GROUP_CONCAT(DISTINCT c.name) as categories, GROUP_CONCAT(DISTINCT t.name) as tags
                FROM articles a
                LEFT JOIN article_categories ac ON a.id = ac.article_id
                LEFT JOIN categories c ON ac.category_id = c.id
                LEFT JOIN article_tags at ON a.id = at.article_id
                LEFT JOIN tags t ON at.tag_id = t.id
                WHERE a.slug = ? AND a.status = 'published'
                GROUP BY a.id
            ");
            $stmt->execute([$slug]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'id' => (int)$row['id'],
                    'title' => $row['title'],
                    'slug' => $row['slug'],
                    'content' => $row['content'],
                    'excerpt' => $row['excerpt'] ?? '',
                    'featured_image' => $row['featured_image'] ?? '',
                    'author_id' => (int)($row['author_id'] ?? 0),
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'] ?? $row['created_at'],
                    'categories' => $row['categories'] ? explode(',', $row['categories']) : [],
                    'tags' => $row['tags'] ? explode(',', $row['tags']) : [],
                    'type' => 'article',
                    'url' => '/article/' . $row['slug']
                ];
            }
        } catch (\Exception $e) {}
        return null;
    }

    /**
     * Load page by slug
     */
    private static function loadPageBySlug(string $slug): ?array
    {
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'published'");
            $stmt->execute([$slug]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'id' => (int)$row['id'],
                    'title' => $row['title'],
                    'slug' => $row['slug'],
                    'content' => $row['content'],
                    'featured_image' => $row['featured_image'] ?? '',
                    'template' => $row['template'] ?? 'default',
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'] ?? $row['created_at'],
                    'type' => 'page',
                    'url' => '/' . $row['slug']
                ];
            }
        } catch (\Exception $e) {}
        return null;
    }

    /**
     * Format date archive title
     */
    private static function formatDateArchiveTitle(string $year, ?string $month, ?string $day): string
    {
        if ($day && $month) {
            return date('F j, Y', strtotime("$year-$month-$day"));
        } elseif ($month) {
            return date('F Y', strtotime("$year-$month-01"));
        }
        return $year;
    }

    /**
     * Reset context (for testing)
     */
    public static function reset(): void
    {
        self::$context = [];
        self::$initialized = false;
    }
}
