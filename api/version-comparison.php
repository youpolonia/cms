<?php
require_once __DIR__ . '/../core/contentversion.php';
require_once __DIR__ . '/../core/auth.php';

header('Content-Type: application/json');

if (!Auth::checkToken()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['version1'], $input['version2'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing version IDs']);
    exit;
}

try {
    $diff = ContentVersion::compare(
        (int)$input['version1'],
        (int)$input['version2']
    );
    echo json_encode($diff);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
