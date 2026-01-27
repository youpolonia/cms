<?php
require_once __DIR__ . '/../../../includes/security.php';
require_once __DIR__ . '/../../../includes/rate_limit.php';
require_once __DIR__ . '/../../../core/database.php';

// Verify RBAC permissions
if (!verifyAdminAccess('users.view')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
    exit;
}

// Apply rate limiting (10 requests per minute)
applyRateLimit('user_search', 10, 60);

header('Content-Type: application/json');

try {
    $db = \core\Database::connection();
    
    // Get and validate parameters
    $searchTerm = trim($_GET['q'] ?? '');
    $role = trim($_GET['role'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = min(max(5, intval($_GET['per_page'] ?? 20)), 100);

    // Base query with additional fields
    $query = "SELECT
                u.id, u.username, u.email, u.status, u.created_at, u.last_login,
                r.name as role_name, r.id as role_id
              FROM users u
              LEFT JOIN roles r ON u.role_id = r.id
              WHERE 1=1";
    
    $params = [];
    
    // Apply filters
    if ($searchTerm) {
        $query .= " AND (u.username LIKE ? OR u.email LIKE ?)";
        $params[] = "%$searchTerm%";
        $params[] = "%$searchTerm%";
    }
    
    if ($role) {
        $query .= " AND r.name = ?";
        $params[] = $role;
    }
    
    if ($status) {
        $query .= " AND u.status = ?";
        $params[] = $status;
    }
    
    // Get total count
    $countQuery = "SELECT COUNT(*) FROM ($query) AS total";
    $stmt = $db->prepare($countQuery);
    $stmt->execute($params);
    $totalUsers = $stmt->fetchColumn();
    $totalPages = ceil($totalUsers / $perPage);
    
    // Apply pagination
    $query .= " LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = ($page - 1) * $perPage;
    
    // Get results
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format response with metadata
    $response = [
        'success' => true,
        'data' => $users,
        'meta' => [
            'filters' => [
                'search' => $searchTerm,
                'role' => $role,
                'status' => $status
            ],
            'pagination' => [
                'total' => $totalUsers,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages
            ]
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("User search API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error processing request'
    ]);
}
