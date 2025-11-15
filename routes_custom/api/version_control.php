<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../services/ContentVersionService.php';

header('Content-Type: application/json');

try {
    $pdo = \core\Database::connection();
    $versionService = new ContentVersionService($pdo);

    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $parts = explode('/', trim($path, '/'));

    if ($parts[1] === 'api' && $parts[2] === 'version') {
        switch ($method) {
            case 'POST':
                if ($parts[3] === 'create') {
                    $input = json_decode(file_get_contents('php://input'), true);
                    $result = $versionService->createVersion(
                        $input['content_id'],
                        $input['version_data'],
                        $input['user_id']
                    );
                    echo json_encode(['success' => $result]);
                } elseif ($parts[3] === 'detect-conflicts') {
                    $input = json_decode(file_get_contents('php://input'), true);
                    $conflicts = $versionService->detectConflicts(
                        $input['content_id'],
                        $input['version_data']
                    );
                    echo json_encode(['conflicts' => $conflicts]);
                } elseif ($parts[3] === 'resolve-conflict') {
                    $input = json_decode(file_get_contents('php://input'), true);
                    $result = $versionService->resolveConflict(
                        $input['conflict_id'],
                        $input['user_id']
                    );
                    echo json_encode(['success' => $result]);
                } elseif ($parts[3] === 'create-branch') {
                    $input = json_decode(file_get_contents('php://input'), true);
                    $result = $versionService->createBranch(
                        $input['content_id'],
                        $input['branch_name'],
                        $input['base_version_id'],
                        $input['user_id']
                    );
                    echo json_encode(['success' => $result]);
                }
                break;

            case 'GET':
                if ($parts[3] === 'conflicts' && isset($parts[4])) {
                    $contentId = (int)$parts[4];
                    $stmt = $pdo->prepare("
                        SELECT * FROM version_conflicts 
                        WHERE content_id = ? AND resolved_at IS NULL
                    ");
                    $stmt->execute([$contentId]);
                    $conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo json_encode($conflicts);
                } elseif ($parts[3] === 'branches' && isset($parts[4])) {
                    $contentId = (int)$parts[4];
                    $stmt = $pdo->prepare("
                        SELECT * FROM version_branches 
                        WHERE content_id = ?
                    ");
                    $stmt->execute([$contentId]);
                    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo json_encode($branches);
                }
                break;
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
