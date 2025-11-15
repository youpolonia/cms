<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/database.php';

/**
 * Database Connection Utility
 *
 * Provides a standardized way to get database connections across the application
 * with multi-tenant support
 */

/**
 * Get a PDO database connection for a specific tenant
 *
 * @param string|null $tenantId Tenant identifier (null for system connection)
 * @return PDO Database connection
 */
if (!function_exists('getDatabaseConnection')) {
function getDatabaseConnection(?string $tenantId = null): PDO {
    return \core\Database::connection();
}
}
