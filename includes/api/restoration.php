<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../../includes/helpers.php';

header('Content-Type: application/json');

try {
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = explode('/', $path);
    $versionId = end($segments);
    
    if (!is_numeric($versionId)) {
        throw new InvalidArgumentException('Invalid version ID');
    }

    $pdo = \core\Database::connection();

    switch ($requestMethod) {
        case 'GET':
            if (strpos($path, '/diff') !== false) {
                // Get version diff
                $query = "SELECT diff_content FROM content_versions WHERE id = :id";
                $stmt = $pdo->prepare($query);
                $stmt->bindValue(':id', $versionId, PDO::PARAM_INT);
                $stmt->execute();
                
                $diff = $stmt->fetchColumn();
                
                echo json_encode([
                    'success' => true,
                    'diff' => $diff
                ]);
            } else {
                // Get version metadata
                $query = "SELECT 
                            v.id, 
                            v.created_at,
                            v.is_autosave,
                            v.content_id,
                            u.username as author_name
                          FROM content_versions v
                          LEFT JOIN users u ON v.author_id = u.id
                          WHERE v.id = :id";
                $stmt = $pdo->prepare($query);
                $stmt->bindValue(':id', $versionId, PDO::PARAM_INT);
                $stmt->execute();
                
                $version = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$version) {
                    throw new RuntimeException('Version not found');
                }
                
                $version['created_at'] = formatDate($version['created_at']);
                
                echo json_encode([
                    'success' => true,
                    'version' => $version
                ]);
            }
            break;
            
        case 'POST':
            require_once __DIR__ . '/../../core/csrf.php';
            csrf_validate_or_403();

            // Restore version
            $pdo->beginTransaction();
            
            try {
                // Get version content
                $query = "SELECT content_id, content FROM content_versions WHERE id = :id";
                $stmt = $pdo->prepare($query);
                $stmt->bindValue(':id', $versionId, PDO::PARAM_INT);
                $stmt->execute();
                
                $version = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$version) {
                    throw new RuntimeException('Version not found');
                }
                
                // Update main content
                $updateQuery = "UPDATE contents
                               SET content = :content,
                                   updated_at = NOW(),
                                   updated_by = :user_id
                               WHERE id = :content_id";
                $updateStmt = $pdo->prepare($updateQuery);
                $updateStmt->bindValue(':content', $version['content']);
                $updateStmt->bindValue(':content_id', $version['content_id'], PDO::PARAM_INT);
                $updateStmt->bindValue(':user_id', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
                $updateStmt->execute();

                // Update version metadata with restoration info
                $metaQuery = "UPDATE version_metadata
                             SET restored_by = :user_id,
                                 restored_at = NOW()
                             WHERE version_id = :version_id";
                $metaStmt = $pdo->prepare($metaQuery);
                $metaStmt->bindValue(':user_id', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
                $metaStmt->bindValue(':version_id', $versionId, PDO::PARAM_INT);
                $metaStmt->execute();
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Version restored successfully'
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
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
