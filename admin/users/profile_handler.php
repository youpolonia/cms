<?php
// Profile form handler - returns JSON responses for AJAX calls
// session boot (admin)
require_once __DIR__ . '/../../core/session_boot.php';
require_once __DIR__ . '/../core/csrf.php';
header('Content-Type: application/json');

// Basic RBAC check - will need to integrate with actual RBAC system
function checkProfilePermission($userId) {
    // TODO: Replace with actual RBAC check
    cms_session_start('admin');
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId;
}

// Validate and sanitize input
function validateProfileInput($data) {
    $errors = [];
    $cleanData = [];

    // Required fields
    if (empty($data['user_id'])) {
        $errors['user_id'] = 'User ID is required';
    } else {
        $cleanData['user_id'] = (int)$data['user_id'];
    }

    // Validate email if provided
    if (!empty($data['email'])) {
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } else {
            $cleanData['email'] = $email;
        }
    }

    // Sanitize other fields
    $allowedFields = ['first_name', 'last_name', 'bio', 'phone'];
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $cleanData[$field] = htmlspecialchars($data[$field], ENT_QUOTES, 'UTF-8');
        }
    }

    return [$cleanData, $errors];
}

// Main handler
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    csrf_validate_or_403();

    // Get input data
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    // Validate input
    list($cleanData, $errors) = validateProfileInput($input);
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    // Check permissions
    if (!checkProfilePermission($cleanData['user_id'])) {
        throw new Exception('Permission denied');
    }

    // TODO: Replace with actual DB connection
    require_once __DIR__ . '/../../core/database.php';
    $db = \core\Database::connection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare update statement
    $fields = [];
    $params = [':user_id' => $cleanData['user_id']];
    
    foreach ($cleanData as $key => $value) {
        if ($key !== 'user_id') {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }
    }

    if (!empty($fields)) {
        $stmt = $db->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = :user_id");
        $stmt->execute($params);
    }

    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully',
        'data' => $cleanData
    ]);

} catch (Exception $e) {
    // Error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
