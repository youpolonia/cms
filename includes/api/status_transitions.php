<?php
/**
 * Status Transition API Endpoints
 * Implements content federation status transitions
 */

require_once __DIR__ . '/../../core/statustransitionhandler.php';
require_once __DIR__ . '/../../core/httpclient.php';

// Initialize HTTP client for internal API calls
$httpClient = new HttpClient(getenv('API_BASE_URL'));

/**
 * POST /api/status/transition
 * Handles status transition requests
 */
function handleStatusTransition(array $request): array {
    // Validate authentication
    if (!validateRequest($request)) {
        return ['error' => 'Unauthorized', 'code' => 401];
    }

    // Validate required fields
    if (empty($request['from_state']) || empty($request['to_state'])) {
        return ['error' => 'Missing required fields', 'code' => 400];
    }

    try {
        $result = StatusTransitionHandler::executeTransition(
            $request['from_state'],
            $request['to_state'],
            $request['context'] ?? []
        );
        
        return ['data' => $result, 'code' => 200];
    } catch (Exception $e) {
        return ['error' => $e->getMessage(), 'code' => 400];
    }
}

/**
 * Validate API request
 */
function validateRequest(array $request): bool {
    // Check for required auth headers
    if (empty($request['headers']['X-Tenant-Context']) || 
        empty($request['headers']['X-Auth-Token'])) {
        return false;
    }

    // Add additional validation logic here
    return true;
}

/**
 * Register default transitions
 */
function registerDefaultTransitions(): void {
    StatusTransitionHandler::registerTransition(
        'draft',
        'published',
        function(array $context) {
            // Add publishing logic here
            return ['status' => 'published'];
        }
    );

    StatusTransitionHandler::registerTransition(
        'published', 
        'archived',
        function(array $context) {
            if (empty($context['user_id'])) {
                throw new Exception("User ID required for archiving");
            }
            return ['status' => 'archived'];
        }
    );
}

// Register default transitions on require_once
registerDefaultTransitions();
