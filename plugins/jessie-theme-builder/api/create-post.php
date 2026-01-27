<?php
/**
 * JTB API - Create Page/Article
 * Creates a new page or article and returns the ID for JTB editing
 *
 * POST /api/jtb/create-post
 * Body: { type: 'page'|'article', title: string }
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Auth check
if (!\Core\Session::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);

$type = $input['type'] ?? '';
$title = trim($input['title'] ?? '');

// Validate
if (!in_array($type, ['page', 'article'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid type. Must be "page" or "article"']);
    exit;
}

if (empty($title)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Title is required']);
    exit;
}

// Generate slug
$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

// Ensure unique slug
$db = \core\Database::connection();
$table = ($type === 'page') ? 'pages' : 'articles';

$baseSlug = $slug;
$counter = 1;
while (true) {
    $stmt = $db->prepare("SELECT id FROM {$table} WHERE slug = ?");
    $stmt->execute([$slug]);
    if (!$stmt->fetch()) {
        break;
    }
    $slug = $baseSlug . '-' . $counter;
    $counter++;
}

try {
    if ($type === 'page') {
        // Create page
        $stmt = $db->prepare("
            INSERT INTO pages (title, slug, content, status, created_at, updated_at)
            VALUES (?, ?, '', 'draft', NOW(), NOW())
        ");
        $stmt->execute([$title, $slug]);
    } else {
        // Create article
        $authorId = \Core\Session::get('user_id') ?? 1;
        $stmt = $db->prepare("
            INSERT INTO articles (title, slug, content, status, author_id, created_at, updated_at)
            VALUES (?, ?, '', 'draft', ?, NOW(), NOW())
        ");
        $stmt->execute([$title, $slug, $authorId]);
    }

    $postId = $db->lastInsertId();

    // Return success with redirect URL
    echo json_encode([
        'success' => true,
        'post_id' => $postId,
        'type' => $type,
        'title' => $title,
        'slug' => $slug,
        'edit_url' => "/admin/jessie-theme-builder/edit/{$postId}?type={$type}"
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
