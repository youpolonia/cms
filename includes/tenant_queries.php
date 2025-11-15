<?php
/**
 * Tenant-Aware Query Utilities
 * 
 * Provides tenant-aware query functions that automatically filter by tenant_id
 */

/**
 * Get current tenant ID from session
 * 
 * @return string Current tenant ID or 'global' if not set
 */
function getCurrentTenantId(): string {
    return $_SESSION['tenant_id'] ?? 'global';
}

/**
 * Execute a tenant-aware SELECT query
 * 
 * @param string $table Table name
 * @param array $conditions Additional WHERE conditions
 * @param array $options Query options (limit, order, etc)
 * @return array Query results
 */
function tenantSelect(string $table, array $conditions = [], array $options = []): array {
    $tenantId = getCurrentTenantId();
    $conditions['tenant_id'] = $tenantId;
    
    $db = getDatabaseConnection($tenantId);
    $query = buildSelectQuery($table, $conditions, $options);
    
    $stmt = $db->prepare($query);
    $stmt->execute($conditions);
    
    return $stmt->fetchAll();
}

/**
 * Execute a tenant-aware INSERT query
 * 
 * @param string $table Table name
 * @param array $data Data to insert
 * @return bool Success status
 */
function tenantInsert(string $table, array $data): bool {
    $tenantId = getCurrentTenantId();
    $data['tenant_id'] = $tenantId;
    
    $db = getDatabaseConnection($tenantId);
    $query = buildInsertQuery($table, $data);
    
    $stmt = $db->prepare($query);
    return $stmt->execute($data);
}

/**
 * Execute a tenant-aware UPDATE query
 * 
 * @param string $table Table name
 * @param array $data Data to update
 * @param array $conditions WHERE conditions
 * @return int Number of affected rows
 */
function tenantUpdate(string $table, array $data, array $conditions = []): int {
    $tenantId = getCurrentTenantId();
    $conditions['tenant_id'] = $tenantId;
    
    $db = getDatabaseConnection($tenantId);
    $query = buildUpdateQuery($table, $data, $conditions);
    
    $stmt = $db->prepare($query);
    $stmt->execute(array_merge($data, $conditions));
    
    return $stmt->rowCount();
}

/**
 * Execute a tenant-aware DELETE query
 * 
 * @param string $table Table name
 * @param array $conditions WHERE conditions
 * @return int Number of affected rows
 */
function tenantDelete(string $table, array $conditions = []): int {
    $tenantId = getCurrentTenantId();
    $conditions['tenant_id'] = $tenantId;
    
    $db = getDatabaseConnection($tenantId);
    $query = buildDeleteQuery($table, $conditions);
    
    $stmt = $db->prepare($query);
    $stmt->execute($conditions);
    
    return $stmt->rowCount();
}
