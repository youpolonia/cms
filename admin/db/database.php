<?php

require_once __DIR__ . '/../../core/database.php';

class Database {
    public function getConnection() {
        return \core\Database::connection();
    }
}
