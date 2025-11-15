<?php
/**
 * SiteManager - Handles multi-site operations
 */
class SiteManager {
    private $db;
    private $currentSiteId;

    public function __construct($db) {
        $this->db = $db;
        $this->currentSiteId = $this->detectCurrentSite();
    }

    /**
     * Detect current site based on domain or request
     */
    private function detectCurrentSite() {
        // Default to primary site (ID 1)
        return 1;
    }

    /**
     * Get all available sites
     */
    public function getAllSites() {
        $query = "SELECT * FROM sites WHERE is_active = 1";
        return $this->db->query($query)->fetchAll();
    }

    /**
     * Get shared content available for current site
     */
    public function getSharedContent() {
        $query = "SELECT c.* FROM content c 
                 JOIN site_content sc ON c.id = sc.content_id
                 WHERE sc.site_id = ? AND c.is_shared = 1";
        return $this->db->query($query, [$this->currentSiteId])->fetchAll();
    }

    /**
     * Check if content is available for current site
     */
    public function isContentAvailable($contentId) {
        $query = "SELECT 1 FROM site_content 
                 WHERE content_id = ? AND site_id = ?";
        return (bool)$this->db->query($query, [$contentId, $this->currentSiteId])->fetch();
    }
}
