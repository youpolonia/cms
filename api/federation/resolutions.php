<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/core/apiresponse.php';
require_once __DIR__ . '/../../includes/core/auth.php';
require_once __DIR__ . '/../../core/database.php';

// Verify API key and permissions
if (!Auth::verifyAPIKey()) {
    APIResponse::rejectUnauthorized();
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    APIResponse::rejectMethodNotAllowed();
    exit;
}

try {
    // Get query parameters
    $limit = min(100, (int)($_GET['limit'] ?? 20));
    $offset = max(0, (int)($_GET['offset'] ?? 0));
    
    // Get resolution history from database
    $db = \core\Database::connection();
    
    // Get total count
    $countStmt = $db->prepare("SELECT COUNT(*) FROM conflict_resolutions");
    $countStmt->execute();
    $totalCount = (int)$countStmt->fetchColumn();
    
    // Get paginated results
    $query = "SELECT
                id,
                conflict_id,
                resolved_by,
                resolution_type,
                resolution_details,
                created_at
              FROM conflict_resolutions
              ORDER BY created_at DESC
              LIMIT :limit OFFSET :offset";
    
    $stmt = $db->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $resolutions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    APIResponse::success([
        'resolutions' => $resolutions,
        'count' => count($resolutions),
        'total' => $totalCount,
        'limit' => $limit,
        'offset' => $offset
    ]);
} catch (Exception $e) {
    APIResponse::rejectServerError($e->getMessage());
}
