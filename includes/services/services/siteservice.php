<?php

class SiteService {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function createSite($domain, $name, array $settings = []) {
        $stmt = $this->db->prepare(
            "INSERT INTO sites (domain, name, settings) VALUES (?, ?, ?)"
        );
        $settingsJson = json_encode($settings);
        $stmt->execute([$domain, $name, $settingsJson]);
        return $this->db->lastInsertId();
    }

    public function getSite($siteId) {
        $stmt = $this->db->prepare("SELECT * FROM sites WHERE id = ?");
        $stmt->execute([$siteId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateSite($siteId, array $data) {
        $updates = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, ['domain', 'name', 'settings'])) {
                $updates[] = "$field = ?";
                $params[] = $field === 'settings' ? json_encode($value) : $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $params[] = $siteId;
        $sql = "UPDATE sites SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function addUserToSite($siteId, $userId, $role) {
        $stmt = $this->db->prepare(
            "INSERT INTO site_users (site_id, user_id, role) VALUES (?, ?, ?)"
        );
        return $stmt->execute([$siteId, $userId, $role]);
    }

    public function getSiteContent($siteId) {
        $stmt = $this->db->prepare(
            "SELECT c.* FROM contents c
            JOIN site_content sc ON c.id = sc.content_id
            WHERE sc.site_id = ?"
        );
        $stmt->execute([$siteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
