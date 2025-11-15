<?php
// legacy alt DB config removed; use \core\Database::connection()
$config = [];

try {
    $pdo = \core\Database::connection();

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database:\n";
    print_r($tables);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Error checking tables\n");
}
