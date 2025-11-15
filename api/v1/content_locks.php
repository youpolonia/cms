<?php
require_once __DIR__ . '/../../includes/core/apiresponse.php';

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $contentId = $_GET['content_id'] ?? $input['content_id'] ?? null;
    $userId = $_GET['user_id'] ?? $input['user_id'] ?? null;

    if (!$contentId) {
        throw new InvalidArgumentException('Missing content_id parameter');
    }

    switch ($method) {
        case 'GET':
            $lockStatus = ContentLock::getContentLock($contentId);
            APIResponse::success($lockStatus);
            break;

        case 'POST':
            if (!$userId) {
                throw new InvalidArgumentException('Missing user_id parameter');
            }
            $timeout = $input['timeout'] ?? 1800;
            $success = ContentLock::setContentLock($contentId, $userId, $timeout);
            APIResponse::success(['success' => $success]);
            break;

        case 'DELETE':
            $success = ContentLock::clearContentLock($contentId);
            APIResponse::success(['success' => $success]);
            break;

        default:
            APIResponse::error('Method not allowed', 405);
    }
} catch (Exception $e) {
    APIResponse::error($e->getMessage(), 400);
}
