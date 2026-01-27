<?php
/**
 * Slug Availability Check API
 * Checks if a slug is available for content entries
 */

define('CMS_ROOT', dirname(__DIR__, 2));
require_once CMS_ROOT . '/config.php';

// Session management
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

// CSRF protection
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot();

// Authentication
require_once CMS_ROOT . '/core/auth.php';
authenticateAdmin();

// Database connection
require_once CMS_ROOT . '/core/database.php';

header('Content-Type: application/json');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

// Validate CSRF token
csrf_validate_or_403();

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
$slug = $input['slug'] ?? '';
$content_id = $input['content_id'] ?? null;

if (empty($slug)) {
    http_response_code(400);
    die(json_encode(['error' => 'Slug is required']));
}

try {
    // Get centralized database connection
    $db = \core\Database::connection();

    // Check slug availability
    $query = "SELECT id FROM content_entries WHERE slug = ?";
    $params = [$slug];

    if ($content_id) {
        $query .= " AND id != ?";
        $params[] = $content_id;
    }

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'available' => !$existing,
        'slug' => $slug
    ]);
} catch (\Throwable $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['error' => 'Internal error']);
}
