<?php
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/../core/indexbuilder.php';
require_once __DIR__ . '/../core/searchengine.php';

header('Content-Type: application/json');

try {
    $pdo = \core\Database::connection();

    $searchEngine = new SearchEngine($pdo);
    $tenantId = $_GET['tenant_id'] ?? $_POST['tenant_id'] ?? '';
    $query = $_GET['q'] ?? $_POST['q'] ?? '';
    $operator = strtoupper($_GET['operator'] ?? $_POST['operator'] ?? 'AND');
    
    if (empty($tenantId) || empty($query)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required parameters']);
        exit;
    }

    $options = [
        'limit' => (int)($_GET['limit'] ?? $_POST['limit'] ?? 20),
        'offset' => (int)($_GET['offset'] ?? $_POST['offset'] ?? 0),
        'version' => $_GET['version'] ?? $_POST['version'] ?? null,
        'min_score' => (float)($_GET['min_score'] ?? $_POST['min_score'] ?? 1.0),
        'operator' => in_array($operator, ['AND', 'OR', 'NOT']) ? $operator : 'AND'
    ];

    $searchResults = $searchEngine->search($query, $tenantId, $options);
    echo json_encode([
        'success' => true,
        'results' => $searchResults['items'],
        'count' => count($searchResults['items']),
        'pagination' => $searchResults['pagination']
    ], JSON_UNESCAPED_SLASHES);
} catch (\PDOException $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
} catch (\Exception $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
