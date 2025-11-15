<?php
declare(strict_types=1);

namespace CMS\Services;

use CMS\Config\MultiSite;
use CMS\Includes\Database\DatabaseConnection;

class CrossSiteAnalytics {
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
     * Track cross-site content views
     */
    public function trackContentView(int $contentId, int $viewerSiteId): bool {
        $sourceSiteId = $this->db->fetchOne(
            "SELECT shared_by FROM shared_content 
             WHERE content_id = ? AND site_id = ?",
            [$contentId, $viewerSiteId]
        )['shared_by'] ?? null;

        if ($sourceSiteId === null) {
            return false;
        }

        return $this->db->insert('cross_site_analytics', [
            'content_id' => $contentId,
            'source_site_id' => $sourceSiteId,
            'viewer_site_id' => $viewerSiteId,
            'viewed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get cross-site content performance metrics
     */
    public function getContentPerformance(int $contentId): array {
        $currentSiteId = $this->multiSite->getCurrentSite()['id'] ?? 0;

        return [
            'total_views' => $this->getTotalViews($contentId),
            'views_by_site' => $this->getViewsBySite($contentId),
            'conversion_rate' => $this->getConversionRate($contentId, $currentSiteId)
        ];
    }

    private function getTotalViews(int $contentId): int {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM cross_site_analytics
             WHERE content_id = ?",
            [$contentId]
        );
        return (int)($result['count'] ?? 0);
    }

    private function getViewsBySite(int $contentId): array {
        return $this->db->fetchAll(
            "SELECT viewer_site_id as site_id, COUNT(*) as views
             FROM cross_site_analytics
             WHERE content_id = ?
             GROUP BY viewer_site_id",
            [$contentId]
        );
    }

    private function getConversionRate(int $contentId, int $sourceSiteId): float {
        $result = $this->db->fetchOne(
            "SELECT 
                (SELECT COUNT(DISTINCT viewer_site_id) FROM cross_site_analytics 
                 WHERE content_id = ? AND source_site_id = ?) as unique_sites,
                (SELECT COUNT(*) FROM shared_content 
                 WHERE content_id = ? AND shared_by = ?) as total_shared_sites",
            [$contentId, $sourceSiteId, $contentId, $sourceSiteId]
        );

        if (empty($result['total_shared_sites'])) {
            return 0.0;
        }

        return round(($result['unique_sites'] / $result['total_shared_sites']) * 100, 2);
    }

    /**
     * Get network-wide analytics trends
     */
    public function getNetworkTrends(?string $timePeriod = '7d'): array {
        $interval = $this->parseTimePeriod($timePeriod);

        return $this->db->fetchAll(
            "SELECT 
                DATE(viewed_at) as date,
                COUNT(*) as views,
                COUNT(DISTINCT content_id) as unique_content,
                COUNT(DISTINCT viewer_site_id) as unique_sites
             FROM cross_site_analytics
             WHERE viewed_at > DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(viewed_at)
             ORDER BY date DESC",
            [$interval]
        );
    }

    private function parseTimePeriod(string $period): int {
        $unit = substr($period, -1);
        $value = (int)substr($period, 0, -1);

        return match($unit) {
            'd' => $value,
            'w' => $value * 7,
            'm' => $value * 30,
            'y' => $value * 365,
            default => 7
        };
    }
}
