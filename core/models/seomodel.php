<?php
/**
 * SEO Model
 * Handles CRUD operations for SEO metadata, redirects, and keywords
 */

class SeoModel
{
    private \PDO $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    // ========================================
    // SEO Metadata Methods
    // ========================================

    /**
     * Get SEO metadata for an entity
     */
    public function getMetadata(string $entityType, int $entityId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM seo_metadata
            WHERE entity_type = :entity_type AND entity_id = :entity_id
        ");
        $stmt->execute([
            ':entity_type' => $entityType,
            ':entity_id' => $entityId
        ]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get SEO metadata by ID
     */
    public function getMetadataById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM seo_metadata WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Save or update SEO metadata
     */
    public function saveMetadata(string $entityType, int $entityId, array $data): bool
    {
        $existing = $this->getMetadata($entityType, $entityId);

        if ($existing) {
            return $this->updateMetadata($existing['id'], $data);
        }

        return $this->createMetadata($entityType, $entityId, $data);
    }

    /**
     * Create new SEO metadata
     */
    public function createMetadata(string $entityType, int $entityId, array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO seo_metadata (
                entity_type, entity_id, meta_title, meta_description, meta_keywords,
                canonical_url, robots_index, robots_follow, og_title, og_description,
                og_image, og_type, twitter_card, twitter_title, twitter_description,
                twitter_image, schema_type, schema_data, focus_keyword, seo_score,
                readability_score, last_analyzed_at
            ) VALUES (
                :entity_type, :entity_id, :meta_title, :meta_description, :meta_keywords,
                :canonical_url, :robots_index, :robots_follow, :og_title, :og_description,
                :og_image, :og_type, :twitter_card, :twitter_title, :twitter_description,
                :twitter_image, :schema_type, :schema_data, :focus_keyword, :seo_score,
                :readability_score, :last_analyzed_at
            )
        ");

        return $stmt->execute([
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':meta_title' => $data['meta_title'] ?? null,
            ':meta_description' => $data['meta_description'] ?? null,
            ':meta_keywords' => $data['meta_keywords'] ?? null,
            ':canonical_url' => $data['canonical_url'] ?? null,
            ':robots_index' => $data['robots_index'] ?? 'index',
            ':robots_follow' => $data['robots_follow'] ?? 'follow',
            ':og_title' => $data['og_title'] ?? null,
            ':og_description' => $data['og_description'] ?? null,
            ':og_image' => $data['og_image'] ?? null,
            ':og_type' => $data['og_type'] ?? 'website',
            ':twitter_card' => $data['twitter_card'] ?? 'summary_large_image',
            ':twitter_title' => $data['twitter_title'] ?? null,
            ':twitter_description' => $data['twitter_description'] ?? null,
            ':twitter_image' => $data['twitter_image'] ?? null,
            ':schema_type' => $data['schema_type'] ?? null,
            ':schema_data' => isset($data['schema_data']) ? json_encode($data['schema_data']) : null,
            ':focus_keyword' => $data['focus_keyword'] ?? null,
            ':seo_score' => $data['seo_score'] ?? null,
            ':readability_score' => $data['readability_score'] ?? null,
            ':last_analyzed_at' => $data['last_analyzed_at'] ?? null
        ]);
    }

    /**
     * Update existing SEO metadata
     */
    public function updateMetadata(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        $allowedFields = [
            'meta_title', 'meta_description', 'meta_keywords', 'canonical_url',
            'robots_index', 'robots_follow', 'og_title', 'og_description',
            'og_image', 'og_type', 'twitter_card', 'twitter_title',
            'twitter_description', 'twitter_image', 'schema_type', 'schema_data',
            'focus_keyword', 'seo_score', 'readability_score', 'last_analyzed_at'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $value = $data[$field];
                if ($field === 'schema_data' && is_array($value)) {
                    $value = json_encode($value);
                }
                $params[":{$field}"] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE seo_metadata SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete SEO metadata
     */
    public function deleteMetadata(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM seo_metadata WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Delete SEO metadata by entity
     */
    public function deleteMetadataByEntity(string $entityType, int $entityId): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM seo_metadata
            WHERE entity_type = :entity_type AND entity_id = :entity_id
        ");
        return $stmt->execute([
            ':entity_type' => $entityType,
            ':entity_id' => $entityId
        ]);
    }

    /**
     * List all SEO metadata with pagination
     */
    public function listMetadata(int $page = 1, int $perPage = 20, ?string $entityType = null): array
    {
        $offset = ($page - 1) * $perPage;
        $where = '';
        $params = [];

        if ($entityType !== null) {
            $where = 'WHERE entity_type = :entity_type';
            $params[':entity_type'] = $entityType;
        }

        // Get total count
        $countSql = "SELECT COUNT(*) FROM seo_metadata {$where}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Get items
        $sql = "SELECT * FROM seo_metadata {$where} ORDER BY updated_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage)
        ];
    }

    /**
     * Get pages with low SEO scores
     */
    public function getLowScorePages(int $threshold = 50, int $limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM seo_metadata
            WHERE seo_score IS NOT NULL AND seo_score < :threshold
            ORDER BY seo_score ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':threshold', $threshold, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get pages without SEO metadata
     */
    public function getPagesWithoutSeo(): array
    {
        $stmt = $this->db->query("
            SELECT p.id, p.slug, p.title
            FROM pages p
            LEFT JOIN seo_metadata s ON s.entity_type = 'page' AND s.entity_id = p.id
            WHERE s.id IS NULL
            ORDER BY p.title
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ========================================
    // Redirect Methods
    // ========================================

    /**
     * Get redirect by ID
     */
    public function getRedirect(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM seo_redirects WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find redirect by source URL
     */
    public function findRedirectBySource(string $sourceUrl): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM seo_redirects
            WHERE source_url = :source_url AND is_active = 1
        ");
        $stmt->execute([':source_url' => $sourceUrl]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Create redirect
     */
    public function createRedirect(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO seo_redirects (source_url, target_url, redirect_type, is_active, notes, created_by)
            VALUES (:source_url, :target_url, :redirect_type, :is_active, :notes, :created_by)
        ");

        $stmt->execute([
            ':source_url' => $data['source_url'],
            ':target_url' => $data['target_url'],
            ':redirect_type' => $data['redirect_type'] ?? 301,
            ':is_active' => $data['is_active'] ?? 1,
            ':notes' => $data['notes'] ?? null,
            ':created_by' => $data['created_by'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update redirect
     */
    public function updateRedirect(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        $allowedFields = ['source_url', 'target_url', 'redirect_type', 'is_active', 'notes'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE seo_redirects SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete redirect
     */
    public function deleteRedirect(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM seo_redirects WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * List all redirects with pagination
     */
    public function listRedirects(int $page = 1, int $perPage = 20, ?bool $activeOnly = null): array
    {
        $offset = ($page - 1) * $perPage;
        $where = '';
        $params = [];

        if ($activeOnly !== null) {
            $where = 'WHERE is_active = :is_active';
            $params[':is_active'] = $activeOnly ? 1 : 0;
        }

        // Get total count
        $countSql = "SELECT COUNT(*) FROM seo_redirects {$where}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Get items
        $sql = "SELECT * FROM seo_redirects {$where} ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage)
        ];
    }

    /**
     * Increment redirect hit count
     */
    public function incrementRedirectHit(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE seo_redirects
            SET hit_count = hit_count + 1, last_hit_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([':id' => $id]);
    }

    // ========================================
    // Keyword Methods
    // ========================================

    /**
     * Get keyword by ID
     */
    public function getKeyword(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM seo_keywords WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find keyword by term
     */
    public function findKeyword(string $keyword): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM seo_keywords WHERE keyword = :keyword");
        $stmt->execute([':keyword' => $keyword]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Save or update keyword
     */
    public function saveKeyword(array $data): int
    {
        $existing = $this->findKeyword($data['keyword']);

        if ($existing) {
            $this->updateKeyword($existing['id'], $data);
            return (int) $existing['id'];
        }

        $stmt = $this->db->prepare("
            INSERT INTO seo_keywords (keyword, search_volume, difficulty, cpc, trend_data, last_updated_at)
            VALUES (:keyword, :search_volume, :difficulty, :cpc, :trend_data, NOW())
        ");

        $stmt->execute([
            ':keyword' => $data['keyword'],
            ':search_volume' => $data['search_volume'] ?? null,
            ':difficulty' => $data['difficulty'] ?? null,
            ':cpc' => $data['cpc'] ?? null,
            ':trend_data' => isset($data['trend_data']) ? json_encode($data['trend_data']) : null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update keyword
     */
    public function updateKeyword(int $id, array $data): bool
    {
        $fields = ['last_updated_at = NOW()'];
        $params = [':id' => $id];

        $allowedFields = ['search_volume', 'difficulty', 'cpc', 'trend_data'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $value = $data[$field];
                if ($field === 'trend_data' && is_array($value)) {
                    $value = json_encode($value);
                }
                $params[":{$field}"] = $value;
            }
        }

        $sql = "UPDATE seo_keywords SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete keyword
     */
    public function deleteKeyword(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM seo_keywords WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * List keywords with pagination
     */
    public function listKeywords(int $page = 1, int $perPage = 20, ?string $search = null): array
    {
        $offset = ($page - 1) * $perPage;
        $where = '';
        $params = [];

        if ($search !== null && $search !== '') {
            $where = 'WHERE keyword LIKE :search';
            $params[':search'] = '%' . $search . '%';
        }

        // Get total count
        $countSql = "SELECT COUNT(*) FROM seo_keywords {$where}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Get items
        $sql = "SELECT * FROM seo_keywords {$where} ORDER BY search_volume DESC NULLS LAST, keyword ASC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(\PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage)
        ];
    }

    // ========================================
    // Crawl Log Methods
    // ========================================

    /**
     * Log a crawl event
     */
    public function logCrawl(string $url, ?int $statusCode, ?int $responseTimeMs, ?string $crawlerType): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO seo_crawl_log (url, status_code, response_time_ms, crawler_type)
            VALUES (:url, :status_code, :response_time_ms, :crawler_type)
        ");

        return $stmt->execute([
            ':url' => $url,
            ':status_code' => $statusCode,
            ':response_time_ms' => $responseTimeMs,
            ':crawler_type' => $crawlerType
        ]);
    }

    /**
     * Get recent crawl logs
     */
    public function getRecentCrawls(int $limit = 100): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM seo_crawl_log
            ORDER BY crawled_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get crawl statistics
     */
    public function getCrawlStats(int $days = 7): array
    {
        $stmt = $this->db->prepare("
            SELECT
                DATE(crawled_at) as date,
                COUNT(*) as total_crawls,
                SUM(CASE WHEN status_code BETWEEN 200 AND 299 THEN 1 ELSE 0 END) as success_count,
                SUM(CASE WHEN status_code BETWEEN 400 AND 499 THEN 1 ELSE 0 END) as client_error_count,
                SUM(CASE WHEN status_code BETWEEN 500 AND 599 THEN 1 ELSE 0 END) as server_error_count,
                AVG(response_time_ms) as avg_response_time
            FROM seo_crawl_log
            WHERE crawled_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
            GROUP BY DATE(crawled_at)
            ORDER BY date DESC
        ");
        $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Clean old crawl logs
     */
    public function cleanOldCrawlLogs(int $daysToKeep = 30): int
    {
        $stmt = $this->db->prepare("
            DELETE FROM seo_crawl_log
            WHERE crawled_at < DATE_SUB(NOW(), INTERVAL :days DAY)
        ");
        $stmt->execute([':days' => $daysToKeep]);
        return $stmt->rowCount();
    }

    // ========================================
    // Sitemap Helper Methods
    // ========================================

    /**
     * Get all indexable pages for sitemap
     */
    public function getIndexablePages(): array
    {
        $stmt = $this->db->query("
            SELECT
                p.id, p.slug, p.title, p.updated_at,
                s.robots_index, s.robots_follow,
                COALESCE(s.canonical_url, CONCAT('/', p.slug)) as url
            FROM pages p
            LEFT JOIN seo_metadata s ON s.entity_type = 'page' AND s.entity_id = p.id
            WHERE p.status = 'published'
            AND (s.robots_index IS NULL OR s.robots_index = 'index')
            ORDER BY p.updated_at DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all indexable articles for sitemap
     */
    public function getIndexableArticles(): array
    {
        $stmt = $this->db->query("
            SELECT
                a.id, a.slug, a.title, a.updated_at, a.meta_title, a.meta_description,
                s.robots_index, s.robots_follow,
                COALESCE(s.canonical_url, CONCAT('/article/', a.slug)) as url
            FROM articles a
            LEFT JOIN seo_metadata s ON s.entity_type = 'article' AND s.entity_id = a.id
            WHERE a.status = 'published'
            AND (s.robots_index IS NULL OR s.robots_index = 'index')
            ORDER BY a.updated_at DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ========================================
    // Statistics Methods
    // ========================================

    /**
     * Get SEO dashboard statistics
     */
    public function getDashboardStats(): array
    {
        $stats = [];

        // Total pages with SEO
        $stmt = $this->db->query("SELECT COUNT(*) FROM seo_metadata");
        $stats['total_with_seo'] = (int) $stmt->fetchColumn();

        // Average SEO score
        $stmt = $this->db->query("SELECT AVG(seo_score) FROM seo_metadata WHERE seo_score IS NOT NULL");
        $stats['avg_seo_score'] = round((float) $stmt->fetchColumn(), 1);

        // Pages needing attention (score < 50)
        $stmt = $this->db->query("SELECT COUNT(*) FROM seo_metadata WHERE seo_score IS NOT NULL AND seo_score < 50");
        $stats['needs_attention'] = (int) $stmt->fetchColumn();

        // Active redirects
        $stmt = $this->db->query("SELECT COUNT(*) FROM seo_redirects WHERE is_active = 1");
        $stats['active_redirects'] = (int) $stmt->fetchColumn();

        // Total redirect hits
        $stmt = $this->db->query("SELECT SUM(hit_count) FROM seo_redirects");
        $stats['total_redirect_hits'] = (int) $stmt->fetchColumn();

        // Tracked keywords
        $stmt = $this->db->query("SELECT COUNT(*) FROM seo_keywords");
        $stats['tracked_keywords'] = (int) $stmt->fetchColumn();

        // Crawl stats (last 24 hours)
        $stmt = $this->db->query("
            SELECT COUNT(*) as total,
                SUM(CASE WHEN status_code BETWEEN 200 AND 299 THEN 1 ELSE 0 END) as success
            FROM seo_crawl_log
            WHERE crawled_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $crawlData = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stats['crawls_24h'] = (int) $crawlData['total'];
        $stats['successful_crawls_24h'] = (int) $crawlData['success'];

        return $stats;
    }
}
