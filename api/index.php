<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

header('Content-Type: application/json');

// Database config from config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cms');
define('DB_USER', 'cms_user');
define('DB_PASS', 'secure_password_123');

// Validate API token
function validateToken($token) {
    try {
        require_once __DIR__ . '/../core/database.php';
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("SELECT * FROM api_tokens WHERE token = ? AND active = 1");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

// Main response handler
function jsonResponse($status, $data = null, $message = '') {
    http_response_code($status);
    echo json_encode([
        'status' => $status,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

// Get token from query param or header
$token = $_GET['token'] ?? 
         (isset($_SERVER['HTTP_AUTHORIZATION']) ? 
          str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']) : null);

if (!$token || !validateToken($token)) {
    jsonResponse(401, null, 'Invalid or missing API token');
}

// Route actions
$action = $_GET['action'] ?? '';
switch ($action) {
    case 'list_entries':
        $type = $_GET['type'] ?? '';
        if (empty($type)) {
            jsonResponse(400, null, 'Content type parameter is required');
        }
        try {
            require_once __DIR__ . '/../core/database.php';
            $pdo = \core\Database::connection();
            $stmt = $pdo->prepare("SELECT id, title, created_at FROM content WHERE type = ?");
            $stmt->execute([$type]);
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            jsonResponse(200, $entries);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            jsonResponse(500, null, 'Database error');
        }
        break;

    case 'get_entry':
        $id = $_GET['id'] ?? 0;
        if (empty($id)) {
            jsonResponse(400, null, 'Entry ID parameter is required');
        }
        try {
            require_once __DIR__ . '/../core/database.php';
            $pdo = \core\Database::connection();
            $stmt = $pdo->prepare("SELECT * FROM content WHERE id = ?");
            $stmt->execute([$id]);
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$entry) {
                jsonResponse(404, null, 'Entry not found');
            }
            
            jsonResponse(200, $entry);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            jsonResponse(500, null, 'Database error');
        }
        break;

    case 'list_types':
        try {
            require_once __DIR__ . '/../core/database.php';
            $pdo = \core\Database::connection();
            $stmt = $pdo->query("SELECT DISTINCT type FROM content");
            $types = $stmt->fetchAll(PDO::FETCH_COLUMN);
            jsonResponse(200, $types);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            jsonResponse(500, null, 'Database error');
        }
        break;

    default:
        jsonResponse(404, null, 'Invalid action');
}
