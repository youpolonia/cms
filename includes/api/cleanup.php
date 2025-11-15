<?php
require_once __DIR__ . '/../services/siteservice.php';

class CleanupAPI {
    private $siteService;

    public function __construct() {
        $this->siteService = new SiteService();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $params = $_GET;

        if ($method === 'GET' && isset($params['manual'])) {
            $this->manualCleanup($params);
        } elseif ($method === 'GET' && isset($params['scheduled'])) {
            $this->scheduledCleanup();
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request']);
        }
    }

    private function manualCleanup($params) {
        try {
            $this->siteService->beginTransaction();
            
            $days = $params['days'] ?? 30;
            $limit = $params['limit'] ?? 1000;
            
            $result = $this->siteService->cleanupOldVersions($days, $limit);
            
            $this->siteService->commit();
            
            echo json_encode([
                'success' => true,
                'deleted' => $result,
                'message' => "Cleaned up $result old versions"
            ]);
        } catch (Exception $e) {
            $this->siteService->rollback();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function scheduledCleanup() {
        try {
            $maintenanceTasks = $this->siteService->getPendingMaintenanceTasks();
            $results = [];
            
            foreach ($maintenanceTasks as $task) {
                if ($task['event_type'] === 'maintenance' && $task['maintenance_type'] === 'cleanup') {
                    $this->siteService->beginTransaction();
                    
                    $config = json_decode($task['maintenance_data'], true);
                    $days = $config['days'] ?? 30;
                    $limit = $config['limit'] ?? 1000;
                    
                    $result = $this->siteService->cleanupOldVersions($days, $limit);
                    $results[] = [
                        'task_id' => $task['id'],
                        'deleted' => $result,
                        'next_run' => $this->siteService->calculateNextRun($task['recurrence'])
                    ];
                    
                    $this->siteService->updateTaskRunTime($task['id']);
                    $this->siteService->commit();
                }
            }
            
            echo json_encode([
                'success' => true,
                'results' => $results
            ]);
        } catch (Exception $e) {
            $this->siteService->rollback();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

$api = new CleanupAPI();
$api->handleRequest();
