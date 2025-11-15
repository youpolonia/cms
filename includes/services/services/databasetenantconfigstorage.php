<?php
namespace Includes\Services;

use Includes\Interfaces\TenantConfigStorage;
use PDO;

class DatabaseTenantConfigStorage implements TenantConfigStorage {
    private PDO $db;
    private string $tenantId;

    public function __construct(PDO $db, string $tenantId) {
        $this->db = $db;
        $this->tenantId = $tenantId;
    }

    public function get(string $key, $default = null) {
        $stmt = $this->db->prepare(
            "SELECT value FROM tenant_config 
             WHERE tenant_id = :tenant_id AND config_key = :key"
        );
        $stmt->execute([':tenant_id' => $this->tenantId, ':key' => $key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? json_decode($result['value'], true) : $default;
    }

    public function set(string $key, $value): void {
        $jsonValue = json_encode($value);
        $stmt = $this->db->prepare(
            "INSERT INTO tenant_config (tenant_id, config_key, value) 
             VALUES (:tenant_id, :key, :value)
             ON DUPLICATE KEY UPDATE value = :value"
        );
        $stmt->execute([
            ':tenant_id' => $this->tenantId,
            ':key' => $key,
            ':value' => $jsonValue
        ]);
    }

    public function has(string $key): bool {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM tenant_config 
             WHERE tenant_id = :tenant_id AND config_key = :key"
        );
        $stmt->execute([':tenant_id' => $this->tenantId, ':key' => $key]);
        return (bool)$stmt->fetch();
    }

    public function delete(string $key): void {
        $stmt = $this->db->prepare(
            "DELETE FROM tenant_config 
             WHERE tenant_id = :tenant_id AND config_key = :key"
        );
        $stmt->execute([':tenant_id' => $this->tenantId, ':key' => $key]);
    }

    public function all(): array {
        $stmt = $this->db->prepare(
            "SELECT config_key, value FROM tenant_config 
             WHERE tenant_id = :tenant_id"
        );
        $stmt->execute([':tenant_id' => $this->tenantId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $config = [];
        foreach ($results as $row) {
            $config[$row['config_key']] = json_decode($row['value'], true);
        }
        return $config;
    }
}
