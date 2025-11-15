<?php
require_once __DIR__ . '/../includes/api_error_handler.php';
require_once __DIR__ . '/../includes/dependencycontainer.php';

class StatusTransitionsAPI {
    private $db;
    private $tenantId;

    public function __construct() {
        $container = DependencyContainer::getInstance();
        $this->db = $container->get('Database');
        $this->tenantId = $container->get('TenantId');
    }

    /**
     * GET /api/status-transitions
     * List all status transitions
     */
    public function listTransitions() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM status_transitions WHERE tenant_id = ?");
            $stmt->execute([$this->tenantId]);
            $transitions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            json_response(['data' => $transitions]);
        } catch (PDOException $e) {
            api_error_handler(500, 'Database error: ' . $e->getMessage());
        }
    }

    /**
     * POST /api/status-transitions
     * Create new status transition
     */
    public function createTransition() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['from_status'], $input['to_status'], $input['rules'])) {
            api_error_handler(400, 'Missing required fields');
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO status_transitions 
                (tenant_id, from_status, to_status, rules, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $this->tenantId,
                $input['from_status'],
                $input['to_status'],
                json_encode($input['rules'])
            ]);
            
            $id = $this->db->lastInsertId();
            json_response(['id' => $id], 201);
        } catch (PDOException $e) {
            api_error_handler(500, 'Database error: ' . $e->getMessage());
        }
    }

    /**
     * GET /api/status-transitions/{id}
     * Get specific transition
     */
    public function getTransition($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM status_transitions 
                WHERE id = ? AND tenant_id = ?
            ");
            $stmt->execute([$id, $this->tenantId]);
            $transition = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$transition) {
                api_error_handler(404, 'Transition not found');
            }
            
            json_response(['data' => $transition]);
        } catch (PDOException $e) {
            api_error_handler(500, 'Database error: ' . $e->getMessage());
        }
    }

    /**
     * PUT /api/status-transitions/{id}
     * Update transition
     */
    public function updateTransition($id) {
        $input = json_decode(file_get_contents('php://input'), true);
        
        try {
            $stmt = $this->db->prepare("
                UPDATE status_transitions 
                SET from_status = ?, to_status = ?, rules = ?
                WHERE id = ? AND tenant_id = ?
            ");
            $stmt->execute([
                $input['from_status'] ?? null,
                $input['to_status'] ?? null,
                isset($input['rules']) ? json_encode($input['rules']) : null,
                $id,
                $this->tenantId
            ]);
            
            if ($stmt->rowCount() === 0) {
                api_error_handler(404, 'Transition not found or no changes made');
            }
            
            json_response(['success' => true]);
        } catch (PDOException $e) {
            api_error_handler(500, 'Database error: ' . $e->getMessage());
        }
    }

    /**
     * DELETE /api/status-transitions/{id}
     * Remove transition
     */
    public function deleteTransition($id) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM status_transitions 
                WHERE id = ? AND tenant_id = ?
            ");
            $stmt->execute([$id, $this->tenantId]);
            
            if ($stmt->rowCount() === 0) {
                api_error_handler(404, 'Transition not found');
            }
            
            json_response(['success' => true]);
        } catch (PDOException $e) {
            api_error_handler(500, 'Database error: ' . $e->getMessage());
        }
    }
}
