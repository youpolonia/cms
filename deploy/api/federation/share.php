<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../middleware/TenantIsolation.php';
require_once __DIR__ . '/../../../includes/contentversionmanager.php';

class ContentSharingService {
    public static function handleShareRequest(array $request): array {
        $validated = TenantIsolation::handle($request);
        if (isset($validated['error'])) {
            return $validated;
        }

        if (!isset($request['body']['content'])) {
            return TenantIsolation::errorResponse(
                'MISSING_CONTENT',
                'Missing content to share',
                ['required_fields' => ['content']]
            );
        }

        try {
            $version = ContentVersionManager::createVersion(
                $validated['tenant'],
                $request['body']['content']
            );
            
            return [
                'status' => 201,
                'body' => [
                    'version_id' => $version['id'],
                    'timestamp' => date('c')
                ]
            ];
        } catch (Exception $e) {
            return TenantIsolation::errorResponse(
                'SHARING_ERROR',
                'Failed to share content',
                ['error_details' => $e->getMessage()]
            );
        }
    }
}

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
