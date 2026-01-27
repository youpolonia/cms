<?php
/**
 * Test endpoint for worker_monitoring_errors table migration
 * 
 * @package CMS
 * @subpackage Database\Migrations\Phase4\Tests
 */

require_once __DIR__ . '/../../../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

defined('CMS_ROOT') or die('No direct script access allowed');
// session boot (admin)
require_once __DIR__ . '/../../../../core/session_boot.php';
require_once __DIR__ . '/../../../../core/csrf.php';
require_once __DIR__ . '/../../../../core/database.php';

// Security checks
cms_session_start('admin');

// 1. CSRF Protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        error_log('CSRF token validation failed');
        http_response_code(403);
        exit;
    }
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 2. IP Whitelisting
$allowedIps = ['127.0.0.1', '::1', '192.168.0.1']; // localhost and common development IPs
$clientIp = $_SERVER['REMOTE_ADDR'];

if (!in_array($clientIp, $allowedIps)) {
    error_log('Access denied for IP: ' . $clientIp);
    http_response_code(403);
    exit;
}

// 3. Input sanitization function
function sanitizeInput($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitizeInput($value);
        }
        return $input;
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Sanitize all inputs
$_GET = sanitizeInput($_GET);
$_POST = sanitizeInput($_POST);

// Initialize database connection
try {
    $db = \core\Database::connection();
} catch (Exception $e) {
    error_log('Database error');
    http_response_code(500);
    exit;
}

// Handle actions
$action = $_GET['action'] ?? 'view';
$message = '';
$errors = [];
$data = [];

try {
    switch ($action) {
        case 'view':
            // Get all records from worker_monitoring_errors table
            $pdo = $db;
            $stmt = $pdo->prepare("
                SELECT * FROM worker_monitoring_errors
                ORDER BY created_at DESC
                LIMIT 100
            ");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'add':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validate inputs
                $workerId = filter_input(INPUT_POST, 'worker_id', FILTER_VALIDATE_INT);
                $errorCode = $_POST['error_code'] ?? '';
                $logMessage = $_POST['log_message'] ?? '';
                
                if (!$workerId) {
                    $errors[] = 'Worker ID must be a valid integer';
                }
                if (empty($errorCode)) {
                    $errors[] = 'Error code is required';
                }
                if (empty($logMessage)) {
                    $errors[] = 'Log message is required';
                }
                
                if (empty($errors)) {
                    // Insert new record using prepared statement
                    $pdo = $db;
                    $stmt = $pdo->prepare("
                        INSERT INTO worker_monitoring_errors (worker_id, error_code, log_message)
                        VALUES (?, ?, ?)
                    ");
                    $result = $stmt->execute([$workerId, $errorCode, $logMessage]);
                    
                    if ($result) {
                        $message = 'Test error log added successfully';
                    } else {
                        $errors[] = 'Failed to add test error log';
                    }
                }
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                
                if (!$id) {
                    $errors[] = 'Invalid ID';
                } else {
                    // Delete record using prepared statement
                    $pdo = $db;
                    $stmt = $pdo->prepare("DELETE FROM worker_monitoring_errors WHERE id = ?");
                    $result = $stmt->execute([$id]);
                    
                    if ($result) {
                        $message = 'Error log deleted successfully';
                    } else {
                        $errors[] = 'Failed to delete error log';
                    }
                }
            }
            break;
            
        case 'truncate':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Truncate table for testing purposes
                $pdo = $db;
                $result = $pdo->exec("TRUNCATE TABLE worker_monitoring_errors");
                
                if ($result) {
                    $message = 'All test error logs cleared successfully';
                } else {
                    $errors[] = 'Failed to clear test error logs';
                }
            }
            break;
            
        default:
            $errors[] = 'Invalid action';
    }
    
    // Get all records for display after any action
    if ($action !== 'view') {
        $pdo = $db;
        $stmt = $pdo->prepare("
            SELECT * FROM worker_monitoring_errors
            ORDER BY created_at DESC
            LIMIT 100
        ");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log('Database error');
    http_response_code(500);
    exit;
}

// HTML output
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Monitoring Errors - Migration Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .message { padding: 10px; background-color: #d4edda; border: 1px solid #c3e6cb; margin-bottom: 20px; }
        .error { padding: 10px; background-color: #f8d7da; border: 1px solid #f5c6cb; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        form { margin-bottom: 20px; padding: 15px; background-color: #f8f9fa; border: 1px solid #ddd; }
        input[type="text"], input[type="number"], textarea { width: 100%; padding: 8px; margin-bottom: 10px; }
        button { padding: 8px 16px; background-color: #007bff; color: white; border: none; cursor: pointer; }
        button.delete { background-color: #dc3545; }
        button.clear { background-color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Worker Monitoring Errors - Migration Test</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <h2>Add Test Error Log</h2>
        <form method="post" action="?action=add">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div>
                <label for="worker_id">Worker ID:</label>
                <input type="number" id="worker_id" name="worker_id" required>
            </div>
            
            <div>
                <label for="error_code">Error Code:</label>
                <input type="text" id="error_code" name="error_code" required>
            </div>
            
            <div>
                <label for="log_message">Log Message:</label>
                <textarea id="log_message" name="log_message" rows="4" required></textarea>
            </div>
            
            <button type="submit">Add Test Error</button>
        </form>
        
        <h2>Error Logs</h2>
        <?php if (empty($data)): ?>
            <p>No error logs found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Worker ID</th>
                        <th>Error Code</th>
                        <th>Log Message</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['worker_id']; ?></td>
                            <td><?php echo $row['error_code']; ?></td>
                            <td><?php echo $row['log_message']; ?></td>
                            <td><?php echo $row['created_at']; ?></td>
                            <td>
                                <form method="post" action="?action=delete" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <form method="post" action="?action=truncate">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="submit" class="clear">Clear All Test Data</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
