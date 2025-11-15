<?php

namespace Api\v1\Controllers;

use Includes\Services\SiteService;
use Includes\Database\TenantContext;

class SiteController {
    private $siteService;
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
        $this->siteService = new SiteService($db);
    }

    public function listSites() {
        try {
            $stmt = $this->db->query("SELECT * FROM sites");
            $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return json_response(200, $sites);
        } catch (Exception $e) {
            return json_response(500, ['error' => 'Failed to fetch sites']);
        }
    }

    public function createSite() {
        $data = get_json_input();
        
        if (empty($data['domain']) || empty($data['name'])) {
            return json_response(400, ['error' => 'Domain and name are required']);
        }

        try {
            $siteId = $this->siteService->createSite(
                $data['domain'],
                $data['name'],
                $data['settings'] ?? []
            );
            return json_response(201, ['id' => $siteId]);
        } catch (Exception $e) {
            return json_response(500, ['error' => 'Failed to create site']);
        }
    }

    public function switchSite($siteId) {
        try {
            $success = $this->siteService->switchSite($siteId);
            if ($success) {
                return json_response(200, ['success' => true]);
            }
            return json_response(404, ['error' => 'Site not found']);
        } catch (Exception $e) {
            return json_response(500, ['error' => 'Failed to switch site']);
        }
    }

    public function getCurrentSite() {
        try {
            $site = $this->siteService->getCurrentSite();
            if ($site) {
                return json_response(200, $site);
            }
            return json_response(404, ['error' => 'No active site']);
        } catch (Exception $e) {
            return json_response(500, ['error' => 'Failed to get current site']);
        }
    }

    public function shareContent($contentId, $targetSiteId) {
        try {
            if (!has_permission('content.share')) {
                return json_response(403, ['error' => 'Permission denied']);
            }

            $content = $this->getContentFromCurrentSite($contentId);
            if (!$content) {
                return json_response(404, ['error' => 'Content not found']);
            }

            $sharedId = $this->shareToSite($content, $targetSiteId);
            return json_response(200, ['shared_id' => $sharedId]);
        } catch (Exception $e) {
            return json_response(500, ['error' => 'Failed to share content']);
        }
    }

    public function getSiteConfig($siteId) {
        try {
            $config = $this->siteService->getSiteConfig($siteId);
            return json_response(200, $config);
        } catch (Exception $e) {
            return json_response(500, ['error' => 'Failed to get site config']);
        }
    }

    public function updateSite($siteId) {
        $data = get_json_input();
        
        try {
            $success = $this->siteService->updateSite($siteId, $data);
            if ($success) {
                return json_response(200, ['success' => true]);
            }
            return json_response(400, ['error' => 'No valid fields to update']);
        } catch (Exception $e) {
            return json_response(500, ['error' => 'Failed to update site']);
        }
    }

    public function getSiteContent($siteId) {
        try {
            $content = $this->siteService->getSiteContent($siteId);
            return json_response(200, $content);
        } catch (Exception $e) {
            return json_response(500, ['error' => 'Failed to fetch site content']);
        }
    }

    // Phase 7: Multi-site Management Methods
    public function listAllSites() {
        try {
            $stmt = $this->db->query("SELECT * FROM sites WHERE status = 'active'");
            $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return json_response(200, $sites);
        } catch (Exception $e) {
            return json_response(500, ['error' => 'Failed to fetch all sites']);
        }
    }

    public function getSiteUsers($siteId) {
        try {
            $stmt = $this->db->prepare(
                "SELECT u.* FROM users u 
                JOIN user_sites us ON u.id = us.user_id 
                WHERE us.site_id = :site_id"
            );
            $stmt->execute(['site_id' => $siteId]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return json_response(200, $users);
        } catch (Exception $e) {
            return json_response(500, ['error' => 'Failed to fetch site users']);
        }
    }

    public function addSiteUser($siteId, $userId, $role = 'editor') {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO user_sites (user_id, site_id, role) 
                VALUES (:user_id, :site_id, :role)
                ON DUPLICATE KEY UPDATE role = :role"
            );
            $stmt->execute([
                'user_id' => $userId,
                'site_id' => $siteId,
                'role' => $role
            ]);
            return json_response(200, ['success' => true]);
        } catch (Exception $e) {
            return json_response(500, ['error' => 'Failed to add user to site']);
        }
    }

    private function getContentFromCurrentSite($contentId) {
        $siteId = TenantContext::getCurrentTenantId();
        $stmt = $this->db->prepare(
            "SELECT * FROM content WHERE id = :id AND site_id = :site_id"
        );
        $stmt->execute(['id' => $contentId, 'site_id' => $siteId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function shareToSite($content, $targetSiteId) {
        unset($content['id']);
        $content['site_id'] = $targetSiteId;
        $content['shared_from'] = TenantContext::getCurrentTenantId();

        $columns = implode(', ', array_keys($content));
        $values = ':' . implode(', :', array_keys($content));

        $query = "INSERT INTO content ($columns) VALUES ($values)";
        $stmt = $this->db->prepare($query);
        $stmt->execute($content);

        return $this->db->lastInsertId();
    }
}
