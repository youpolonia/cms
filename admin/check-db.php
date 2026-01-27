<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/core/database.php';

$pdo = \core\Database::connection();

// Show all tables
echo "<h1>Database Tables</h1>";
$result = $pdo->query("SHOW TABLES");
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    echo $row[0] . "<br>";
}

echo "<hr><h2>Settings table content</h2>";
$result = $pdo->query("SELECT * FROM settings LIMIT 20");
echo "<table border='1'><tr>";
for ($i = 0; $i < $result->columnCount(); $i++) {
    $meta = $result->getColumnMeta($i);
    echo "<th>" . $meta['name'] . "</th>";
}
echo "</tr>";
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    foreach ($row as $val) {
        echo "<td>" . htmlspecialchars((string)$val) . "</td>";
    }
    echo "</tr>";
}
echo "</table>";

// Check for active_theme
echo "<hr><h2>Active Theme Setting</h2>";
$stmt = $pdo->query("SELECT * FROM settings WHERE `key` LIKE '%theme%'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . print_r($rows, true) . "</pre>";
