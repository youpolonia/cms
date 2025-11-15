<?php

namespace Database;

use PDO;
use PDOStatement;

class TenantAwarePDOStatement extends PDOStatement {
    private string $tenantId;

    protected function __construct(string $tenantId) {
        $this->tenantId = $tenantId;
    }

    public function execute($params = null): bool {
        $query = $this->queryString;
        
        if ($this->needsTenantFilter($query)) {
            $this->queryString = $this->addTenantCondition($query);
        }

        return parent::execute($params);
    }

    private function needsTenantFilter(string $query): bool {
        $query = strtolower(trim($query));
        $tables = ['content', 'users', 'settings']; // Tenant-aware tables
        
        foreach ($tables as $table) {
            if (str_contains($query, $table) && 
                !str_contains($query, 'tenant_id') &&
                !str_contains($query, 'where tenant_id')) {
                return true;
            }
        }
        return false;
    }

    private function addTenantCondition(string $query): string {
        $query = trim($query);
        $wherePos = stripos($query, 'where');
        
        if ($wherePos === false) {
            return $query . " WHERE tenant_id = '{$this->tenantId}'";
        }
        
        return substr($query, 0, $wherePos + 5) . 
               " tenant_id = '{$this->tenantId}' AND " . 
               substr($query, $wherePos + 5);
    }
}
