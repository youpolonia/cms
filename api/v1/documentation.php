<?php
require_once __DIR__ . '/../../includes/services/DocGenerator.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$version = $_GET['version'] ?? '1.0.0';

try {
    switch ($action) {
        case 'generate':
            $result = DocGenerator::generateApiDocs('storage/docs', $version);
            echo json_encode(['success' => $result]);
            break;
            
        case 'get':
            $docs = DocGenerator::getVersionedDocs($version);
            echo json_encode($docs ?? ['error' => 'Documentation not found']);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
