<?php
/**
 * Template Matcher
 * Matches current request context to appropriate templates
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Template_Matcher
{
    private static ?array $cachedContext = null;
    private static array $cachedTemplates = [];

    /**
     * Get the header template for current request
     */
    public static function getHeader(): ?array
    {
        return self::findMatch('header');
    }

    /**
     * Get the footer template for current request
     */
    public static function getFooter(): ?array
    {
        return self::findMatch('footer');
    }

    /**
     * Get the body template for current request
     */
    public static function getBody(): ?array
    {
        return self::findMatch('body');
    }

    /**
     * Get all templates for current request
     */
    public static function getTemplates(): array
    {
        return [
            'header' => self::getHeader(),
            'footer' => self::getFooter(),
            'body' => self::getBody()
        ];
    }

    /**
     * Find the best matching template for a type
     */
    public static function findMatch(string $templateType): ?array
    {
        // Check cache first
        if (isset(self::$cachedTemplates[$templateType])) {
            return self::$cachedTemplates[$templateType];
        }

        $context = self::getRequestContext();

        // Get all templates of this type with their conditions
        $templates = self::getTemplatesWithConditions($templateType);

        if (empty($templates)) {
            // Return default template if exists
            $default = JTB_Templates::getDefault($templateType);
            self::$cachedTemplates[$templateType] = $default;
            return $default;
        }

        // Find the best match
        $bestMatch = null;
        $bestPriority = -1;

        foreach ($templates as $template) {
            // Check if excluded
            if (self::isExcluded($template, $context)) {
                continue;
            }

            // Check if matches
            if (self::matchesContext($template, $context)) {
                $priority = self::calculatePriority($template['conditions'], $context);

                if ($priority > $bestPriority) {
                    $bestPriority = $priority;
                    $bestMatch = $template;
                }
            }
        }

        // If no match found, try default
        if ($bestMatch === null) {
            $bestMatch = JTB_Templates::getDefault($templateType);
        }

        self::$cachedTemplates[$templateType] = $bestMatch;
        return $bestMatch;
    }

    /**
     * Get current request context
     */
    public static function getRequestContext(): array
    {
        if (self::$cachedContext !== null) {
            return self::$cachedContext;
        }

        $context = [
            'page_type' => 'unknown',
            'object_id' => null,
            'post_type' => null,
            'taxonomy' => null,
            'term_id' => null,
            'author_id' => null,
            'is_home' => false,
            'is_404' => false,
            'is_search' => false
        ];

        // Get URI
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $qpos = strpos($uri, '?');
        if ($qpos !== false) {
            $uri = substr($uri, 0, $qpos);
        }

        // Check for homepage
        if ($uri === '/' || $uri === '/index.php') {
            $context['page_type'] = 'homepage';
            $context['is_home'] = true;
            self::$cachedContext = $context;
            return $context;
        }

        // Check for 404
        if (http_response_code() === 404) {
            $context['page_type'] = '404';
            $context['is_404'] = true;
            self::$cachedContext = $context;
            return $context;
        }

        // Check for search
        if (isset($_GET['s']) || preg_match('#^/search#', $uri)) {
            $context['page_type'] = 'search';
            $context['is_search'] = true;
            self::$cachedContext = $context;
            return $context;
        }

        // Try to detect from URI patterns
        $context = self::detectFromUri($uri, $context);

        // Try to detect from current page/post data
        $context = self::detectFromPageData($context);

        self::$cachedContext = $context;
        return $context;
    }

    /**
     * Detect page type from URI
     */
    private static function detectFromUri(string $uri, array $context): array
    {
        // Category archive
        if (preg_match('#^/category/([^/]+)/?$#', $uri, $matches)) {
            $context['page_type'] = 'category';
            $context['taxonomy'] = 'category';
            // Try to get category ID from slug
            $categoryId = self::getCategoryIdBySlug($matches[1]);
            if ($categoryId) {
                $context['term_id'] = $categoryId;
                $context['object_id'] = $categoryId;
            }
            return $context;
        }

        // Tag archive
        if (preg_match('#^/tag/([^/]+)/?$#', $uri, $matches)) {
            $context['page_type'] = 'tag';
            $context['taxonomy'] = 'tag';
            $tagId = self::getTagIdBySlug($matches[1]);
            if ($tagId) {
                $context['term_id'] = $tagId;
                $context['object_id'] = $tagId;
            }
            return $context;
        }

        // Author archive
        if (preg_match('#^/author/([^/]+)/?$#', $uri, $matches)) {
            $context['page_type'] = 'author';
            $authorId = self::getAuthorIdBySlug($matches[1]);
            if ($authorId) {
                $context['author_id'] = $authorId;
                $context['object_id'] = $authorId;
            }
            return $context;
        }

        // Archive (date-based)
        if (preg_match('#^/\d{4}(/\d{2})?(/\d{2})?/?$#', $uri)) {
            $context['page_type'] = 'archive';
            return $context;
        }

        return $context;
    }

    /**
     * Detect page type from current page data
     */
    private static function detectFromPageData(array $context): array
    {
        // If we already determined type, skip
        if ($context['page_type'] !== 'unknown') {
            return $context;
        }

        // Try to get current post/page from global or request
        $postId = $_GET['id'] ?? $_GET['post_id'] ?? null;

        if ($postId) {
            $postData = self::getPostData((int)$postId);

            if ($postData) {
                if ($postData['type'] === 'page') {
                    $context['page_type'] = 'single_page';
                    $context['post_type'] = 'page';
                } else {
                    $context['page_type'] = 'single_post';
                    $context['post_type'] = $postData['type'] ?? 'post';
                }
                $context['object_id'] = (int)$postId;
            }
        }

        return $context;
    }

    /**
     * Get templates with their conditions
     */
    private static function getTemplatesWithConditions(string $type): array
    {
        $templates = JTB_Templates::getAll($type);

        foreach ($templates as &$template) {
            $template['conditions'] = JTB_Template_Conditions::getForTemplate($template['id']);
            $template['include_conditions'] = array_filter($template['conditions'], fn($c) => $c['condition_type'] === 'include');
            $template['exclude_conditions'] = array_filter($template['conditions'], fn($c) => $c['condition_type'] === 'exclude');
        }

        return $templates;
    }

    /**
     * Check if template is excluded for context
     */
    private static function isExcluded(array $template, array $context): bool
    {
        if (empty($template['exclude_conditions'])) {
            return false;
        }

        foreach ($template['exclude_conditions'] as $condition) {
            if (self::conditionMatchesContext($condition, $context)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if template matches context
     */
    private static function matchesContext(array $template, array $context): bool
    {
        // No include conditions = matches all (if is_default)
        if (empty($template['include_conditions'])) {
            return !empty($template['is_default']);
        }

        // Check if any include condition matches
        foreach ($template['include_conditions'] as $condition) {
            if (self::conditionMatchesContext($condition, $context)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a single condition matches context
     */
    private static function conditionMatchesContext(array $condition, array $context): bool
    {
        $pageType = $condition['page_type'] ?? '';
        $objectId = $condition['object_id'] ?? null;

        // "All" matches everything
        if ($pageType === 'all') {
            return true;
        }

        // Check page type match
        if ($context['page_type'] !== $pageType) {
            return false;
        }

        // If condition has specific object ID, it must match
        if ($objectId !== null) {
            return $context['object_id'] === (int)$objectId;
        }

        // Page type matches, no specific object required
        return true;
    }

    /**
     * Calculate priority for matched conditions
     * Higher priority = more specific match
     */
    private static function calculatePriority(array $conditions, array $context): int
    {
        $maxPriority = 0;

        foreach ($conditions as $condition) {
            if ($condition['condition_type'] !== 'include') {
                continue;
            }

            if (!self::conditionMatchesContext($condition, $context)) {
                continue;
            }

            $priority = JTB_Template_Conditions::getPriority($condition);

            if ($priority > $maxPriority) {
                $maxPriority = $priority;
            }
        }

        return $maxPriority;
    }

    /**
     * Get category ID by slug
     */
    private static function getCategoryIdBySlug(string $slug): ?int
    {
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ?");
            $stmt->execute([$slug]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? (int)$row['id'] : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get tag ID by slug
     */
    private static function getTagIdBySlug(string $slug): ?int
    {
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("SELECT id FROM tags WHERE slug = ?");
            $stmt->execute([$slug]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? (int)$row['id'] : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get author ID by slug/username
     */
    private static function getAuthorIdBySlug(string $slug): ?int
    {
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$slug]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? (int)$row['id'] : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get post data by ID
     */
    private static function getPostData(int $id): ?array
    {
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("SELECT id, title, slug, type FROM posts WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Clear cached data
     */
    public static function clearCache(): void
    {
        self::$cachedContext = null;
        self::$cachedTemplates = [];
    }

    /**
     * Check if any JTB template is active for current request
     */
    public static function hasActiveTemplates(): bool
    {
        return self::getHeader() !== null ||
               self::getFooter() !== null ||
               self::getBody() !== null;
    }

    /**
     * Get debug info about current matching
     */
    public static function getDebugInfo(): array
    {
        return [
            'context' => self::getRequestContext(),
            'templates' => self::getTemplates(),
            'has_header' => self::getHeader() !== null,
            'has_footer' => self::getFooter() !== null,
            'has_body' => self::getBody() !== null
        ];
    }
}
