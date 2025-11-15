<?php
/**
 * CMS Settings Import
 *
 * Imports system settings from a JSON file
 * PHP 8.1+ compatible, FTP-deployable
 */

// Admin session validation
require_once __DIR__ . '/includes/admin_auth.php';
if (!isAdminLoggedIn()) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Database connection
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../core/csrf.php';

// Initialize variables
$message = '';
$messageType = '';

// Process file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['settings_file'])) {
    csrf_validate_or_403();
    $file = $_FILES['settings_file'];
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = 'Upload failed: ' . getUploadErrorMessage($file['error']);
        $messageType = 'error';
    } elseif ($file['size'] > 1048576) { // 1MB max
        $message = 'File too large. Maximum size is 1MB.';
        $messageType = 'error';
    } elseif ($file['type'] !== 'application/json' && !preg_match('/\.json$/i', $file['name'])) {
        $message = 'Invalid file type. Only JSON files are allowed.';
        $messageType = 'error';
    } else {
        // Read and validate JSON content
        $jsonContent = file_get_contents($file['tmp_name']);
        $settings = json_decode($jsonContent, true);
        
        if ($settings === null && json_last_error() !== JSON_ERROR_NONE) {
            $message = 'Invalid JSON format: ' . json_last_error_msg();
            $messageType = 'error';
        } else {
            // Begin transaction
            $pdo->beginTransaction();
            
            try {
                // Prepare statement for updating or inserting settings
                $stmt = $pdo->prepare("
                    INSERT INTO system_settings (key, value, updated_at)
                    VALUES (:key, :value, NOW())
                    ON DUPLICATE KEY UPDATE
                    value = VALUES(value),
                    updated_at = NOW()
                ");
                
                // Process each setting
                $importCount = 0;
                
                // Handle different JSON structures
                if (isset($settings[0]) && is_array($settings[0])) {
                    // Array of objects format (from export)
                    foreach ($settings as $setting) {
                        if (!isset($setting['key']) || !isset($setting['value'])) {
                            continue;
                        }
                        
                        // Sanitize inputs
                        $key = filter_var($setting['key'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                        $value = $setting['value'];
                        
                        // Skip invalid entries
                        if (empty($key)) {
                            continue;
                        }
                        
                        // Bind parameters and execute
                        $stmt->bindParam(':key', $key, PDO::PARAM_STR);
                        $stmt->bindParam(':value', $value, PDO::PARAM_STR);
                        $stmt->execute();
                        
                        $importCount++;
                    }
                } else {
                    // Simple key-value object format
                    foreach ($settings as $key => $value) {
                        // Sanitize key
                        $key = filter_var($key, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                        
                        // Skip invalid entries
                        if (empty($key)) {
                            continue;
                        }
                        
                        // Bind parameters and execute
                        $stmt->bindParam(':key', $key, PDO::PARAM_STR);
                        $stmt->bindParam(':value', $value, PDO::PARAM_STR);
                        $stmt->execute();
                        
                        $importCount++;
                    }
                }
                
                // Commit transaction
                $pdo->commit();
                
                $message = "Successfully imported {$importCount} settings.";
                $messageType = 'success';
            } catch (PDOException $e) {
                // Rollback transaction on error
                $pdo->rollBack();
                $message = 'Database error: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

/**
 * Get human-readable upload error message
 */
function getUploadErrorMessage($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
            return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
        case UPLOAD_ERR_FORM_SIZE:
            return 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form';
        case UPLOAD_ERR_PARTIAL:
            return 'The uploaded file was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing a temporary folder';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'A PHP extension stopped the file upload';
        default:
            return 'Unknown upload error';
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import System Settings</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        h1 { color: #333; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        form { background-color: #f9f9f9; padding: 20px; margin-bottom: 20px; }
        button { background-color: #4CAF50; color: white; padding: 10px 15px;
                border: none; cursor: pointer; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Import System Settings</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); 
?>            </div>
        <?php endif; ?>
        <form action="" method="post" enctype="multipart/form-data">
            <?= csrf_field(); ?>
            <label for="settings_file">Select Settings JSON File:</label><br>
            <input type="file" name="settings_file" id="settings_file" accept=".json,application/json" required>
            <button type="submit">Import Settings</button>
        </form>
        
        <p><strong>Note:</strong> Upload a valid JSON file with settings (max 1MB).<br>
        The import will update existing settings and add new ones.</p>
        
        <p><a href="settings_export.php">Export Current Settings</a></p>
    </div>
</body>
</html>
