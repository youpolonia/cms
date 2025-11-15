<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

if ($argc < 2) {
    echo "Usage: php scripts/run_migrations.php <migration_file_path>\n";
    exit(1);
}

$migrationFile = $argv[1];

if (!file_exists($migrationFile)) {
    echo "Error: Migration file not found: " . $migrationFile . "\n";
    exit(1);
}

// Include database configuration
// legacy alt DB config removed; use \core\Database::connection()

// Create connection
$conn = \core\Database::connection();

echo "Connected successfully to database: " . DB_NAME . "\n";

// Read the SQL from the migration file
$sql = file_get_contents($migrationFile);

if ($sql === false) {
    echo "Error: Could not read migration file: " . $migrationFile . "\n";
    exit(1);
}

// Execute SQL
try {
    $conn->exec($sql);
    echo "Migration executed successfully: " . $migrationFile . "\n";
} catch (\PDOException $e) {
    echo "Error executing migration: " . $migrationFile . "\n";
    echo "SQL Error: " . $e->getMessage() . "\n";
}

echo "Connection closed.\n";
