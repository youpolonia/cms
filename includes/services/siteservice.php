<?php

namespace Includes\Services;

use PDO;
use Includes\Database\TenantContext;

class SiteService {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function findByDomain(string $domain): ?array {
        $stmt = $this->db->prepare("SELECT * FROM sites WHERE domain = :domain");
        $stmt->execute(['domain' => $domain]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getCurrentSite(): ?array {
        $siteId = TenantContext::getCurrentTenantId();
        if (!$siteId) {
            return null;
        }
        return $this->getSiteById($siteId);
    }

    public function getSiteById(int $siteId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM sites WHERE id = :id");
        $stmt->execute(['id' => $siteId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function switchSite(int $siteId): bool {
        $site = $this->getSiteById($siteId);
        if ($site) {
            TenantContext::setCurrentTenantId($siteId);
            return true;
        }
        return false;
    }

    public function getSiteConfig(int $siteId): array {
        $stmt = $this->db->prepare("SELECT config FROM site_configs WHERE site_id = :site_id");
        $stmt->execute(['site_id' => $siteId]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        return $config ? json_decode($config['config'], true) : [];
    }

    public function createSite(string $domain, string $name, array $settings = []): int {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO sites (domain, name) VALUES (:domain, :name)");
            $stmt->execute(['domain' => $domain, 'name' => $name]);
            $siteId = $this->db->lastInsertId();

            $configStmt = $this->db->prepare(
                "INSERT INTO site_configs (site_id, config) VALUES (:site_id, :config)"
            );
            $configStmt->execute([
                'site_id' => $siteId,
                'config' => json_encode($settings)
            ]);

            $this->db->commit();
            return $siteId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateSite(int $siteId, array $data): bool {
        $updates = [];
        $params = ['id' => $siteId];

        if (isset($data['domain'])) {
            $updates[] = 'domain = :domain';
            $params['domain'] = $data['domain'];
        }
        if (isset($data['name'])) {
            $updates[] = 'name = :name';
            $params['name'] = $data['name'];
        }

        if (empty($updates)) {
            return false;
        }

        $query = "UPDATE sites SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    // New resource monitoring methods
    public function getDiskUsage(int $siteId): float {
        $stmt = $this->db->prepare("
            SELECT SUM(size) as total_size 
            FROM site_files 
            WHERE site_id = :site_id
        ");
        $stmt->execute(['site_id' => $siteId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_size'] ?? 0;
    }

    public function getDatabaseSize(int $siteId): float {
        $stmt = $this->db->prepare("
            SELECT SUM(data_length + index_length) as size 
            FROM information_schema.tables 
            WHERE table_schema = DATABASE()
            AND table_name LIKE 'site_{$siteId}_%'
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['size'] ?? 0;
    }

    public function getActiveConnections(int $siteId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM information_schema.processlist 
            WHERE db = DATABASE()
            AND user = :site_user
        ");
        $stmt->execute(['site_user' => "site_{$siteId}_user"]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
}
