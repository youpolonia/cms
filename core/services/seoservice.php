<?php
/**
 * SEO Service
 * Handles sitemap generation, robots.txt, SEO analysis, and meta tag rendering
 */

class SeoService
{
    private \PDO $db;
    private ?array $settings = null;
    private string $baseUrl;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
        $this->baseUrl = $this->detectBaseUrl();
    }

    /**
     * Detect base URL from settings or server
     */
    private function detectBaseUrl(): string
    {
        $settings = $this->getGlobalSettings();
        if (!empty($settings['canonical_base_url'])) {
            return rtrim($settings['canonical_base_url'], '/');
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }

    /**
     * Get global SEO settings from JSON file
     */
    public function getGlobalSettings(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        $path = defined('CMS_ROOT') ? CMS_ROOT . '/config/seo_settings.json' : __DIR__ . '/../../config/seo_settings.json';

        if (!file_exists($path)) {
            $this->settings = $this->getDefaultSettings();
            return $this->settings;
        }

        $json = @file_get_contents($path);
        if ($json === false) {
            $this->settings = $this->getDefaultSettings();
            return $this->settings;
        }

        $data = json_decode($json, true);
        $this->settings = is_array($data) ? array_merge($this->getDefaultSettings(), $data) : $this->getDefaultSettings();
        return $this->settings;
    }

    /**
     * Get default SEO settings
     */
    private function getDefaultSettings(): array
    {
        return [
            'site_name' => '',
            'meta_description' => '',
            'meta_keywords' => '',
            'robots_index' => 'index',
            'robots_follow' => 'follow',
            'canonical_base_url' => '',
            'og_image_url' => ''
        ];
    }

    // ========================================
    // Sitemap Generation
    // ========================================

    /**
     * Generate XML sitemap
     */
    public function generateSitemap(): string
    {
        $urls = [];

        // Add homepage
        $urls[] = [
            'loc' => $this->baseUrl . '/',
            'lastmod' => date('Y-m-d'),
            'changefreq' => 'daily',
            'priority' => '1.0'
        ];

        // Add pages
        $pages = $this->getIndexablePages();
        foreach ($pages as $page) {
            $urls[] = [
                'loc' => $this->baseUrl . '/' . ltrim($page['slug'], '/'),
                'lastmod' => $page['updated_at'] ? date('Y-m-d', strtotime($page['updated_at'])) : date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.8'
            ];
        }

        // Add articles
        $articles = $this->getIndexableArticles();
        foreach ($articles as $article) {
            $urls[] = [
                'loc' => $this->baseUrl . '/article/' . ltrim($article['slug'], '/'),
                'lastmod' => $article['updated_at'] ? date('Y-m-d', strtotime($article['updated_at'])) : date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.7'
            ];
        }

        return $this->buildSitemapXml($urls);
    }

    /**
     * Build XML from URL array
     */
    private function buildSitemapXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
            if (!empty($url['lastmod'])) {
                $xml .= "    <lastmod>" . htmlspecialchars($url['lastmod'], ENT_XML1, 'UTF-8') . "</lastmod>\n";
            }
            if (!empty($url['changefreq'])) {
                $xml .= "    <changefreq>" . htmlspecialchars($url['changefreq'], ENT_XML1, 'UTF-8') . "</changefreq>\n";
            }
            if (!empty($url['priority'])) {
                $xml .= "    <priority>" . htmlspecialchars($url['priority'], ENT_XML1, 'UTF-8') . "</priority>\n";
            }
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';
        return $xml;
    }

    /**
     * Get indexable pages
     */
    private function getIndexablePages(): array
    {
        $stmt = $this->db->query("
            SELECT p.id, p.slug, p.title, p.updated_at,
                   sm.robots_index
            FROM pages p
            LEFT JOIN seo_metadata sm ON sm.entity_type = 'page' AND sm.entity_id = p.id
            WHERE p.status = 'published'
            AND (sm.robots_index IS NULL OR sm.robots_index = 'index')
            ORDER BY p.updated_at DESC
        ");
        return $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
    }

    /**
     * Get indexable articles
     */
    private function getIndexableArticles(): array
    {
        $stmt = $this->db->query("
            SELECT a.id, a.slug, a.title, a.updated_at,
                   sm.robots_index
            FROM articles a
            LEFT JOIN seo_metadata sm ON sm.entity_type = 'article' AND sm.entity_id = a.id
            WHERE a.status = 'published'
            AND (sm.robots_index IS NULL OR sm.robots_index = 'index')
            ORDER BY a.updated_at DESC
        ");
        return $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
    }

    // ========================================
    // Robots.txt Generation
    // ========================================

    /**
     * Generate robots.txt content
     */
    public function generateRobotsTxt(): string
    {
        $settings = $this->getGlobalSettings();
        $lines = [];

        $lines[] = 'User-agent: *';

        // Check global index/noindex setting
        if (($settings['robots_index'] ?? 'index') === 'noindex') {
            $lines[] = 'Disallow: /';
        } else {
            $lines[] = 'Allow: /';

            // Standard disallows
            $lines[] = '';
            $lines[] = '# Admin and system directories';
            $lines[] = 'Disallow: /admin/';
            $lines[] = 'Disallow: /config/';
            $lines[] = 'Disallow: /core/';
            $lines[] = 'Disallow: /includes/';
            $lines[] = 'Disallow: /logs/';
            $lines[] = 'Disallow: /backups/';

            // Get noindex pages and add them
            $noindexUrls = $this->getNoIndexUrls();
            if (!empty($noindexUrls)) {
                $lines[] = '';
                $lines[] = '# Pages set to noindex';
                foreach ($noindexUrls as $url) {
                    $lines[] = 'Disallow: ' . $url;
                }
            }
        }

        // Add sitemap reference
        $lines[] = '';
        $lines[] = '# Sitemap';
        $lines[] = 'Sitemap: ' . $this->baseUrl . '/sitemap.php';

        // Add custom rules from settings if any
        $customRules = $this->getCustomRobotsTxtRules();
        if (!empty($customRules)) {
            $lines[] = '';
            $lines[] = '# Custom rules';
            $lines[] = $customRules;
        }

        return implode("\n", $lines);
    }

    /**
     * Get URLs marked as noindex
     */
    private function getNoIndexUrls(): array
    {
        $urls = [];

        $stmt = $this->db->query("
            SELECT entity_type, entity_id, canonical_url
            FROM seo_metadata
            WHERE robots_index = 'noindex'
        ");

        if (!$stmt) {
            return $urls;
        }

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($results as $row) {
            if (!empty($row['canonical_url'])) {
                $urls[] = parse_url($row['canonical_url'], PHP_URL_PATH) ?: $row['canonical_url'];
            } else {
                // Build URL based on entity type
                $path = $this->buildEntityPath($row['entity_type'], (int) $row['entity_id']);
                if ($path) {
                    $urls[] = $path;
                }
            }
        }

        return $urls;
    }

    /**
     * Build URL path for an entity
     */
    private function buildEntityPath(string $entityType, int $entityId): ?string
    {
        switch ($entityType) {
            case 'page':
                $stmt = $this->db->prepare("SELECT slug FROM pages WHERE id = :id");
                $stmt->execute([':id' => $entityId]);
                $slug = $stmt->fetchColumn();
                return $slug ? '/' . $slug : null;

            case 'article':
                $stmt = $this->db->prepare("SELECT slug FROM articles WHERE id = :id");
                $stmt->execute([':id' => $entityId]);
                $slug = $stmt->fetchColumn();
                return $slug ? '/article/' . $slug : null;

            default:
                return null;
        }
    }

    /**
     * Get custom robots.txt rules from database settings
     */
    private function getCustomRobotsTxtRules(): string
    {
        $stmt = $this->db->prepare("
            SELECT `value` FROM settings
            WHERE `key` = 'seo_robots_txt_custom'
        ");
        $stmt->execute();
        $value = $stmt->fetchColumn();
        return is_string($value) ? trim($value) : '';
    }

    // ========================================
    // Meta Tag Generation
    // ========================================

    /**
     * Get meta tags for a page
     */
    public function getMetaTags(string $entityType, int $entityId, array $pageData = []): array
    {
        require_once __DIR__ . '/../models/seomodel.php';
        $seoModel = new SeoModel($this->db);
        $metadata = $seoModel->getMetadata($entityType, $entityId);
        $globalSettings = $this->getGlobalSettings();

        $tags = [];

        // Title
        $title = $metadata['meta_title'] ?? $pageData['title'] ?? $globalSettings['site_name'] ?? '';
        if (!empty($globalSettings['site_name']) && strpos($title, $globalSettings['site_name']) === false) {
            $title .= ' | ' . $globalSettings['site_name'];
        }
        $tags['title'] = $title;

        // Description
        $description = $metadata['meta_description'] ?? $pageData['excerpt'] ?? $globalSettings['meta_description'] ?? '';
        if (!empty($description)) {
            $tags['meta_description'] = '<meta name="description" content="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '">';
        }

        // Keywords
        $keywords = $metadata['meta_keywords'] ?? $globalSettings['meta_keywords'] ?? '';
        if (!empty($keywords)) {
            $tags['meta_keywords'] = '<meta name="keywords" content="' . htmlspecialchars($keywords, ENT_QUOTES, 'UTF-8') . '">';
        }

        // Robots
        $robotsIndex = $metadata['robots_index'] ?? $globalSettings['robots_index'] ?? 'index';
        $robotsFollow = $metadata['robots_follow'] ?? $globalSettings['robots_follow'] ?? 'follow';
        $tags['robots'] = '<meta name="robots" content="' . htmlspecialchars($robotsIndex . ', ' . $robotsFollow, ENT_QUOTES, 'UTF-8') . '">';

        // Canonical
        $canonical = $metadata['canonical_url'] ?? null;
        if (empty($canonical) && !empty($pageData['slug'])) {
            $canonical = $this->baseUrl . '/' . ltrim($pageData['slug'], '/');
        }
        if (!empty($canonical)) {
            $tags['canonical'] = '<link rel="canonical" href="' . htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') . '">';
        }

        // Open Graph
        $tags['og_type'] = '<meta property="og:type" content="' . htmlspecialchars($metadata['og_type'] ?? 'website', ENT_QUOTES, 'UTF-8') . '">';
        $tags['og_title'] = '<meta property="og:title" content="' . htmlspecialchars($metadata['og_title'] ?? $title, ENT_QUOTES, 'UTF-8') . '">';

        $ogDescription = $metadata['og_description'] ?? $description;
        if (!empty($ogDescription)) {
            $tags['og_description'] = '<meta property="og:description" content="' . htmlspecialchars($ogDescription, ENT_QUOTES, 'UTF-8') . '">';
        }

        $ogImage = $metadata['og_image'] ?? $globalSettings['og_image_url'] ?? '';
        if (!empty($ogImage)) {
            $tags['og_image'] = '<meta property="og:image" content="' . htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') . '">';
        }

        if (!empty($canonical)) {
            $tags['og_url'] = '<meta property="og:url" content="' . htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') . '">';
        }

        // Twitter Card
        $twitterCard = $metadata['twitter_card'] ?? 'summary_large_image';
        $tags['twitter_card'] = '<meta name="twitter:card" content="' . htmlspecialchars($twitterCard, ENT_QUOTES, 'UTF-8') . '">';
        $tags['twitter_title'] = '<meta name="twitter:title" content="' . htmlspecialchars($metadata['twitter_title'] ?? $title, ENT_QUOTES, 'UTF-8') . '">';

        $twitterDescription = $metadata['twitter_description'] ?? $description;
        if (!empty($twitterDescription)) {
            $tags['twitter_description'] = '<meta name="twitter:description" content="' . htmlspecialchars($twitterDescription, ENT_QUOTES, 'UTF-8') . '">';
        }

        $twitterImage = $metadata['twitter_image'] ?? $ogImage;
        if (!empty($twitterImage)) {
            $tags['twitter_image'] = '<meta name="twitter:image" content="' . htmlspecialchars($twitterImage, ENT_QUOTES, 'UTF-8') . '">';
        }

        return $tags;
    }

    /**
     * Render meta tags as HTML string
     */
    public function renderMetaTags(string $entityType, int $entityId, array $pageData = []): string
    {
        $tags = $this->getMetaTags($entityType, $entityId, $pageData);
        $html = '';

        if (!empty($tags['title'])) {
            $html .= '<title>' . htmlspecialchars($tags['title'], ENT_QUOTES, 'UTF-8') . '</title>' . "\n";
        }

        unset($tags['title']);

        foreach ($tags as $tag) {
            $html .= $tag . "\n";
        }

        return $html;
    }

    // ========================================
    // SEO Analysis
    // ========================================

    /**
     * Analyze content for SEO
     */
    public function analyzeContent(string $content, string $title, ?string $focusKeyword = null): array
    {
        $analysis = [
            'score' => 0,
            'issues' => [],
            'suggestions' => [],
            'passed' => []
        ];

        $points = 0;
        $maxPoints = 0;

        // Title analysis
        $maxPoints += 15;
        $titleLength = mb_strlen($title);
        if ($titleLength === 0) {
            $analysis['issues'][] = 'Missing page title';
        } elseif ($titleLength < 30) {
            $analysis['issues'][] = 'Title is too short (under 30 characters)';
            $points += 5;
        } elseif ($titleLength > 60) {
            $analysis['suggestions'][] = 'Title is long (' . $titleLength . ' chars). Consider shortening to under 60 characters';
            $points += 10;
        } else {
            $analysis['passed'][] = 'Title length is optimal (' . $titleLength . ' characters)';
            $points += 15;
        }

        // Content length analysis
        $maxPoints += 20;
        $wordCount = str_word_count(strip_tags($content));
        if ($wordCount < 100) {
            $analysis['issues'][] = 'Content is very short (' . $wordCount . ' words). Aim for at least 300 words';
        } elseif ($wordCount < 300) {
            $analysis['suggestions'][] = 'Content is short (' . $wordCount . ' words). Consider expanding to 300+ words';
            $points += 10;
        } elseif ($wordCount > 2000) {
            $analysis['passed'][] = 'Excellent content length (' . $wordCount . ' words)';
            $points += 20;
        } else {
            $analysis['passed'][] = 'Good content length (' . $wordCount . ' words)';
            $points += 15;
        }

        // Focus keyword analysis
        if (!empty($focusKeyword)) {
            $maxPoints += 25;
            $keywordLower = mb_strtolower($focusKeyword);
            $titleLower = mb_strtolower($title);
            $contentLower = mb_strtolower(strip_tags($content));

            // Keyword in title
            if (strpos($titleLower, $keywordLower) !== false) {
                $analysis['passed'][] = 'Focus keyword found in title';
                $points += 10;
            } else {
                $analysis['issues'][] = 'Focus keyword not found in title';
            }

            // Keyword in content
            $keywordCount = substr_count($contentLower, $keywordLower);
            if ($keywordCount === 0) {
                $analysis['issues'][] = 'Focus keyword not found in content';
            } elseif ($keywordCount < 3) {
                $analysis['suggestions'][] = 'Focus keyword appears only ' . $keywordCount . ' time(s). Consider using it more';
                $points += 5;
            } else {
                // Calculate keyword density
                $density = ($keywordCount / $wordCount) * 100;
                if ($density > 3) {
                    $analysis['suggestions'][] = 'Keyword density is high (' . round($density, 1) . '%). Consider reducing';
                    $points += 10;
                } else {
                    $analysis['passed'][] = 'Good keyword density (' . round($density, 1) . '%)';
                    $points += 15;
                }
            }
        }

        // Heading analysis
        $maxPoints += 15;
        preg_match_all('/<h[1-6][^>]*>/i', $content, $headings);
        $headingCount = count($headings[0]);
        if ($headingCount === 0) {
            $analysis['suggestions'][] = 'No headings found. Add H2/H3 headings to structure your content';
        } elseif ($headingCount < 2) {
            $analysis['suggestions'][] = 'Only ' . $headingCount . ' heading found. Consider adding more subheadings';
            $points += 7;
        } else {
            $analysis['passed'][] = 'Good heading structure (' . $headingCount . ' headings)';
            $points += 15;
        }

        // Image analysis
        $maxPoints += 10;
        preg_match_all('/<img[^>]+>/i', $content, $images);
        $imageCount = count($images[0]);
        if ($imageCount === 0 && $wordCount > 300) {
            $analysis['suggestions'][] = 'No images found. Consider adding relevant images';
        } else {
            // Check for alt tags
            preg_match_all('/<img[^>]+alt=["\'][^"\']+["\'][^>]*>/i', $content, $imagesWithAlt);
            $imagesWithAltCount = count($imagesWithAlt[0]);
            if ($imageCount > 0 && $imagesWithAltCount < $imageCount) {
                $analysis['issues'][] = ($imageCount - $imagesWithAltCount) . ' image(s) missing alt text';
                $points += 5;
            } elseif ($imageCount > 0) {
                $analysis['passed'][] = 'All images have alt text';
                $points += 10;
            }
        }

        // Link analysis
        $maxPoints += 15;
        preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $content, $links);
        $linkCount = count($links[0]);
        $externalLinks = 0;
        $internalLinks = 0;

        foreach ($links[1] as $href) {
            if (strpos($href, 'http') === 0 && strpos($href, $this->baseUrl) === false) {
                $externalLinks++;
            } else {
                $internalLinks++;
            }
        }

        if ($internalLinks === 0 && $wordCount > 300) {
            $analysis['suggestions'][] = 'No internal links found. Consider linking to related content';
        } else {
            $points += 7;
            if ($internalLinks > 0) {
                $analysis['passed'][] = $internalLinks . ' internal link(s) found';
            }
        }

        if ($externalLinks > 0) {
            $analysis['passed'][] = $externalLinks . ' external link(s) found';
            $points += 8;
        }

        // Calculate final score
        $analysis['score'] = $maxPoints > 0 ? round(($points / $maxPoints) * 100) : 0;
        $analysis['word_count'] = $wordCount;
        $analysis['reading_time'] = ceil($wordCount / 200); // Average reading speed

        return $analysis;
    }

    /**
     * Calculate readability score (Flesch-Kincaid)
     */
    public function calculateReadabilityScore(string $content): int
    {
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);

        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $sentenceCount = count($sentences);

        if ($sentenceCount === 0) {
            return 0;
        }

        $words = str_word_count($text, 1);
        $wordCount = count($words);

        if ($wordCount === 0) {
            return 0;
        }

        // Count syllables (simplified)
        $syllableCount = 0;
        foreach ($words as $word) {
            $syllableCount += $this->countSyllables($word);
        }

        // Flesch Reading Ease formula
        $asl = $wordCount / $sentenceCount; // Average Sentence Length
        $asw = $syllableCount / $wordCount; // Average Syllables per Word

        $score = 206.835 - (1.015 * $asl) - (84.6 * $asw);

        // Clamp to 0-100
        return max(0, min(100, (int) round($score)));
    }

    /**
     * Count syllables in a word (simplified)
     */
    private function countSyllables(string $word): int
    {
        $word = strtolower($word);
        $word = preg_replace('/[^a-z]/', '', $word);

        if (strlen($word) <= 3) {
            return 1;
        }

        // Count vowel groups
        $count = preg_match_all('/[aeiouy]+/', $word, $matches);

        // Subtract silent e
        if (preg_match('/e$/', $word)) {
            $count--;
        }

        // Add back for -le endings
        if (preg_match('/le$/', $word) && strlen($word) > 2) {
            $count++;
        }

        return max(1, $count);
    }

    // ========================================
    // Schema.org Structured Data
    // ========================================

    /**
     * Generate Schema.org JSON-LD for a page
     */
    public function generateSchemaData(string $entityType, int $entityId, array $pageData = []): string
    {
        require_once __DIR__ . '/../models/seomodel.php';
        $seoModel = new SeoModel($this->db);
        $metadata = $seoModel->getMetadata($entityType, $entityId);
        $globalSettings = $this->getGlobalSettings();

        $schema = [];

        // Website schema (always include)
        $websiteSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $globalSettings['site_name'] ?? '',
            'url' => $this->baseUrl
        ];

        // Add search action if applicable
        $websiteSchema['potentialAction'] = [
            '@type' => 'SearchAction',
            'target' => $this->baseUrl . '/search?q={search_term_string}',
            'query-input' => 'required name=search_term_string'
        ];

        $schema[] = $websiteSchema;

        // Page-specific schema
        $schemaType = $metadata['schema_type'] ?? null;
        $customSchemaData = null;

        if (!empty($metadata['schema_data'])) {
            $customSchemaData = is_string($metadata['schema_data'])
                ? json_decode($metadata['schema_data'], true)
                : $metadata['schema_data'];
        }

        if ($customSchemaData) {
            $customSchemaData['@context'] = 'https://schema.org';
            $schema[] = $customSchemaData;
        } elseif ($entityType === 'article' || $schemaType === 'Article') {
            $articleSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $metadata['meta_title'] ?? $pageData['title'] ?? '',
                'description' => $metadata['meta_description'] ?? $pageData['excerpt'] ?? '',
                'url' => $metadata['canonical_url'] ?? ($this->baseUrl . '/article/' . ($pageData['slug'] ?? '')),
                'datePublished' => $pageData['published_at'] ?? $pageData['created_at'] ?? date('c'),
                'dateModified' => $pageData['updated_at'] ?? date('c')
            ];

            if (!empty($metadata['og_image'])) {
                $articleSchema['image'] = $metadata['og_image'];
            }

            if (!empty($globalSettings['site_name'])) {
                $articleSchema['publisher'] = [
                    '@type' => 'Organization',
                    'name' => $globalSettings['site_name']
                ];
            }

            $schema[] = $articleSchema;
        } elseif ($schemaType === 'Product') {
            // Product schema placeholder
            $productSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $metadata['meta_title'] ?? $pageData['title'] ?? '',
                'description' => $metadata['meta_description'] ?? ''
            ];
            $schema[] = $productSchema;
        }

        // Build JSON-LD script tags
        $output = '';
        foreach ($schema as $item) {
            $output .= '<script type="application/ld+json">' . "\n";
            $output .= json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $output .= "\n</script>\n";
        }

        return $output;
    }

    // ========================================
    // Redirect Handling
    // ========================================

    /**
     * Check and execute redirect if exists
     */
    public function checkRedirect(string $requestUri): ?array
    {
        require_once __DIR__ . '/../models/seomodel.php';
        $seoModel = new SeoModel($this->db);

        // Normalize URL
        $path = parse_url($requestUri, PHP_URL_PATH);
        $path = rtrim($path, '/');
        if (empty($path)) {
            $path = '/';
        }

        $redirect = $seoModel->findRedirectBySource($path);

        if ($redirect) {
            // Increment hit counter
            $seoModel->incrementRedirectHit((int) $redirect['id']);
            return $redirect;
        }

        // Also check with trailing slash
        if ($path !== '/') {
            $redirect = $seoModel->findRedirectBySource($path . '/');
            if ($redirect) {
                $seoModel->incrementRedirectHit((int) $redirect['id']);
                return $redirect;
            }
        }

        return null;
    }

    /**
     * Execute redirect
     */
    public function executeRedirect(array $redirect): void
    {
        $statusCode = (int) ($redirect['redirect_type'] ?? 301);
        $targetUrl = $redirect['target_url'];

        // Make sure target URL is absolute
        if (strpos($targetUrl, 'http') !== 0) {
            $targetUrl = $this->baseUrl . '/' . ltrim($targetUrl, '/');
        }

        http_response_code($statusCode);
        header('Location: ' . $targetUrl);
        exit;
    }

    // ========================================
    // Utility Methods
    // ========================================

    /**
     * Get base URL
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Sanitize URL for canonical/redirect use
     */
    public function sanitizeUrl(string $url): string
    {
        $url = trim($url);

        // If it's a relative URL, make it absolute
        if (strpos($url, 'http') !== 0 && strpos($url, '//') !== 0) {
            $url = '/' . ltrim($url, '/');
        }

        return $url;
    }

    /**
     * Generate URL-friendly slug
     */
    public function generateSlug(string $text): string
    {
        $slug = mb_strtolower($text);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
}
