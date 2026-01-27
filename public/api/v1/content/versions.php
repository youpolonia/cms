<?php
/**
 * Content Versions API Endpoint
 * Version 1
 */
require_once __DIR__ . '/../../../../includes/bootstrap.php';
require_once __DIR__ . '/../../../../core/api/apihandler.php';
require_once __DIR__ . '/../../../../core/database.php';

// Get database connection
$pdo = \core\Database::connection();
$handler = new ApiHandler();

try {
    switch ($handler->getMethod()) {
        case 'GET':
            // Get content versions
            $contentId = $_GET['content_id'] ?? null;
            $tenantId = $_GET['tenant_id'] ?? null;
            if (!$contentId || !$tenantId) {
                $handler->error('Content ID and Tenant ID required');
            }

            $stmt = $pdo->prepare("SELECT * FROM content_versions
                WHERE content_id = ? AND tenant_id = ?
                ORDER BY version DESC");
            $stmt->execute([$contentId, $tenantId]);
            $versions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $handler->respond([
                'success' => true,
                'data' => $versions
            ]);
            break;

        case 'POST':
            require_once __DIR__ . '/../../../../core/csrf.php';
            csrf_validate_or_403();

            // Create new version
            $data = $handler->getRequestData();
            
            if (empty($data['content_id']) || empty($data['tenant_id'])) {
                $handler->error('Content ID and Tenant ID required');
            }
            if (empty($data['content'])) {
                $handler->error('Content data required');
            }

            $pdo->beginTransaction();
            
            try {
                // Get current version
                $stmt = $pdo->prepare("SELECT MAX(version) FROM content_versions
                    WHERE content_id = ? AND tenant_id = ?");
                $stmt->execute([$data['content_id'], $data['tenant_id']]);
                $currentVersion = (int)$stmt->fetchColumn();
                $newVersion = $currentVersion + 1;

                // Insert new version
                $stmt = $pdo->prepare("INSERT INTO content_versions 
                    (content_id, tenant_id, version, content, created_at, created_by)
                    VALUES (?, ?, ?, ?, NOW(), ?)");
                $stmt->execute([
                    $data['content_id'],
                    $data['tenant_id'],
                    $newVersion,
                    json_encode($data['content']),
                    $_SESSION['user_id'] ?? null
                ]);
                
                $pdo->commit();
                $handler->respond([
                    'success' => true,
                    'message' => 'New version created',
                    'version' => $newVersion
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                $handler->error('Database operation failed: ' . $e->getMessage(), 500);
            }
            break;

        default:
            $handler->error('Method not allowed', 405);
    }
} catch (Exception $e) {
    $handler->error($e->getMessage(), 500);
}
