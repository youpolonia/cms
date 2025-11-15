<?php
declare(strict_types=1);

require_once __DIR__ . '/../../api/gateway/middleware/authmiddleware.php';

/**
 * GET /api/analytics/versions
 * Returns version metrics for content pages and blocks
 */
return function(array $request): array {
    // Authenticate request
    $authResult = Api\Gateway\Middleware\AuthMiddleware::handle($request, function($request) {
        // Get tenant ID from JWT token
        $tenantId = $request['jwt']['tenant_id'] ?? null;
        if (!$tenantId) {
            return [
                'status' => 400,
                'body' => json_encode(['error' => 'Missing tenant ID'])
            ];
        }

        try {
            $db = []; // legacy alt DB config removed; use \core\Database::connection()
            
            // Get version metrics for content pages
            $pagesQuery = "SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN current_status = 'published' THEN 1 END) as published,
                COUNT(CASE WHEN current_status = 'draft' THEN 1 END) as draft,
                COUNT(CASE WHEN current_status = 'archived' THEN 1 END) as archived
                FROM content_pages";

            $pagesStmt = $db->prepare($pagesQuery . " WHERE tenant_id = ?");
            $pagesStmt->execute([$tenantId]);
            $pagesMetrics = $pagesStmt->fetch(PDO::FETCH_ASSOC);

            // Get version metrics for content blocks  
            $blocksQuery = "SELECT
                COUNT(*) as total,
                COUNT(CASE WHEN current_status = 'published' THEN 1 END) as published,
                COUNT(CASE WHEN current_status = 'draft' THEN 1 END) as draft,
                COUNT(CASE WHEN current_status = 'archived' THEN 1 END) as archived
                FROM content_blocks";

            $blocksStmt = $db->prepare($blocksQuery . " WHERE tenant_id = ?");
            $blocksStmt->execute([$tenantId]);
            $blocksMetrics = $blocksStmt->fetch(PDO::FETCH_ASSOC);

            return [
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'pages' => $pagesMetrics,
                    'blocks' => $blocksMetrics
                ])
            ];
        } catch (PDOException $e) {
            return [
                'status' => 500,
                'body' => json_encode(['error' => 'Database error: ' . $e->getMessage()])
            ];
        }
    });

    return $authResult;
};
