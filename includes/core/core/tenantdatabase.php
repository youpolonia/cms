<?php
/**
 * Tenant-aware database wrapper
 * Handles schema prefixing for multi-tenant isolation
 */
class TenantDatabase {
    private static $tenantPrefix = '';
    private static $sharedTables = ['users', 'system_settings'];

    /**
     * Set current tenant ID
     */
    public static function setTenant(string $tenantId): void {
        self::$tenantPrefix = 'tenant_' . $tenantId . '_';
    }

    /**
     * Get prefixed table name
     */
    public static function table(string $table): string {
        if (in_array($table, self::$sharedTables)) {
            return $table;
        }
        return self::$tenantPrefix . $table;
    }

    /**
     * Execute tenant-aware query
     */
    public static function query(string $sql, array $params = []): array {
        // Replace {table} placeholders with prefixed names
        $sql = preg_replace_callback('/\{(\w+)\}/', function($matches) {
            return self::table($matches[1]);
        }, $sql);

        // Execute query using existing DB connection
        return DB::execute($sql, $params);
    }
}
