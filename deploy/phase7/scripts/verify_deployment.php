<?php
// Placeholder for database configuration
// legacy alt DB config removed 

class DeploymentVerifier {
    public static function checkDatabaseConnection() {
        try {
            $db = \core\Database::connection();
            return "✅ Database connection successful";
        } catch (PDOException $e) {
            return "❌ Database connection failed: " . $e->getMessage();
        }
    }

    public static function verifyMigrationCount($expected) {
        $migrationDir = __DIR__.'/../database/migrations/phase7/';
        if (!is_dir($migrationDir)) {
            return "❌ Migration directory not found: $migrationDir";
        }
        $migrationFiles = glob($migrationDir . '*.php');
        $count = count($migrationFiles);
        $status = ($count === $expected) ? "✅" : "❌";
        return "$status Found $count/$expected migration files";
    }

    public static function checkGDPRProcedure() {
        try {
            $db = \core\Database::connection();
        } catch (PDOException $e) {
            return "❌ Could not connect to DB for GDPR check: " . $e->getMessage();
        }

        $exists = false;
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'mysql') {
            $stmt = $db->query("SELECT COUNT(*) FROM information_schema.routines
                                  WHERE routine_schema = DATABASE()
                                  AND routine_type = 'PROCEDURE'
                                  AND routine_name = 'purge_orphaned_user_data'");
            if ($stmt) $exists = $stmt->fetchColumn() > 0;
        } elseif ($driver === 'sqlsrv') {
            $stmt = $db->query("SELECT COUNT(*) FROM sys.procedures WHERE name = 'purge_orphaned_user_data'");
            if ($stmt) $exists = $stmt->fetchColumn() > 0;
        }

        $status = $exists ? '✅' : '❌';
        return "$status GDPR procedure 'purge_orphaned_user_data' exists";
    }

    public static function checkContentLifecycleManager() {
        $filePath = __DIR__.'/../../services/ContentLifecycleManager.php';
        $exists = file_exists($filePath);
        $status = $exists ? '✅' : '❌';
        return "$status ContentLifecycleManager.php exists";
    }
}

echo "=== Phase 7 Deployment Verification ===\n";
echo DeploymentVerifier::checkDatabaseConnection() . "\n";
echo DeploymentVerifier::verifyMigrationCount(3) . "\n"; // Expecting 3 phase 7 migrations
echo DeploymentVerifier::checkGDPRProcedure() . "\n";
echo DeploymentVerifier::checkContentLifecycleManager() . "\n";

// Atomic deployment check (conceptual)
// In a real FTP deployment, this might involve checking a version file
// or symbolic link target.
$versionFile = __DIR__.'/../VERSION.md';
if (file_exists($versionFile)) {
    echo "✅ Atomic deployment marker (VERSION.md) found.\n";
} else {
    echo "❌ Atomic deployment marker (VERSION.md) not found.\n";
}

echo "=====================================\n";
