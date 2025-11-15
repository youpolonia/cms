<?php
/**
 * Content Lifecycle Scheduler Endpoint
 * Handles automated publishing, archiving and expiration of content
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__.'/../core/database.php';

header('Content-Type: application/json');

try {
    // Get database instance
    $db = \core\Database::connection();
    
    // Process due items in batches of 50
    $batchSize = 50;
    $processed = 0;
    
    // Get current timestamp
    $now = date('Y-m-d H:i:s');
    
    // Process scheduled items ready for publishing
    $publishResults = $db->query(
        "UPDATE content_schedules
        SET status = 'published', published_at = ?
        WHERE status = 'scheduled' AND publish_at <= ?
        LIMIT ?",
        [$now, $now, $batchSize]
    );
    $processed += $publishResults->affectedRows();
    
    // Process published items ready for archiving
    $archiveResults = $db->query(
        "UPDATE content_schedules
        SET status = 'archived', archived_at = ?
        WHERE status = 'published' AND archive_at <= ?
        LIMIT ?",
        [$now, $now, $batchSize]
    );
    $processed += $archiveResults->affectedRows();
    
    // Process archived items ready for expiration
    $expireResults = $db->query(
        "UPDATE content_schedules
        SET status = 'expired', expired_at = ?
        WHERE status = 'archived' AND expire_at <= ?
        LIMIT ?",
        [$now, $now, $batchSize]
    );
    $processed += $expireResults->affectedRows();
    
    // Log execution
    $db->insert('scheduler_logs', [
        'executed_at' => $now,
        'items_processed' => $processed,
        'status' => 'completed'
    ]);
    
    echo json_encode([
        'success' => true,
        'processed' => $processed,
        'timestamp' => $now
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
