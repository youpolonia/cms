<?php
/**
 * Search API for Command Palette
 * GET /api/search.php?type={pages|articles|all}&q={query}&limit={20}
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../core/database.php';

// Check if admin is logged in
cms_session_start('admin');
if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

$type = $_GET['type'] ?? 'all';
$query = trim($_GET['q'] ?? '');
$limit = min((int)($_GET['limit'] ?? 20), 50);

$pdo = \core\Database::connection();
$results = [];

try {
    // Search pages
    if ($type === 'all' || $type === 'pages') {
        $sql = "SELECT id, title, slug, status FROM pages";
        $params = [];

        if ($query) {
            $sql .= " WHERE title LIKE ? OR slug LIKE ?";
            $params = ["%$query%", "%$query%"];
        }

        $sql .= " ORDER BY updated_at DESC LIMIT " . (int)$limit;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $results[] = [
                'type' => 'page',
                'id' => $row['id'],
                'title' => $row['title'],
                'slug' => $row['slug'],
                'status' => $row['status'],
                'url' => "/admin/pages/edit/{$row['id']}"
            ];
        }
    }

    // Search articles
    if ($type === 'all' || $type === 'articles') {
        $sql = "SELECT id, title, slug, status FROM articles";
        $params = [];

        if ($query) {
            $sql .= " WHERE title LIKE ? OR slug LIKE ?";
            $params = ["%$query%", "%$query%"];
        }

        $sql .= " ORDER BY updated_at DESC LIMIT " . (int)$limit;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $results[] = [
                'type' => 'article',
                'id' => $row['id'],
                'title' => $row['title'],
                'slug' => $row['slug'],
                'status' => $row['status'],
                'url' => "/admin/articles/edit/{$row['id']}"
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $results,
        'count' => count($results)
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'data' => [],
        'count' => 0
    ]);
}
