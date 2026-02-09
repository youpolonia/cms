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

    // ========================================
    // HELPER METHODS FOR THEME MODULES
    // Added 2026-02-04
    // ========================================

    /**
     * Get post title
     */
    public static function getPostTitle(): string
    {
        $post = self::getPost();
        return $post['title'] ?? '';
    }

    /**
     * Get post content
     */
    public static function getPostContent(): string
    {
        $post = self::getPost();
        return $post['content'] ?? '';
    }

    /**
     * Get post excerpt
     */
    public static function getPostExcerpt(int $length = 150): string
    {
        $post = self::getPost();
        if (!empty($post['excerpt'])) {
            return $post['excerpt'];
        }
        // Generate from content
        $content = strip_tags($post['content'] ?? '');
        if (strlen($content) > $length) {
            return substr($content, 0, $length) . '...';
        }
        return $content;
    }

    /**
     * Get featured image URL
     */
    public static function getFeaturedImage(): string
    {
        $post = self::getPost();
        return $post['featured_image'] ?? '';
    }

    /**
     * Get post date formatted
     */
    public static function getPostDate(string $format = 'F j, Y'): string
    {
        $post = self::getPost();
        if (!empty($post['created_at'])) {
            return date($format, strtotime($post['created_at']));
        }
        return '';
    }

    /**
     * Get post categories
     */
    public static function getPostCategories(): array
    {
        $post = self::getPost();
        return $post['categories'] ?? [];
    }

    /**
     * Get post tags
     */
    public static function getPostTags(): array
    {
        $post = self::getPost();
        return $post['tags'] ?? [];
    }

    /**
     * Get post URL
     */
    public static function getPostUrl(): string
    {
        $post = self::getPost();
        return $post['url'] ?? '';
    }

    /**
     * Get author name
     */
    public static function getAuthorName(): string
    {
        $author = self::getAuthor();
        return $author['name'] ?? '';
    }

    /**
     * Get author bio
     */
    public static function getAuthorBio(): string
    {
        $author = self::getAuthor();
        return $author['bio'] ?? '';
    }

    /**
     * Get author avatar
     */
    public static function getAuthorAvatar(): string
    {
        $author = self::getAuthor();
        return $author['avatar'] ?? '';
    }

    /**
     * Get author URL
     */
    public static function getAuthorUrl(): string
    {
        $author = self::getAuthor();
        if (!empty($author['username'])) {
            return '/author/' . $author['username'];
        }
        return '';
    }

    /**
     * Get author role/title
     */
    public static function getAuthorRole(): string
    {
        $author = self::getAuthor();
        return $author['role'] ?? $author['title'] ?? '';
    }

    /**
     * Get author social links
     */
    public static function getAuthorSocial(): array
    {
        $author = self::getAuthor();
        if (!empty($author['social'])) {
            return $author['social'];
        }
        // Try to get from user meta
        if (!empty($author['id'])) {
            try {
                $db = \core\Database::connection();
                $stmt = $db->prepare("SELECT meta_key, meta_value FROM user_meta WHERE user_id = ? AND meta_key LIKE 'social_%'");
                $stmt->execute([$author['id']]);
                $social = [];
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    $key = str_replace('social_', '', $row['meta_key']);
                    $social[$key] = $row['meta_value'];
                }
                return $social;
            } catch (\Exception $e) {}
        }
        return [];
    }

    // NOTE: getArchiveTitle() and getArchiveDescription() moved to end of class (line 1020+)

    /**
     * Get site logo URL
     */
    public static function getSiteLogo(): string
    {
        $site = self::getSite();
        return $site['logo'] ?? '';
    }

    /**
     * Get site title
     */
    public static function getSiteTitle(): string
    {
        $site = self::getSite();
        return $site['title'] ?? '';
    }

    /**
     * Get site tagline
     */
    public static function getSiteTagline(): string
    {
        $site = self::getSite();
        return $site['tagline'] ?? '';
    }

    /**
     * Get site social media URLs from settings
     */
    public static function getSiteSocial(): array
    {
        try {
            $db = \core\Database::connection();
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'social_%'");
            $social = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $key = str_replace('social_', '', $row['setting_key']);
                $social[$key] = $row['setting_value'];
            }
            return $social;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get breadcrumbs
     */
    public static function getBreadcrumbs(): array
    {
        self::init();
        $crumbs = [['label' => 'Home', 'url' => '/']];

        if (self::isArchive()) {
            $archive = self::getArchive();
            $type = $archive['type'] ?? '';

            if ($type === 'category') {
                $crumbs[] = ['label' => 'Categories', 'url' => '/categories'];
                $crumbs[] = ['label' => $archive['title'] ?? 'Category', 'url' => '/category/' . ($archive['slug'] ?? '')];
            } elseif ($type === 'tag') {
                $crumbs[] = ['label' => 'Tags', 'url' => '/tags'];
                $crumbs[] = ['label' => $archive['title'] ?? 'Tag', 'url' => '/tag/' . ($archive['slug'] ?? '')];
            } elseif ($type === 'author') {
                $crumbs[] = ['label' => 'Authors', 'url' => '/authors'];
                $author = self::getAuthor();
                $crumbs[] = ['label' => $author['name'] ?? 'Author', 'url' => self::getAuthorUrl()];
            } elseif ($type === 'blog') {
                $crumbs[] = ['label' => 'Blog', 'url' => '/blog'];
            }
        } elseif (self::isSinglePost()) {
            $post = self::getPost();
            $crumbs[] = ['label' => 'Blog', 'url' => '/blog'];
            $cats = $post['categories'] ?? [];
            if (!empty($cats[0])) {
                $crumbs[] = ['label' => $cats[0], 'url' => '/category/' . strtolower(str_replace(' ', '-', $cats[0]))];
            }
            $crumbs[] = ['label' => $post['title'] ?? 'Post', 'url' => ''];
        } elseif (self::isSinglePage()) {
            $post = self::getPost();
            $crumbs[] = ['label' => $post['title'] ?? 'Page', 'url' => ''];
        } elseif (self::isSearch()) {
            $crumbs[] = ['label' => 'Search Results', 'url' => ''];
        } elseif (self::is404()) {
            $crumbs[] = ['label' => 'Page Not Found', 'url' => ''];
        }

        return $crumbs;
    }

    /**
     * Get related posts
     */
    public static function getRelatedPosts(int $count = 3): array
    {
        $post = self::getPost();
        if (!$post) return [];

        try {
            $db = \core\Database::connection();
            $categories = $post['categories'] ?? [];
            $postId = $post['id'] ?? 0;

            if (empty($categories)) {
                // Fallback: recent posts
                $stmt = $db->prepare("
                    SELECT id, title, slug, excerpt, featured_image, created_at
                    FROM articles
                    WHERE status = 'published' AND id != ?
                    ORDER BY created_at DESC
                    LIMIT ?
                ");
                $stmt->execute([$postId, $count]);
            } else {
                // Related by category
                $placeholders = implode(',', array_fill(0, count($categories), '?'));
                $stmt = $db->prepare("
                    SELECT DISTINCT a.id, a.title, a.slug, a.excerpt, a.featured_image, a.created_at
                    FROM articles a
                    JOIN article_categories ac ON a.id = ac.article_id
                    JOIN categories c ON ac.category_id = c.id
                    WHERE a.status = 'published'
                      AND a.id != ?
                      AND c.name IN ({$placeholders})
                    ORDER BY a.created_at DESC
                    LIMIT ?
                ");
                $params = array_merge([$postId], $categories, [$count]);
                $stmt->execute($params);
            }

            $posts = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $posts[] = [
                    'id' => (int)$row['id'],
                    'title' => $row['title'],
                    'slug' => $row['slug'],
                    'excerpt' => $row['excerpt'] ?? '',
                    'featured_image' => $row['featured_image'] ?? '',
                    'date' => date('F j, Y', strtotime($row['created_at'])),
                    'url' => '/article/' . $row['slug']
                ];
            }
            return $posts;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get archive posts
     */
    public static function getArchivePosts(int $count = 10, int $offset = 0): array
    {
        self::init();
        $archive = self::getArchive();
        if (!$archive) return [];

        try {
            $db = \core\Database::connection();
            $type = $archive['type'] ?? '';
            $id = $archive['id'] ?? 0;

            $query = "SELECT a.id, a.title, a.slug, a.excerpt, a.featured_image, a.created_at
                      FROM articles a";
            $params = [];

            if ($type === 'category' && $id) {
                $query .= " JOIN article_categories ac ON a.id = ac.article_id
                            WHERE ac.category_id = ? AND a.status = 'published'";
                $params[] = $id;
            } elseif ($type === 'tag' && $id) {
                $query .= " JOIN article_tags at ON a.id = at.article_id
                            WHERE at.tag_id = ? AND a.status = 'published'";
                $params[] = $id;
            } elseif ($type === 'author') {
                $author = self::getAuthor();
                $query .= " WHERE a.author_id = ? AND a.status = 'published'";
                $params[] = $author['id'] ?? 0;
            } else {
                $query .= " WHERE a.status = 'published'";
            }

            $query .= " ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $count;
            $params[] = $offset;

            $stmt = $db->prepare($query);
            $stmt->execute($params);

            $posts = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $posts[] = [
                    'id' => (int)$row['id'],
                    'title' => $row['title'],
                    'slug' => $row['slug'],
                    'excerpt' => $row['excerpt'] ?? '',
                    'featured_image' => $row['featured_image'] ?? '',
                    'date' => date('F j, Y', strtotime($row['created_at'])),
                    'url' => '/article/' . $row['slug']
                ];
            }
            return $posts;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get menu items for a location
     */
    public static function getMenuItems(string $location = 'primary'): array
    {
        try {
            $db = \core\Database::connection();

            // Try to get menu by location
            $stmt = $db->prepare("
                SELECT m.id, m.name
                FROM menus m
                WHERE m.location = ? OR m.slug = ?
                LIMIT 1
            ");
            $stmt->execute([$location, $location]);
            $menu = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$menu) {
                // Fallback: get first menu
                $stmt = $db->query("SELECT id FROM menus LIMIT 1");
                $menu = $stmt->fetch(\PDO::FETCH_ASSOC);
            }

            if (!$menu) return [];

            // Get menu items
            $stmt = $db->prepare("
                SELECT id, parent_id, title, url, target, icon, position
                FROM menu_items
                WHERE menu_id = ?
                ORDER BY parent_id, position
            ");
            $stmt->execute([$menu['id']]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Build tree
            $items = [];
            $byParent = [];
            foreach ($rows as $row) {
                $parentId = (int)($row['parent_id'] ?? 0);
                $byParent[$parentId][] = [
                    'id' => (int)$row['id'],
                    'title' => $row['title'],
                    'url' => $row['url'],
                    'target' => $row['target'] ?? '_self',
                    'icon' => $row['icon'] ?? '',
                    'children' => []
                ];
            }

            // Recursive build
            $buildTree = function($parentId) use (&$byParent, &$buildTree) {
                $items = $byParent[$parentId] ?? [];
                foreach ($items as &$item) {
                    $item['children'] = $buildTree($item['id']);
                }
                return $items;
            };

            return $buildTree(0);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Check if in builder/preview mode
     */
    public static function isPreviewMode(): bool
    {
        return !empty($_GET['jtb_preview']) || !empty($_GET['preview']);
    }

    /**
     * Get placeholder content for preview mode
     */
    public static function getPlaceholder(string $type): string
    {
        $placeholders = [
            'title' => 'Your Dynamic Post Title Will Display Here',
            'content' => '<p>This is placeholder content that will be replaced with your actual post content when viewing the page on the frontend.</p>',
            'excerpt' => 'This is a sample excerpt that will be replaced with your actual post excerpt...',
            'author' => 'John Doe',
            'date' => date('F j, Y'),
            'category' => 'Category',
            'featured_image' => '/uploads/jtb/placeholder-featured.jpg',
            'archive_title' => 'Archive Title',
            'search_query' => 'Search Query'
        ];
        return $placeholders[$type] ?? '';
    }

    /**
     * Get search URL
     * Returns the URL for search form action
     *
     * @return string Search URL (default: /search)
     */
    public static function getSearchUrl(): string
    {
        // Try to get from settings
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'search_url'");
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($result && !empty($result['setting_value'])) {
                return $result['setting_value'];
            }
        } catch (\Exception $e) {}

        // Default search URL
        return '/search';
    }

    /**
     * Get archive title
     * Returns the title for archive pages (category, tag, author, date)
     *
     * @return string Archive title
     */
    public static function getArchiveTitle(): string
    {
        self::init();
        $archive = self::getArchive();

        if (!$archive) {
            return 'Archive';
        }

        $type = $archive['type'] ?? '';
        $title = $archive['title'] ?? '';

        switch ($type) {
            case 'category':
                return 'Category: ' . $title;
            case 'tag':
                return 'Tag: ' . $title;
            case 'author':
                $author = self::getAuthor();
                return 'Posts by ' . ($author['name'] ?? 'Author');
            case 'date':
                return $title ?: 'Archives';
            case 'blog':
                return 'Blog';
            default:
                return $title ?: 'Archive';
        }
    }

    /**
     * Get archive description
     * Returns the description for archive pages
     *
     * @return string Archive description
     */
    public static function getArchiveDescription(): string
    {
        self::init();
        $archive = self::getArchive();

        if (!$archive) {
            return '';
        }

        return $archive['description'] ?? '';
    }
}
