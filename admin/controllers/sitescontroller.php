<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

class SitesController {
    private $siteManager;

    public function __construct($siteManager) {
        $this->siteManager = $siteManager;
    }

    public function listSites() {
        $sites = $this->siteManager->getAllSites();
        // Render sites list view
        require_once __DIR__ . '/../views/sites/list.php';
    }

    public function editSite($siteId) {
        // Implementation for editing a site
    }

    public function saveSite($data) {
        // Implementation for saving site data
    }

    public function manageSharedContent($siteId) {
        $content = $this->siteManager->getSharedContent();
        // Render shared content view
        require_once __DIR__ . '/../views/sites/shared_content.php';
    }
}
