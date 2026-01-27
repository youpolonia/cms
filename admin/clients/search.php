<?php
require_once __DIR__ . '/../../../includes/middleware/Authenticate.php';
require_once __DIR__ . '/../../../models/Client.php';
require_once __DIR__ . '/../../../includes/database/connection.php';

// Verify admin access
$auth = new CMS\Middleware\Authenticate();
if (!$auth->isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Get search term from request
$searchTerm = $_GET['q'] ?? '';
if (empty($searchTerm)) {
    http_response_code(400);
    echo json_encode(['error' => 'Search term is required']);
    exit;
}

// Sanitize search term
$searchTerm = trim(filter_var($searchTerm, FILTER_SANITIZE_STRING));

try {
    $connection = new CMS\Includes\Database\Connection();
    $clientModel = new CMS\Models\Client($connection);
    
    // Search clients (we'll add this method to Client model)
    $results = $clientModel->search($searchTerm);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $results
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
