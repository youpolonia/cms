<?php
declare(strict_types=1);

namespace CMS\Config;

use CMS\Includes\Database\DatabaseConnection;
use CMS\Includes\Routing\SiteDetectionMiddleware;

class MultiSite {
    private DatabaseConnection $db;
    private array $config;
    private ?array $currentSite = null;
    private array $siteCache = [];

    public function __construct(DatabaseConnection $db, array $config = []) {
        $this->db = $db;
        $this->config = $config;
    }

    /**
     * Get current site configuration
     */
    public function getCurrentSite(): array {
        if ($this->currentSite === null) {
            $this->detectCurrentSite();
        }
        return $this->currentSite ?? [];
    }

    /**
     * Detect current site based on request
     */
    public function detectCurrentSite(): void {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $path = $_SERVER['REQUEST_URI'] ?? '';

        // Check cache first
        $cacheKey = md5($host . $path);
        if (isset($this->siteCache[$cacheKey])) {
            $this->currentSite = $this->siteCache[$cacheKey];
            return;
        }

        // Try to match by domain first
        $site = $this->db->fetchOne(
            "SELECT * FROM sites 
             WHERE domain = ? AND status = 'active'",
            [$host]
        );

        // If no domain match, try path prefix
        if (!$site) {
            $pathParts = explode('/', trim($path, '/'));
            $pathPrefix = $pathParts[0] ?? '';

            $site = $this->db->fetchOne(
                "SELECT * FROM sites 
                 WHERE path_prefix = ? AND status = 'active'",
                [$pathPrefix]
            );
        }

        // Fallback to default site
        if (!$site) {
            $site = $this->db->fetchOne(
                "SELECT * FROM sites WHERE is_default = 1 AND status = 'active'"
            );
        }

        if ($site) {
            $site['config'] = json_decode($site['config'] ?? '{}', true);
            $this->currentSite = $site;
            $this->siteCache[$cacheKey] = $site;
        }
    }

    /**
     * Get all active sites
     */
    public function getAllSites(): array {
        $sites = $this->db->fetchAll(
            "SELECT * FROM sites WHERE status = 'active' ORDER BY is_default DESC"
        );

        return array_map(function($site) {
            $site['config'] = json_decode($site['config'] ?? '{}', true);
            return $site;
        }, $sites);
    }

    /**
     * Get site by ID
     */
    public function getSiteById(int $siteId): ?array {
        $site = $this->db->fetchOne(
            "SELECT * FROM sites WHERE id = ? AND status = 'active'",
            [$siteId]
        );

        if ($site) {
            $site['config'] = json_decode($site['config'] ?? '{}', true);
            return $site;
        }

        return null;
    }

    /**
     * Create a new site
     */
    public function createSite(array $siteData): int {
        $defaultConfig = [
            'theme' => 'default',
            'locale' => 'en_US',
            'timezone' => 'UTC'
        ];

        $siteData['config'] = json_encode($siteData['config'] ?? $defaultConfig);
        $siteData['created_at'] = date('Y-m-d H:i:s');
        $siteData['status'] = 'active';

        return $this->db->insert('sites', $siteData);
    }

    /**
     * Update a site
     */
    public function updateSite(int $siteId, array $siteData): bool {
        if (isset($siteData['config'])) {
            $siteData['config'] = json_encode($siteData['config']);
        }
        return $this->db->update('sites', $siteData, ['id' => $siteId]);
    }

    /**
     * Get site-specific configuration value
     */
    public function getConfigValue(string $key, $default = null) {
        $site = $this->getCurrentSite();
        return $site['config'][$key] ?? $default;
    }

    /**
     * Switch current site context
     */
    public function switchSiteContext(int $siteId): bool {
        $site = $this->getSiteById($siteId);
        if ($site) {
            $this->currentSite = $site;
            return true;
        }
        return false;
    }

    /**
     * Get shared content pool configuration
     */
    public function getSharedContentConfig(): array {
        return $this->config['shared_content'] ?? [
            'enabled' => true,
            'default_access' => 'read',
            'sync_interval' => 3600
        ];
    }
}

return [
    'enabled' => false, // Default multi-site status
    'default_site_id' => 1,
    'sites' => [], // Placeholder for site configurations
    'shared_content' => [
        'enabled' => true,
        'default_access' => 'read',
        'sync_interval' => 3600
    ]
];
