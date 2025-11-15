<?php
/**
 * Worker Controller - Handles worker registration, monitoring and scaling
 */
class WorkerController {
    private $supervisor;
    private $db;

    public function __construct($db, $workerSupervisor) {
        $this->db = $db;
        $this->supervisor = $workerSupervisor;
    }

    public function registerWorker($request): array {
        $input = json_decode($request, true);
        
        if (empty($input['worker_type'])) {
            return $this->errorResponse('Missing worker type');
        }

        try {
            $workerId = $this->supervisor->registerWorker(
                $input['worker_type'],
                $input['capabilities'] ?? []
            );

            return [
                'success' => true,
                'data' => ['worker_id' => $workerId]
            ];
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function heartbeat($request): array {
        $input = json_decode($request, true);
        
        if (empty($input['worker_id']) || empty($input['metrics'])) {
            return $this->errorResponse('Missing required fields');
        }

        try {
            $result = $this->supervisor->processHeartbeat(
                $input['worker_id'],
                $input['metrics']
            );

            return [
                'success' => true,
                'data' => $result
            ];
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getMetrics(): array {
        return $this->supervisor->getAggregateMetrics();
    }

    public function getScalingRecommendations(): array {
        return $this->supervisor->getScalingRecommendations();
    }

    /**
     * Returns worker status overview
     */
    public function status(): array {
        $workers = $this->db->query("
            SELECT
                worker_id,
                health_score,
                failure_count,
                last_seen
            FROM workers
            WHERE last_seen > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ORDER BY health_score ASC
        ")->fetchAll();

        return [
            'success' => true,
            'data' => [
                'workers' => $workers,
                'timestamp' => time()
            ]
        ];
    }
    
    /**
     * Update schedule status with state machine logic
     *
     * @param array $request Request data
     * @return array Response with success/error status
     */
    public function updateScheduleStatus($request): array {
        $input = json_decode($request, true);
        
        if (empty($input['shift_id'])) {
            return $this->errorResponse('Shift ID is required');
        }
        
        if (empty($input['status'])) {
            return $this->errorResponse('Status is required');
        }
        
        try {
            // Get shift model
            require_once __DIR__ . '/../../../models/shift.php';
            require_once __DIR__ . '/../../../models/notification.php';
            
            $shift = new \Shift($this->db);
            $notification = new \Notification($this->db);
            
            // Get current shift data
            $shiftData = $shift->get((int)$input['shift_id']);
            
            if (!$shiftData) {
                return $this->errorResponse('Shift not found');
            }
            
            // Define state machine transitions
            $validTransitions = [
                'scheduled' => ['approved', 'rejected', 'cancelled'],
                'pending' => ['approved', 'rejected', 'cancelled'],
                'approved' => ['completed', 'cancelled'],
                'rejected' => ['scheduled', 'pending'],
                'cancelled' => ['scheduled', 'pending'],
                'completed' => []
            ];
            
            $currentStatus = $shiftData['status'];
            $newStatus = $input['status'];
            
            // Validate transition
            if (!in_array($newStatus, $validTransitions[$currentStatus] ?? [])) {
                return $this->errorResponse("Invalid status transition from '{$currentStatus}' to '{$newStatus}'");
            }
            
            // Update shift status
            $updateData = [
                'worker_id' => $shiftData['worker_id'],
                'start_time' => $shiftData['start_time'],
                'end_time' => $shiftData['end_time'],
                'status' => $newStatus,
                'location' => $shiftData['location'],
                'notes' => $shiftData['notes']
            ];
            
            // Add status change reason if provided
            if (!empty($input['reason'])) {
                $updateData['notes'] = ($updateData['notes'] ? $updateData['notes'] . "\n\n" : '') .
                                      "Status changed to {$newStatus}: " . $input['reason'];
            }
            
            $success = $shift->update((int)$input['shift_id'], $updateData);
            
            if (!$success) {
                return $this->errorResponse('Failed to update shift status');
            }
            
            // Log the status change
            if (class_exists('AuditLogger')) {
                \AuditLogger::logAccess(
                    $_SESSION['user_id'] ?? null,
                    'shift',
                    'status_change',
                    (string)$input['shift_id']
                );
            }
            
            // Create notification for the worker
            $notificationTitle = "Shift Status Update";
            $notificationMessage = "Your shift on " . date('F j, Y', strtotime($shiftData['start_time'])) .
                                  " has been {$newStatus}";
            
            if (!empty($input['reason'])) {
                $notificationMessage .= ". Reason: " . $input['reason'];
            }
            
            $notification->create($shiftData['worker_id'], $notificationTitle, $notificationMessage, 1);
            
            // Return success response
            return [
                'success' => true,
                'message' => "Shift status updated to {$newStatus}",
                'shift_id' => $input['shift_id'],
                'status' => $newStatus
            ];
            
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    private function errorResponse(string $message): array {
        return [
            'success' => false,
            'error' => $message
        ];
    }
}
