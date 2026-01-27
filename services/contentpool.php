<?php
declare(strict_types=1);

namespace CMS\Services;

use CMS\Config\MultiSite;
use CMS\Includes\Database\DatabaseConnection;

class ContentPool {
    private DatabaseConnection $db;
    private MultiSite $multiSite;
    private array $config;

    public function __construct(
        DatabaseConnection $db,
        MultiSite $multiSite,
        array $config = []
    ) {
        $this->db = $db;
        $this->multiSite = $multiSite;
        $this->config = $config;
    }

    /**
     * Get shared content available to current site
     */
    public function getSharedContent(?int $limit = null): array {
        $currentSiteId = $this->multiSite->getCurrentSite()['id'] ?? 0;
        $sharedConfig = $this->multiSite->getSharedContentConfig();

        $query = "SELECT c.* FROM shared_content sc
                  JOIN content c ON sc.content_id = c.id
                  WHERE sc.site_id = ? AND c.status = 'published'
                  ORDER BY sc.shared_at DESC";

        if ($limit !== null) {
            $query .= " LIMIT " . (int)$limit;
        }

        return $this->db->fetchAll($query, [$currentSiteId]);
    }

    /**
     * Share content with other sites
     */
    public function shareContent(int $contentId, array $siteIds): bool {
        $currentSiteId = $this->multiSite->getCurrentSite()['id'] ?? 0;
        $sharedAt = date('Y-m-d H:i:s');

        foreach ($siteIds as $siteId) {
            // Skip sharing with self
            if ($siteId === $currentSiteId) {
                continue;
            }

            $this->db->insert('shared_content', [
                'content_id' => $contentId,
                'site_id' => $siteId,
                'shared_by' => $currentSiteId,
                'shared_at' => $sharedAt,
                'access_level' => 'read'
            ]);
        }

        return true;
    }

    /**
     * Sync content from source site
     */
    public function syncContentFromSource(int $sourceSiteId): int {
        $currentSiteId = $this->multiSite->getCurrentSite()['id'] ?? 0;
        $sharedConfig = $this->multiSite->getSharedContentConfig();
        $syncInterval = $sharedConfig['sync_interval'] ?? 3600;

        // Get content shared with current site that needs syncing
        $content = $this->db->fetchAll(
            "SELECT sc.content_id, sc.shared_at, sc.access_level 
             FROM shared_content sc
             JOIN content c ON sc.content_id = c.id
             WHERE sc.site_id = ? AND sc.shared_by = ?
               AND sc.shared_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
               AND c.status = 'published'",
            [$currentSiteId, $sourceSiteId, $syncInterval]
        );

        $count = 0;
        foreach ($content as $item) {
            // Implement actual content sync logic here
            $count++;
        }

        return $count;
    }

    /**
     * Get content sharing statistics
     */
    public function getSharingStats(): array {
        $currentSiteId = $this->multiSite->getCurrentSite()['id'] ?? 0;

        return [
            'shared_out' => $this->db->fetchOne(
                "SELECT COUNT(DISTINCT content_id) as count 
                 FROM shared_content WHERE shared_by = ?",
                [$currentSiteId]
            )['count'] ?? 0,
            'shared_in' => $this->db->fetchOne(
                "SELECT COUNT(DISTINCT content_id) as count 
                 FROM shared_content WHERE site_id = ? AND shared_by != ?",
                [$currentSiteId, $currentSiteId]
            )['count'] ?? 0
        ];
    }
}
