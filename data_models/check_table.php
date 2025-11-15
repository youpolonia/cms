<?php
require_once dirname(__DIR__) . '/config.php';

try {
    $pdo = \core\Database::connection();
    
    // Check if system_settings table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'system_settings'");
    $tableExists = $stmt->fetch() !== false;
    
    if ($tableExists) {
        // Get table schema
        $stmt = $pdo->query("DESCRIBE system_settings");
        $schema = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode([
            'exists' => true,
            'schema' => $schema
        ], JSON_PRETTY_PRINT);
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'exists' => false
        ], JSON_PRETTY_PRINT);
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode([
        'error' => 'Database error'
    ], JSON_PRETTY_PRINT);
}
