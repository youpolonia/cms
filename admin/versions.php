<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/database.php';

declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__.'/../core/database.php';
require_once __DIR__.'/../services/authservice.php';

$db = \core\Database::connection();
$auth = new AuthService($db);

// Authenticate API request
$userId = $auth->getCurrentUserId();
if (!$userId) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized']));
}

// Check version management permissions
if (!$auth->hasPermission($userId, 'manage_versions')) {
    http_response_code(403);
    die(json_encode(['error' => 'Forbidden']));
}

$action = $_GET['action'] ?? '';
try {
    switch ($action) {
        case 'history':
            handleVersionHistory($db, $userId);
            break;
        case 'compare':
            handleVersionCompare($db, $userId);
            break;
        case 'rollback':
            handleVersionRollback($db, $userId);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}

function handleVersionHistory(PDO $db, int $userId): void {
    $contentId = filter_input(INPUT_GET, 'content_id', FILTER_VALIDATE_INT);
    if (!$contentId) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid content ID']);
        return;
    }

    $stmt = $db->prepare("
        SELECT v.version_id, v.version_number, v.created_at, 
               u.username as created_by, r.notes as rollback_notes
        FROM content_versions v
        JOIN users u ON v.created_by = u.id
        LEFT JOIN rollback_points r ON v.version_id = r.version_id
        WHERE v.content_id = ?
        ORDER BY v.version_number DESC
        LIMIT 100
    ");
    $stmt->execute([$contentId]);
    $versions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['data' => $versions]);
}

function handleVersionCompare(PDO $db, int $userId): void {
    $version1 = filter_input(INPUT_GET, 'version1', FILTER_VALIDATE_INT);
    $version2 = filter_input(INPUT_GET, 'version2', FILTER_VALIDATE_INT);
    
    if (!$version1 || !$version2) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid version IDs']);
        return;
    }

    $stmt = $db->prepare("
        SELECT v1.content_data as content1, v2.content_data as content2,
               v1.version_number as version1, v2.version_number as version2
        FROM content_versions v1
        JOIN content_versions v2 ON v1.content_id = v2.content_id
        WHERE v1.version_id = ? AND v2.version_id = ?
    ");
    $stmt->execute([$version1, $version2]);
    $comparison = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comparison) {
        http_response_code(404);
        echo json_encode(['error' => 'Versions not found or not comparable']);
        return;
    }

    // Simple diff implementation - could be enhanced with proper diff library
    $diff = [
        'version1' => $comparison['version1'],
        'version2' => $comparison['version2'],
        'changes' => compareContent($comparison['content1'], $comparison['content2'])
    ];

    echo json_encode(['data' => $diff]);
}

function handleVersionRollback(PDO $db, int $userId): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $versionId = filter_var($input['version_id'] ?? null, FILTER_VALIDATE_INT);
    $notes = filter_var($input['notes'] ?? '', FILTER_SANITIZE_STRING);

    if (!$versionId) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid version ID']);
        return;
    }

    $db->beginTransaction();
    try {
        // Get version data
        $stmt = $db->prepare("
            SELECT content_id, content_data 
            FROM content_versions 
            WHERE version_id = ?
        ");
        $stmt->execute([$versionId]);
        $version = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$version) {
            throw new PDOException('Version not found');
        }

        // Update current content
        $db->prepare("
            UPDATE content 
            SET content_data = ? 
            WHERE id = ?
        ")->execute([$version['content_data'], $version['content_id']]);

        // Record rollback
        $db->prepare("
            INSERT INTO rollback_points 
            (version_id, rolled_back_by, notes)
            VALUES (?, ?, ?)
        ")->execute([$versionId, $userId, $notes]);

        $db->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Rollback failed']);
    }
}

function compareContent(string $old, string $new): array {
    // Basic line-by-line comparison
    $oldLines = explode("\n", $old);
    $newLines = explode("\n", $new);
    
    $diff = [];
    $maxLines = max(count($oldLines), count($newLines));
    
    for ($i = 0; $i < $maxLines; $i++) {
        $oldLine = $oldLines[$i] ?? null;
        $newLine = $newLines[$i] ?? null;
        
        if ($oldLine !== $newLine) {
            $diff[] = [
                'line' => $i + 1,
                'old' => $oldLine,
                'new' => $newLine
            ];
        }
    }
    
    return $diff;
}
