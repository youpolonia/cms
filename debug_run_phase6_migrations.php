<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define CMS_ROOT if not already defined
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', __DIR__);
}

// Include bootstrap.php to set up the environment
require_once CMS_ROOT . '/includes/bootstrap.php';
require_once CMS_ROOT . '/includes/database/migrationrunner.php';

try {
    // Connect to the database (centralized)
    require_once __DIR__ . '/core/database.php';
    $pdo = \core\Database::connection();
    
    echo "
<h1>Running Phase 6 Migrations</h1>";
    
    // Define the path to phase6 migrations
    $phase6MigrationsPath = CMS_ROOT . '/database/migrations/phase6';
    
    // Create a MigrationRunner instance for phase6
    $runner = new MigrationRunner($pdo, $phase6MigrationsPath);
    
    // Run the migrations
    $result = $runner->migrate();
    
    echo "
<h2>Migration Results:</h2>";
    echo "
<p>Batch: " . htmlspecialchars($result['batch'] ?? 'N/A') . "</p>";
    echo "
<p>Processed: " . htmlspecialchars(
$result['processed'] ?? 0) . "</p>";
    
    if (!empty($result['results'])) {
        echo "
<h3>Details:</h3>";
        echo "
<ul>";
        foreach (
$result['results'] as $res) {
            $statusClass = htmlspecialchars($res['status']);
            echo "
<li class='" .
 $statusClass . "'>";
            echo "<strong>Migration:</strong> " . htmlspecialchars($res['migration']) . "<br>";
            echo "<strong>Status:</strong> " . htmlspecialchars(ucfirst($res['status'])) . "
<br>";
            echo "<strong>Message:</strong> " . nl2br(htmlspecialchars($res['message']));
            echo "
</li>";
        }
        echo "</ul>";
    }
    
    // Check if foreign key exists after migration
    $stmt = $pdo->query("
        SELECT CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'content_schedules'
          AND COLUMN_NAME = 'version_id'
          AND REFERENCED_TABLE_NAME = 'versions'
          AND CONSTRAINT_NAME = 'fk_cs_version_id';
    ");
    $fkExists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "
<h2>Verification:</h2>";
    echo "
<p>Foreign key fk_cs_version_id exists: " . ($fkExists ? "Yes" : "No") . "</p>";
    
} catch (PDOException
 $e) {
    echo "
<h1>Database Connection Error</h1>";
    echo "
<p>Error: " . $e->getMessage() . "</p>";
} catch (Exception
 $e) {
    echo "
<h1>Migration Error</h1>";
    echo "
<p>Error: " . $e->getMessage() . "</p>";
    echo "
<pre>" .
 $e->getTraceAsString() . "</pre>";
}
