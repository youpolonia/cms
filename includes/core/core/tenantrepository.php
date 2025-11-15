<?php

namespace CMS\Core;

class TenantRepository
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function create(Tenant $tenant)
    {
        // Implement create tenant in database
        try {
            $stmt = $this->db->prepare("INSERT INTO tenants (name, domain) VALUES (:name, :domain)");
            $stmt->bindParam(':name', $tenant->getName());
            $stmt->bindParam(':domain', $tenant->getDomain());
            $stmt->execute();
        } catch (\PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
        return true;
    }

    public function update(Tenant $tenant)
    {
        // Implement update tenant in database
        try {
            $stmt = $this->db->prepare("UPDATE tenants SET name = :name, domain = :domain WHERE id = :id");
            $stmt->bindParam(':name', $tenant->getName());
            $stmt->bindParam(':domain', $tenant->getDomain());
            $stmt->bindParam(':id', $tenant->getId());
            $stmt->execute();
        } catch (\PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
        return true;
    }

    public function delete(Tenant $tenant)
    {
        // Implement delete tenant from database
        try {
            $stmt = $this->db->prepare("DELETE FROM tenants WHERE id = :id");
            $stmt->bindParam(':id', $tenant->getId());
            $stmt->execute();
        } catch (\PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
        return true;
    }

    public function find($id)
    {
        // Implement find tenant by id
        try {
            $stmt = $this->db->prepare("SELECT * FROM tenants WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                return new Tenant($result['id'], $result['name'], $result['domain']);
            }
        } catch (\PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
        }
        return null;
    }

    public function findAll()
    {
        // Implement find all tenants
        $tenants = [];
        try {
            $stmt = $this->db->prepare("SELECT * FROM tenants");
            $stmt->execute();
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tenants[] = new Tenant($result['id'], $result['name'], $result['domain']);
            }
        } catch (\PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
        }
        return $tenants;
    }

    public static function generateChecksum(string $tenantId, array $payload): string
    {
        $data = json_encode(['tenant' => $tenantId, 'payload' => $payload]);
        return hash_hmac('sha256', $data, $_ENV['TENANT_SECRET']);
    }

    public static function validateChecksum(string $tenantId, array $payload, string $checksum): bool
    {
        $expected = self::generateChecksum($tenantId, $payload);
        return hash_equals($expected, $checksum);
    }

    public function isValidTenant(string $tenantId): bool
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $tenantId)) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM tenants WHERE id = :id");
            $stmt->bindParam(':id', $tenantId);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log('Validation error: ' . $e->getMessage());
            return false;
        }
    }
}
