<?php

require_once __DIR__ . '/../includes/database/migration.php';
require_once __DIR__ . '/../includes/database/migrationrunner.php';

// Get database connection
$db = \core\Database::connection();

$runner = new Database\Migrations\MigrationRunner($db);

if (!$runner->tableExists('roles')) {
    die("Roles table does not exist\n");
}

// Get table structure
$result = $db->query("DESCRIBE roles");
while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
    print_r($row);
}
