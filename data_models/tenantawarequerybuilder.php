<?php
/**
 * Tenant-Aware Query Builder
 * 
 * Automatically scopes queries to current tenant
 */

class TenantAwareQueryBuilder {
    protected $pdo;
    protected $table;
    protected $tenantId;
    
    public function __construct(PDO $pdo, string $table) {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->tenantId = $GLOBALS['current_tenant'] ?? null;
        
        if (!$this->tenantId) {
            throw new Exception('Tenant context not set');
        }
    }
    
    /**
     * Execute SELECT query with tenant filtering
     */
    public function select(array $columns = ['*'], array $conditions = []): array {
        $conditions['tenant_id'] = $this->tenantId;
        
        $columnsStr = implode(', ', $columns);
        $where = $this->buildWhereClause($conditions);
        
        $stmt = $this->pdo->prepare("
            SELECT {$columnsStr} FROM {$this->table} 
            WHERE {$where}
        ");
        $stmt->execute($conditions);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Execute INSERT query with tenant ID
     */
    public function insert(array $data): int {
        $data['tenant_id'] = $this->tenantId;
        
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table} ({$columns})
            VALUES ({$placeholders})
        ");
        $stmt->execute(array_values($data));
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Execute UPDATE query with tenant filtering
     */
    public function update(array $data, array $conditions): int {
        $conditions['tenant_id'] = $this->tenantId;
        
        $set = $this->buildSetClause($data);
        $where = $this->buildWhereClause($conditions);
        
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table} 
            SET {$set} 
            WHERE {$where}
        ");
        $stmt->execute(array_merge(array_values($data), array_values($conditions)));
        
        return $stmt->rowCount();
    }
    
    /**
     * Execute DELETE query with tenant filtering
     */
    public function delete(array $conditions): int {
        $conditions['tenant_id'] = $this->tenantId;
        
        $where = $this->buildWhereClause($conditions);
        
        $stmt = $this->pdo->prepare("
            DELETE FROM {$this->table} 
            WHERE {$where}
        ");
        $stmt->execute(array_values($conditions));
        
        return $stmt->rowCount();
    }
    
    protected function buildWhereClause(array $conditions): string {
        return implode(' AND ', array_map(
            fn($col) => "{$col} = ?",
            array_keys($conditions)
        ));
    }
    
    protected function buildSetClause(array $data): string {
        return implode(', ', array_map(
            fn($col) => "{$col} = ?",
            array_keys($data)
        ));
    }
}
