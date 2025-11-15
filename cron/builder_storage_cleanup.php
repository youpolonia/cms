<?php
/**
 * Weekly cleanup of builder storage files
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/storage/builderstorage.php';

try {
    $results = \CMS\Storage\BuilderStorage::runScheduledCleanup();
    
    $logMessage = sprintf(
        "[%s] Builder storage cleanup completed. Autosaves deleted: %d, Versions deleted: %d",
        date('Y-m-d H:i:s'),
        $results['autosaves_deleted'],
        $results['versions_deleted']
    );
    
    file_put_contents(
        __DIR__ . '/../logs/builder_storage_cleanup.log',
        $logMessage . PHP_EOL,
        FILE_APPEND
    );
    
    exit(0); // Success
} catch (\Exception $e) {
    $errorMessage = sprintf(
        "[%s] Builder storage cleanup failed: %s",
        date('Y-m-d H:i:s'),
        $e->getMessage()
    );
    
    file_put_contents(
        __DIR__ . '/../logs/builder_storage_cleanup.log',
        $errorMessage . PHP_EOL,
        FILE_APPEND
    );
    
    exit(1); // Error
}
