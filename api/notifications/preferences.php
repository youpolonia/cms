<?php
require_once __DIR__.'/../includes/security/authservicewrapper.php';
require_once __DIR__ . '/../includes/db.php';

AuthServiceWrapper::checkAuth();

header('Content-Type: application/json');

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return json_response(405, ['error' => 'Method not allowed']);
}

// Check permissions
if (!has_permission('notification_preferences')) {
    return json_response(403, ['error' => 'Permission denied']);
}

// Validate input
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    return json_response(400, ['error' => 'Invalid JSON input']);
}

if (empty($input['user_id']) || !is_numeric($input['user_id'])) {
    return json_response(400, ['error' => 'Invalid user ID']);
}

// Validate channel preferences
$allowedChannels = ['email', 'sms', 'push'];
foreach ($input['preferences'] as $channel => $enabled) {
    if (!in_array($channel, $allowedChannels)) {
        return json_response(400, ['error' => "Invalid channel: $channel"]);
    }
    if (!is_bool($enabled)) {
        return json_response(400, ['error' => "Invalid preference value for $channel"]);
    }
}

try {
    $db = db();
    
    // Begin transaction
    $db->beginTransaction();
    
    // Update preferences
    foreach ($input['preferences'] as $channel => $enabled) {
        $stmt = $db->prepare("
            INSERT INTO user_notification_preferences 
            (user_id, channel, enabled, updated_at)
            VALUES (:user_id, :channel, :enabled, NOW())
            ON DUPLICATE KEY UPDATE 
            enabled = :enabled, 
            updated_at = NOW()
        ");
        
        $stmt->execute([
            ':user_id' => $input['user_id'],
            ':channel' => $channel,
            ':enabled' => $enabled ? 1 : 0
        ]);
    }
    
    $db->commit();
    
    return json_response(200, ['success' => true]);
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Notification preferences error: " . $e->getMessage());
    return json_response(500, ['error' => 'Failed to update preferences']);
}

function json_response($code, $data) {
    http_response_code($code);
    return json_encode($data);
}
