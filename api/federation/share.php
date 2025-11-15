<?php
declare(strict_types=1);

require_once __DIR__ . '/../../middleware/TenantIsolation.php';
require_once __DIR__ . '/../../includes/validationhelper.php';

class ContentSharingService {
    public static function handleShareRequest(array $request): array {
        $validated = TenantIsolation::handle($request);
        if (isset($validated['error'])) {
            return $validated;
        }

        if (!ValidationHelper::validateContentSharePayload($request['body'])) {
            return TenantIsolation::errorResponse(
                'INVALID_PAYLOAD',
                'Invalid content share payload',
                ['required_fields' => ['content_id', 'target_tenants', 'version']]
            );
        }

        // Process content sharing
        $shareId = uniqid('share_', true);
        return [
            'status' => 201,
            'body' => [
                'share_id' => $shareId,
                'content_id' => $request['body']['content_id'],
                'status' => 'queued',
                'timestamp' => date('c')
            ]
        ];
    }
}

// Main request handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $request = [
        'headers' => getallheaders(),
        'body' => json_decode(file_get_contents('php://input'), true)
    ];

    $response = ContentSharingService::handleShareRequest($request);
    http_response_code($response['status'] ?? 200);
    echo json_encode($response['body'] ?? []);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
