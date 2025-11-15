<?php
require_once __DIR__ . '/../core/blockmanager.php';

header('Content-Type: application/json');

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['name'])) {
                // Get specific block
                $block = BlockManager::getBlock($_GET['name']);
                if ($block) {
                    echo json_encode($block);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Block not found']);
                }
            } else {
                // List all blocks
                echo json_encode(BlockManager::listBlocks());
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['name']) || !isset($data['content'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                break;
            }

            $success = BlockManager::saveBlock(
                $data['name'],
                $data['content'],
                $data['metadata'] ?? []
            );

            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to save block']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
