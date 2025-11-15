<?php
/**
 * ActivityTracker Fix Script
 * 
 * This script automatically fixes ActivityTracker-related code in migration files
 * by commenting out ActivityTracker imports and replacing ActivityTracker::logMigrationError
 * calls with error_log.
 * 
 * The ActivityTracker class is not available, causing "Class not found" errors.
 */

// Configuration
$migrationsDir = __DIR__ . '/database/migrations/phase6';
$backupDir = __DIR__ . '/database/migrations/phase6/backups';
$logFile = __DIR__ . '/activity_tracker_fix_log.txt';

// Create backup directory if it doesn't exist
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Initialize log file
file_put_contents($logFile, "ActivityTracker Fix Log - " . date('Y-m-d H:i:s') . "\n\n");

// Get all PHP files in the migrations directory
$files = glob($migrationsDir . '/*.php');

// Process each file
foreach ($files as $file) {
    $filename = basename($file);
    
    // Skip the backup directory
    if (strpos($file, '/backups/') !== false) {
        continue;
    }
    
    echo "Processing $filename...\n";
    logMessage("Processing $filename");
    
    // Read file content
    $content = file_get_contents($file);
    
    // Create backup if it doesn't exist
    if (!file_exists("$backupDir/$filename")) {
        file_put_contents("$backupDir/$filename", $content);
    }
    
    // Fix patterns
    
    // Pattern 1: Comment out ActivityTracker import
    $pattern1 = '/(use\s+Includes\\\\Core\\\\ActivityTracker;)/i';
    $replacement1 = '// ActivityTracker.php not found, commenting out
// $1';
    $content = preg_replace($pattern1, $replacement1, $content);
    
    // Pattern 2: Replace ActivityTracker::logMigrationError calls with error_log
    $pattern2 = '/ActivityTracker::logMigrationError\([\'"]([^\'"]+)[\'"],\s*([^,]+),\s*([^\)]+)\)/i';
    $replacement2 = '// ActivityTracker not available, using error_log instead
            error_log("Error in $1: " . $2 . " in " . $3)';
    $content = preg_replace($pattern2, $replacement2, $content);
    
    // Save modified content
    file_put_contents($file, $content);
    
    logMessage("Fixed $filename");
    echo "Fixed $filename\n";
}

echo "\nAll migration files processed. See $logFile for details.\n";

/**
 * Log a message to the log file
 */
function logMessage($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - $message\n", FILE_APPEND);
}
