<?php
require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../handlers/contentfilehandler.php';
require_once __DIR__ . '/../../../includes/tenant/Validation.php';

header('Content-Type: application/json');

try {
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = min(50, max(10, intval($_GET['per_page'] ?? 20)));
    $tenantId = $_SERVER['HTTP_X_TENANT_ID'] ?? null;
    $state = $_GET['state'] ?? null;

    if (!$tenantId || !Tenant\Validation::isValidUuid($tenantId)) {
        throw new InvalidArgumentException('Valid X-Tenant-ID header required');
    }

    $handler = new ContentFileHandler($tenantId);
    $indexFile = $handler->getIndexPath();
    $index = [];
    
    if (file_exists($indexFile)) {
        $indexData = file_get_contents($indexFile);
        if ($indexData !== false) {
            $index = json_decode($indexData, true) ?: [];
        }
    }

    // Apply filters
    $filtered = array_filter($index, function($item) use ($tenantId, $state) {
        if ($tenantId && ($item['meta']['tenant_id'] ?? null) !== $tenantId) {
            return false;
        }
        if ($state && ($item['meta']['state'] ?? null) !== $state) {
            return false;
        }
        return true;
    });

    // Paginate results
    $total = count($filtered);
    $totalPages = ceil($total / $perPage);
    $offset = ($page - 1) * $perPage;
    $results = array_slice($filtered, $offset, $perPage);

    echo json_encode([
        'success' => true,
        'data' => $results,
        'meta' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages
        ]
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'success' => false
    ]);
}
