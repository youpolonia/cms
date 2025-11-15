<?php
require_once __DIR__ . '/../core/database.php';

class DatabaseConnection {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $this->connection = \core\Database::connection();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function prepare($statement) {
        return $this->connection->prepare($statement);
    }

    public function exec($statement) {
        return 0; /* exec disabled */
    }
}
