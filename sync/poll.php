<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../core/database.php';

// Validate API token
function validateToken($token) {
    try {
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

// Get last sync timestamp
$lastSync = (int)($_GET['last_sync'] ?? 0);
if (!is_numeric($lastSync)) {
    jsonResponse(400, null, 'Invalid timestamp format');
}

// Simple cache control
$cacheTime = 3; // seconds
require_once __DIR__ . '/../core/tmp_sandbox.php';
$cacheFile = cms_tmp_path('sync_cache_' . md5($lastSync));
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    jsonResponse(200, [], 'No changes (cached)');
}

try {
    $pdo = \core\Database::connection();
    $stmt = $pdo->prepare("
        SELECT id, title, type, content, updated_at 
        FROM content 
        WHERE updated_at > FROM_UNIXTIME(?)
        ORDER BY updated_at DESC
        LIMIT 20
    ");
    $stmt->execute([$lastSync]);
    $changes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Update cache file
    file_put_contents($cacheFile, json_encode($changes));

    if (empty($changes)) {
        jsonResponse(200, [], 'No changes');
    }

    jsonResponse(200, [
        'changes' => $changes,
        'current_time' => time()
    ]);
} catch (PDOException $e) {
    jsonResponse(500, null, 'Database error: ' . $e->getMessage());
}
