<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../core/database.php';

header('Content-Type: application/json');

try {
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    $pdo = \core\Database::connection();

    switch ($requestMethod) {
        case 'GET':
            if (strpos($path, '/version-stats') !== false) {
                // Get version statistics
                $statsQuery = <<<SQL
                SELECT
                    COUNT(*) as total_versions,
                    SUM(is_autosave) as autosave_count,
                    COUNT(DISTINCT content_id) as content_count,
                    COUNT(DISTINCT author_id) as author_count,
                    MIN(created_at) as oldest_version,
                    MAX(created_at) as newest_version,
                    AVG(LENGTH(content_hash)) as avg_size,
                    MAX(LENGTH(content_hash)) as max_size
                FROM content_versions
                JOIN version_content ON content_versions.id = version_content.version_id
                SQL;
                
                $stats = $db->query($statsQuery)->fetch(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'stats' => $stats
                ]);
            } elseif (strpos($path, '/version-activity') !== false) {
                // Get version activity timeline
                $activityQuery = <<<SQL
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as version_count,
                    SUM(is_autosave) as autosave_count
                FROM content_versions
                GROUP BY DATE(created_at)
                ORDER BY date DESC
                LIMIT 30
                SQL;
                
                $activity = $db->query($activityQuery)->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'activity' => $activity
                ]);
            } elseif (strpos($path, '/top-content-versions') !== false) {
                // Get content with most versions
                $topContentQuery = <<<SQL
                SELECT 
                    c.id,
                    c.title,
                    COUNT(v.id) as version_count
                FROM contents c
                JOIN content_versions v ON c.id = v.content_id
                GROUP BY c.id, c.title
                ORDER BY version_count DESC
                LIMIT 10
                SQL;
                
                $topContent = $db->query($topContentQuery)->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'top_content' => $topContent
                ]);
            } elseif (strpos($path, '/user-activity') !== false) {
                // Get user version activity
                $userActivityQuery = <<<SQL
                SELECT
                    u.id,
                    u.username,
                    COUNT(v.id) as version_count,
                    SUM(v.is_autosave) as autosave_count
                FROM users u
                JOIN content_versions v ON u.id = v.author_id
                GROUP BY u.id, u.username
                ORDER BY version_count DESC
                LIMIT 10
                SQL;
                
                $userActivity = $db->query($userActivityQuery)->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'user_activity' => $userActivity
                ]);
            } else {
                throw new RuntimeException('Endpoint not found');
            }
            break;
            
        default:
            throw new RuntimeException('Method not allowed');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
