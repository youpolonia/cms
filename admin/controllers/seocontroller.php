<?php
/**
 * SEO Controller
 * Handles admin SEO management actions
 */

class SeoController
{
    private \PDO $db;
    private $seoModel;
    private $seoService;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
        require_once __DIR__ . '/../../core/models/seomodel.php';
        require_once __DIR__ . '/../../core/services/seoservice.php';
        $this->seoModel = new SeoModel($db);
        $this->seoService = new SeoService($db);
    }

    /**
     * List all SEO metadata entries
     */
    public function index(): array
    {
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $entityType = isset($_GET['type']) ? trim($_GET['type']) : null;

        if ($entityType !== null && !in_array($entityType, ['page', 'article', 'category', 'custom'], true)) {
            $entityType = null;
        }

        $result = $this->seoModel->listMetadata($page, 20, $entityType);

        // Enrich with entity titles
        foreach ($result['items'] as &$item) {
            $item['entity_title'] = $this->getEntityTitle($item['entity_type'], (int) $item['entity_id']);
        }

        return $result;
    }

    /**
     * Get entity title for display
     */
    private function getEntityTitle(string $entityType, int $entityId): string
    {
        switch ($entityType) {
            case 'page':
                $stmt = $this->db->prepare("SELECT title FROM pages WHERE id = :id");
                break;
            case 'article':
                $stmt = $this->db->prepare("SELECT title FROM articles WHERE id = :id");
                break;
            case 'category':
                $stmt = $this->db->prepare("SELECT name as title FROM categories WHERE id = :id");
                break;
            default:
                return 'Unknown Entity #' . $entityId;
        }

        $stmt->execute([':id' => $entityId]);
        $result = $stmt->fetchColumn();
        return $result ?: 'Unknown #' . $entityId;
    }

    /**
     * Show edit form for SEO metadata
     */
    public function edit(string $entityType, int $entityId): array
    {
        $metadata = $this->seoModel->getMetadata($entityType, $entityId);

        return [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'entity_title' => $this->getEntityTitle($entityType, $entityId),
            'metadata' => $metadata ?: [],
            'defaults' => [
                'robots_index' => 'index',
                'robots_follow' => 'follow',
                'og_type' => 'website',
                'twitter_card' => 'summary_large_image'
            ]
        ];
    }

    /**
     * Save SEO metadata
     */
    public function save(string $entityType, int $entityId, array $data): array
    {
        $errors = $this->validate($data);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Clean data
        $cleanData = [
            'meta_title' => isset($data['meta_title']) ? trim($data['meta_title']) : null,
            'meta_description' => isset($data['meta_description']) ? trim($data['meta_description']) : null,
            'meta_keywords' => isset($data['meta_keywords']) ? trim($data['meta_keywords']) : null,
            'canonical_url' => isset($data['canonical_url']) ? trim($data['canonical_url']) : null,
            'robots_index' => in_array($data['robots_index'] ?? 'index', ['index', 'noindex'], true) ? $data['robots_index'] : 'index',
            'robots_follow' => in_array($data['robots_follow'] ?? 'follow', ['follow', 'nofollow'], true) ? $data['robots_follow'] : 'follow',
            'og_title' => isset($data['og_title']) ? trim($data['og_title']) : null,
            'og_description' => isset($data['og_description']) ? trim($data['og_description']) : null,
            'og_image' => isset($data['og_image']) ? trim($data['og_image']) : null,
            'og_type' => isset($data['og_type']) ? trim($data['og_type']) : 'website',
            'twitter_card' => in_array($data['twitter_card'] ?? 'summary_large_image', ['summary', 'summary_large_image', 'app', 'player'], true) ? $data['twitter_card'] : 'summary_large_image',
            'twitter_title' => isset($data['twitter_title']) ? trim($data['twitter_title']) : null,
            'twitter_description' => isset($data['twitter_description']) ? trim($data['twitter_description']) : null,
            'twitter_image' => isset($data['twitter_image']) ? trim($data['twitter_image']) : null,
            'focus_keyword' => isset($data['focus_keyword']) ? trim($data['focus_keyword']) : null,
            'schema_type' => isset($data['schema_type']) ? trim($data['schema_type']) : null,
        ];

        // Handle schema_data JSON
        if (isset($data['schema_data']) && !empty($data['schema_data'])) {
            $schemaData = json_decode($data['schema_data'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $cleanData['schema_data'] = $schemaData;
            }
        }

        $result = $this->seoModel->saveMetadata($entityType, $entityId, $cleanData);

        if ($result) {
            return ['success' => true, 'message' => 'SEO settings saved successfully'];
        }

        return ['success' => false, 'errors' => ['Failed to save SEO settings']];
    }

    /**
     * Validate SEO data
     */
    private function validate(array $data): array
    {
        $errors = [];

        // Validate meta title length
        if (!empty($data['meta_title']) && mb_strlen($data['meta_title']) > 255) {
            $errors[] = 'Meta title must be under 255 characters';
        }

        // Validate meta description length
        if (!empty($data['meta_description']) && mb_strlen($data['meta_description']) > 500) {
            $errors[] = 'Meta description should be under 500 characters';
        }

        // Validate canonical URL
        if (!empty($data['canonical_url'])) {
            if (strpos($data['canonical_url'], 'http') === 0 && !filter_var($data['canonical_url'], FILTER_VALIDATE_URL)) {
                $errors[] = 'Invalid canonical URL format';
            }
        }

        // Validate OG image URL
        if (!empty($data['og_image']) && !filter_var($data['og_image'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid Open Graph image URL';
        }

        // Validate Twitter image URL
        if (!empty($data['twitter_image']) && !filter_var($data['twitter_image'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid Twitter image URL';
        }

        // Validate schema_data JSON
        if (!empty($data['schema_data'])) {
            json_decode($data['schema_data']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = 'Schema data must be valid JSON';
            }
        }

        return $errors;
    }

    /**
     * Delete SEO metadata
     */
    public function delete(int $id): array
    {
        $metadata = $this->seoModel->getMetadataById($id);

        if (!$metadata) {
            return ['success' => false, 'errors' => ['SEO metadata not found']];
        }

        $result = $this->seoModel->deleteMetadata($id);

        if ($result) {
            return ['success' => true, 'message' => 'SEO metadata deleted'];
        }

        return ['success' => false, 'errors' => ['Failed to delete SEO metadata']];
    }

    /**
     * Analyze content SEO
     */
    public function analyze(string $entityType, int $entityId): array
    {
        // Get content based on entity type
        $content = '';
        $title = '';

        switch ($entityType) {
            case 'page':
                $stmt = $this->db->prepare("SELECT title, content FROM pages WHERE id = :id");
                $stmt->execute([':id' => $entityId]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row) {
                    $title = $row['title'];
                    $content = $row['content'];
                }
                break;

            case 'article':
                $stmt = $this->db->prepare("SELECT title, content FROM articles WHERE id = :id");
                $stmt->execute([':id' => $entityId]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row) {
                    $title = $row['title'];
                    $content = $row['content'];
                }
                break;
        }

        if (empty($content) && empty($title)) {
            return ['success' => false, 'errors' => ['Content not found']];
        }

        // Get focus keyword from metadata
        $metadata = $this->seoModel->getMetadata($entityType, $entityId);
        $focusKeyword = $metadata['focus_keyword'] ?? null;

        // Run analysis
        $analysis = $this->seoService->analyzeContent($content, $title, $focusKeyword);
        $readabilityScore = $this->seoService->calculateReadabilityScore($content);

        // Save scores to metadata
        $this->seoModel->saveMetadata($entityType, $entityId, [
            'seo_score' => $analysis['score'],
            'readability_score' => $readabilityScore,
            'last_analyzed_at' => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'seo_score' => $analysis['score'],
            'readability_score' => $readabilityScore,
            'analysis' => $analysis
        ];
    }

    /**
     * Get dashboard statistics
     */
    public function dashboard(): array
    {
        $stats = $this->seoModel->getDashboardStats();
        $lowScorePages = $this->seoModel->getLowScorePages(50, 10);
        $pagesWithoutSeo = $this->seoModel->getPagesWithoutSeo();

        // Enrich low score pages with titles
        foreach ($lowScorePages as &$page) {
            $page['entity_title'] = $this->getEntityTitle($page['entity_type'], (int) $page['entity_id']);
        }

        return [
            'stats' => $stats,
            'low_score_pages' => $lowScorePages,
            'pages_without_seo' => array_slice($pagesWithoutSeo, 0, 10)
        ];
    }

    // ========================================
    // Redirect Management
    // ========================================

    /**
     * List redirects
     */
    public function listRedirects(): array
    {
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $activeOnly = isset($_GET['active']) ? (bool) $_GET['active'] : null;

        return $this->seoModel->listRedirects($page, 20, $activeOnly);
    }

    /**
     * Get redirect for editing
     */
    public function getRedirect(int $id): ?array
    {
        return $this->seoModel->getRedirect($id);
    }

    /**
     * Save redirect
     */
    public function saveRedirect(array $data): array
    {
        $errors = [];

        // Validate source URL
        if (empty($data['source_url'])) {
            $errors[] = 'Source URL is required';
        }

        // Validate target URL
        if (empty($data['target_url'])) {
            $errors[] = 'Target URL is required';
        }

        // Validate redirect type
        $validTypes = [301, 302, 307, 308];
        $redirectType = (int) ($data['redirect_type'] ?? 301);
        if (!in_array($redirectType, $validTypes, true)) {
            $errors[] = 'Invalid redirect type';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $cleanData = [
            'source_url' => $this->seoService->sanitizeUrl($data['source_url']),
            'target_url' => $this->seoService->sanitizeUrl($data['target_url']),
            'redirect_type' => $redirectType,
            'is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            'notes' => isset($data['notes']) ? trim($data['notes']) : null,
            'created_by' => $_SESSION['admin_id'] ?? null
        ];

        if (!empty($data['id'])) {
            // Update existing
            $result = $this->seoModel->updateRedirect((int) $data['id'], $cleanData);
            $message = 'Redirect updated successfully';
        } else {
            // Create new
            $id = $this->seoModel->createRedirect($cleanData);
            $result = $id > 0;
            $message = 'Redirect created successfully';
        }

        if ($result) {
            return ['success' => true, 'message' => $message];
        }

        return ['success' => false, 'errors' => ['Failed to save redirect']];
    }

    /**
     * Delete redirect
     */
    public function deleteRedirect(int $id): array
    {
        $redirect = $this->seoModel->getRedirect($id);

        if (!$redirect) {
            return ['success' => false, 'errors' => ['Redirect not found']];
        }

        $result = $this->seoModel->deleteRedirect($id);

        if ($result) {
            return ['success' => true, 'message' => 'Redirect deleted'];
        }

        return ['success' => false, 'errors' => ['Failed to delete redirect']];
    }

    // ========================================
    // Keyword Management
    // ========================================

    /**
     * List keywords
     */
    public function listKeywords(): array
    {
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;

        return $this->seoModel->listKeywords($page, 20, $search);
    }

    /**
     * Save keyword
     */
    public function saveKeyword(array $data): array
    {
        if (empty($data['keyword'])) {
            return ['success' => false, 'errors' => ['Keyword is required']];
        }

        $cleanData = [
            'keyword' => trim($data['keyword']),
            'search_volume' => isset($data['search_volume']) ? (int) $data['search_volume'] : null,
            'difficulty' => isset($data['difficulty']) ? (int) $data['difficulty'] : null,
            'cpc' => isset($data['cpc']) ? (float) $data['cpc'] : null
        ];

        $id = $this->seoModel->saveKeyword($cleanData);

        if ($id > 0) {
            return ['success' => true, 'message' => 'Keyword saved', 'id' => $id];
        }

        return ['success' => false, 'errors' => ['Failed to save keyword']];
    }

    /**
     * Delete keyword
     */
    public function deleteKeyword(int $id): array
    {
        $result = $this->seoModel->deleteKeyword($id);

        if ($result) {
            return ['success' => true, 'message' => 'Keyword deleted'];
        }

        return ['success' => false, 'errors' => ['Failed to delete keyword']];
    }

    // ========================================
    // Sitemap & Robots
    // ========================================

    /**
     * Regenerate sitemap (cache bust)
     */
    public function regenerateSitemap(): array
    {
        $sitemap = $this->seoService->generateSitemap();

        // Optionally save to file for caching
        $sitemapPath = defined('CMS_ROOT') ? CMS_ROOT . '/public/sitemap.xml' : __DIR__ . '/../../public/sitemap.xml';

        $result = @file_put_contents($sitemapPath, $sitemap);

        if ($result !== false) {
            return ['success' => true, 'message' => 'Sitemap regenerated successfully', 'url_count' => substr_count($sitemap, '<url>')];
        }

        return ['success' => false, 'errors' => ['Failed to write sitemap file']];
    }

    /**
     * Get crawl statistics
     */
    public function getCrawlStats(): array
    {
        return $this->seoModel->getCrawlStats(30);
    }

    /**
     * Clean old crawl logs
     */
    public function cleanCrawlLogs(): array
    {
        $deleted = $this->seoModel->cleanOldCrawlLogs(30);

        return [
            'success' => true,
            'message' => "Cleaned {$deleted} old crawl log entries"
        ];
    }
}
