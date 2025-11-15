<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * State Management API Endpoints
 * 
 * Handles content state transitions (draft/review/published)
 * Integrates with VersionManager for version control
 */

require_once __DIR__.'/../Core/StateManager.php';
require_once __DIR__.'/../Core/ResponseHandler.php';

header('Content-Type: application/json');

try {
    $stateManager = new StateManager();
    $responseHandler = new ResponseHandler();

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            require_once __DIR__ . '/../../core/csrf.php';
            csrf_validate_or_403();

            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['content_id']) || !isset($data['target_state'])) {
                throw new InvalidArgumentException('Missing required parameters');
            }

            $result = $stateManager->transitionState(
                $data['content_id'],
                $data['target_state'],
                $data['comment'] ?? null
            );

            echo $responseHandler->successResponse($result);
            break;

        case 'GET':
            if (!isset($_GET['content_id'])) {
                throw new InvalidArgumentException('Content ID required');
            }

            $stateInfo = $stateManager->getStateHistory($_GET['content_id']);
            echo $responseHandler->successResponse($stateInfo);
            break;

        default:
            http_response_code(405);
            echo $responseHandler->errorResponse('Method not allowed');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo $responseHandler->errorResponse($e->getMessage());
}
