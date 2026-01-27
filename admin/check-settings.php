<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/core/database.php';

$pdo = \core\Database::connection();
$result = $pdo->query('DESCRIBE settings');
echo "Settings table structure:\n";
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
