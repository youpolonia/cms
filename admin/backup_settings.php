<?php
require_once __DIR__ . '/includes/security.php';
verifyAdminAccess();

try {
    // Create backups directory if it doesn't exist
    if (!file_exists(__DIR__ . '/../backups')) {
        if (!mkdir(__DIR__ . '/../backups', 0755, true)) {
            throw new Exception('Failed to create backups directory');
        }
    }

    // Check directory permissions
    if (!is_writable(__DIR__ . '/../backups')) {
        throw new Exception('Backups directory is not writable');
    }

    // Connect to database with error handling
    require_once __DIR__ . '/../core/database.php';
    $db = \core\Database::connection();

    // Fetch all settings with error handling
    $stmt = $db->query("SELECT * FROM system_settings");
    if ($stmt === false) {
        throw new Exception('Failed to fetch settings from database');
    }
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate filename with current date
    $filename = 'settings_backup_' . date('Ymd_His') . '.json';
    $filepath = __DIR__ . '/../backups/' . $filename;

    // Save to JSON file with file locking
    $result = file_put_contents($filepath, json_encode($settings, JSON_PRETTY_PRINT), LOCK_EX);
    if ($result === false) {
        throw new Exception('Failed to write backup file');
    }

    // Cleanup old backups (keep last 5)
    $backups = glob(__DIR__ . '/../backups/settings_backup_*.json');
    if (count($backups) > 5) {
        usort($backups, function($a, $b) {
            return filemtime($a) < filemtime($b);
        });
        foreach (array_slice($backups, 5) as $oldBackup) {
            unlink($oldBackup);
        }
    }

    // Redirect back with success message
    $_SESSION['backup_message'] = "Backup created successfully: $filename";
    header('Location: settings.php');
    exit;
} catch (Exception $e) {
    $_SESSION['backup_message'] = "Backup failed: " . $e->getMessage();
    header('Location: settings.php');
    exit;
}
