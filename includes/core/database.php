<?php
require_once __DIR__ . '/../../core/database.php';
class Database {
    public static function connection(): \PDO { return \core\Database::connection(); }
    public static function getConnection(): \PDO { return \core\Database::connection(); }
}
