<?php
// endpoints/migration_0003.php

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
// Include the migration file
require_once __DIR__ . '/../database/migrations/Migration_0003_CompleteTenantScope.php';
require_once __DIR__ . '/../includes/databaseconnection.php';

// Log function to append messages to memory-bank/progress.md
function logProgress($message) {
    file_put_contents(__DIR__ . '/../memory-bank/progress.md', $message . PHP_EOL, FILE_APPEND);
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    logProgress('Attempted to access migration_0003.php with non-POST method: ' . $_SERVER['REQUEST_METHOD']);
    exit;
}

$pdo = DatabaseConnection::getPDO();

if (isset($_POST['action']) && $_POST['action'] === 'migrate') {
    try {
        Migration_0003_CompleteTenantScope::migrate($pdo);
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Migration executed successfully']);
        logProgress('Migration 0003 executed successfully');
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        logProgress('Migration 0003 failed: ' . $e->getMessage());
    }
} elseif (isset($_POST['action']) && $_POST['action'] === 'rollback') {
    try {
        Migration_0003_CompleteTenantScope::rollback($pdo);
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Rollback executed successfully']);
        logProgress('Rollback 0003 executed successfully');
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        logProgress('Rollback 0003 failed: ' . $e->getMessage());
    }
} elseif (isset($_POST['action']) && $_POST['action'] === 'test') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Invalid action specified']);
    logProgress('Invalid action specified in migration_0003.php: ' . (isset($_POST['action']) ? $_POST['action'] : 'none'));
}
