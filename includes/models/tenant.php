<?php

class Tenant
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findById($tenantId)
    {
        $stmt = $this->db->prepare(
            "SELECT id, name, is_active 
             FROM tenants 
             WHERE id = ?"
        );
        $stmt->execute([$tenantId]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function isActive($tenantId)
    {
        $tenant = $this->findById($tenantId);
        return $tenant && $tenant['is_active'];
    }
}
