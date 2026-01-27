<?php
require_once __DIR__ . '/../../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

function add_content_status_fields(PDO $pdo) {
    try {
        $pdo->beginTransaction();
        
        $sql = "ALTER TABLE contents 
                ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'draft',
                ADD COLUMN author_id INT DEFAULT NULL,
                ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        
        $pdo->exec($sql);
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Database error');
        return false;
    }
}

function drop_content_status_fields(PDO $pdo) {
    try {
        $pdo->beginTransaction();
        
        $sql = "ALTER TABLE contents
                DROP COLUMN status,
                DROP COLUMN author_id,
                DROP COLUMN created_at,
                DROP COLUMN updated_at";
        
        $pdo->exec($sql);
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Database error');
        return false;
    }
}
