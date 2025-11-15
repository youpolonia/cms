<?php
/**
 * Save Notification Preferences Handler
 */

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/input_validation.php';
require_once __DIR__ . '/../../core/csrf.php';

csrf_boot();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
}

header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$response = ['success' => false, 'message' => ''];

try {
    // Validate inputs
    $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
    $pushNotifications = isset($_POST['push_notifications']) ? 1 : 0;
    $smsNotifications = isset($_POST['sms_notifications']) ? 1 : 0;
    $digestFrequency = validate_input($_POST['digest_frequency'] ?? '', 'digest_frequency');

    // Check if preferences exist
    $stmt = $pdo->prepare("SELECT user_id FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$userId]);
    $exists = $stmt->fetch();

    if ($exists) {
        // Update existing preferences
        $stmt = $pdo->prepare("
            UPDATE user_preferences SET
                email_notifications = ?,
                push_notifications = ?,
                sms_notifications = ?,
                digest_frequency = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE user_id = ?
        ");
        $stmt->execute([
            $emailNotifications,
            $pushNotifications,
            $smsNotifications,
            $digestFrequency,
            $userId
        ]);
    } else {
        // Insert new preferences
        $stmt = $pdo->prepare("
            INSERT INTO user_preferences (
                user_id,
                email_notifications,
                push_notifications,
                sms_notifications,
                digest_frequency
            ) VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $emailNotifications,
            $pushNotifications,
            $smsNotifications,
            $digestFrequency
        ]);
    }

    $response['success'] = true;
    $response['message'] = 'Preferences saved successfully';
} catch (PDOException $e) {
    error_log("Error saving preferences: " . $e->getMessage());
    $response['message'] = 'Error saving preferences';
} catch (Exception $e) {
    error_log("Validation error: " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
