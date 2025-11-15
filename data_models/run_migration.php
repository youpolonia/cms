<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}
require_once __DIR__.'/../core/database.php';
require_once __DIR__.'/../database/migrations/0001_tenant_isolation.php';

try {
    $db = \core\Database::connection();
    $migration = new Migration_0001_TenantIsolation();
    $migration->migrate($db);
    
    file_put_contents(__DIR__.'/../memory-bank/progress.md', 
        "## Tenant ID Migration Completed\n".
        "- Date: ".date('Y-m-d H:i:s')."\n".
        "- Status: Success\n".
        "- Tables affected: tenants, content, users\n",
        FILE_APPEND
    );
    
} catch (PDOException $e) {
    echo "Migration failed: ".$e->getMessage()."\n";
    file_put_contents(__DIR__.'/../memory-bank/progress.md', 
        "## Tenant ID Migration Failed\n".
        "- Date: ".date('Y-m-d H:i:s')."\n".
        "- Error: ".$e->getMessage()."\n",
        FILE_APPEND
    );
    
    error_log("Migration error: ".$e->getMessage());
    exit(1);
}
