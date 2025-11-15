<?php
/**
 * Monthly Analytics Aggregation Script
 * 
 * Calculates monthly totals (views, likes, comments) from content_engagement_monthly
 * and stores results in analytics_monthly_summary table.
 * 
 * Can be called via HTTP endpoint or directly.
 */

// Load database configuration
require_once __DIR__ . '/../../../core/database.php';

try {
    // Create PDO connection
    $pdo = \core\Database::connection();
    
    // Set timezone for date operations
    $pdo->exec("SET time_zone = '+00:00'");
    
    // Calculate monthly aggregates
    $query = "
        INSERT INTO analytics_monthly_summary 
            (content_id, tenant_id, month, total_views, total_likes, total_comments, updated_at)
        SELECT 
            content_id,
            tenant_id,
            DATE_FORMAT(date, '%Y-%m') AS month,
            SUM(views) AS total_views,
            SUM(likes) AS total_likes,
            SUM(comments) AS total_comments,
            NOW() AS updated_at
        FROM content_engagement_monthly
        GROUP BY content_id, tenant_id, DATE_FORMAT(date, '%Y-%m')
        ON DUPLICATE KEY UPDATE
            total_views = VALUES(total_views),
            total_likes = VALUES(total_likes),
            total_comments = VALUES(total_comments),
            updated_at = VALUES(updated_at)
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $affectedRows = $stmt->rowCount();
    
    // Log success
    $logMessage = date('Y-m-d H:i:s') . " - Aggregated $affectedRows monthly records\n";
    file_put_contents(__DIR__ . '/../../../logs/analytics_aggregation.log', $logMessage, FILE_APPEND);
    
    // HTTP response if called via web
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => "Aggregated $affectedRows monthly records",
            'timestamp' => date('c')
        ]);
    }
    
} catch (PDOException $e) {
    // Log error
    $errorMessage = date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n";
    file_put_contents(__DIR__ . '/../../../logs/analytics_aggregation.log', $errorMessage, FILE_APPEND);
    
    // HTTP error response if called via web
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json', true, 500);
        error_log($e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => 'Aggregation failed',
            'timestamp' => date('c')
        ]);
    }
    
    exit(1);
}
