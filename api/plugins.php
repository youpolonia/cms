<?php
require_once __DIR__ . '/../admin/core/pluginservice.php';

header('Content-Type: application/json');

try {
    $pluginService = PluginService::getInstance();
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'registry':
                        echo json_encode($pluginService->getAvailablePlugins());
                        break;
                    case 'installed':
                        echo json_encode($pluginService->getInstalledPlugins());
                        break;
                    default:
                        http_response_code(400);
                        echo json_encode(['error' => 'Invalid action']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Action parameter required']);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['pluginId'])) {
                http_response_code(400);
                echo json_encode(['error' => 'pluginId required']);
                break;
            }
            
            $result = $pluginService->installPlugin(
                $data['pluginId'],
                $data['licenseKey'] ?? null
            );
            
            echo json_encode($result);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
