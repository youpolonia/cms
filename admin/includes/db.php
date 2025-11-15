<?php
/**
 * Standardized database connection for admin pages
 * 
 * @throws PDOException On connection failure
 * @throws Exception On configuration issues
 * @return PDO Configured database connection
 */
function getDatabaseConnection(): PDO {
    try {
        return \core\Database::connection();
    } catch (PDOException $e) {
        http_response_code(500);
        error_log($e->getMessage());
        exit;
    }
}
