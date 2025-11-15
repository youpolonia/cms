<?php

namespace Includes\Database;

class TenantScope
{
    /**
     * Applies tenant scope to database queries
     * @param \PDOStatement|object $query The database query object
     * @param string $tableName The table name to scope
     */
    public function apply($query, string $tableName): void
    {
        if ($tenantId = TenantContext::getCurrentTenantId()) {
            if (method_exists($query, 'where')) {
                $query->where("$tableName.tenant_id", $tenantId);
            } else {
                // Fallback for raw PDO queries
                $query->bindValue(':tenant_id', $tenantId);
            }
        }
    }
}
