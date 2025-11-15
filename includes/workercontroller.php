<?php

namespace Includes;

use Exception;
use InvalidArgumentException;
use Models\Worker;
use Services\MonitoringService;

class WorkerController
{
    private $monitoringService;

    public function __construct()
    {
        $this->monitoringService = new MonitoringService();
    }

    /**
     * Handle worker registration requests
     */
    public function register()
    {
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException('Invalid JSON input');
            }
            
            if (empty($input['name'])) {
                throw new InvalidArgumentException('Worker name is required');
            }
            
            if (empty($input['type'])) {
                throw new InvalidArgumentException('Worker type is required');
            }
            
            $worker = new Worker();
            $worker->name = $input['name'];
            $worker->type = $input['type'];
            $worker->status = $input['status'] ?? 'active';
            $worker->capabilities = $input['capabilities'] ?? [];
            
            $result = $worker->save();
            
            if ($result) {
                http_response_code(201);
                echo json_encode([
                    'id' => $worker->id,
                    'message' => 'Worker registered successfully'
                ]);
            } else {
                throw new Exception('Failed to register worker');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle worker heartbeat requests
     */
    public function heartbeat()
    {
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException('Invalid JSON input');
            }

            if (empty($input['worker_id'])) {
                throw new InvalidArgumentException('Worker ID is required');
            }

            try {
                $response = $this->monitoringService->processHeartbeat(
                    $input['worker_id'],
                    $input['metrics'] ?? []
                );

                http_response_code(200);
                echo json_encode($response);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ]);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
}
