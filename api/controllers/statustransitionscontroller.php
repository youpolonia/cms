<?php

class StatusTransitionsController {
    /**
     * Log a status transition
     * POST /api/status/transitions
     */
    public static function logTransition() {
        header('Content-Type: application/json');
        
        try {
            // Validate input
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['entity_type']) || !isset($input['entity_id']) 
                || !isset($input['from_status']) || !isset($input['to_status'])) {
                throw new Exception('Missing required fields', 400);
            }

            // Prepare statement
            global $pdo;
            $stmt = $pdo->prepare("
                INSERT INTO status_transitions 
                (entity_type, entity_id, from_status, to_status, reason) 
                VALUES (:entity_type, :entity_id, :from_status, :to_status, :reason)
            ");

            // Execute with input data
            $success = $stmt->execute([
                ':entity_type' => $input['entity_type'],
                ':entity_id' => $input['entity_id'],
                ':from_status' => $input['from_status'],
                ':to_status' => $input['to_status'],
                ':reason' => $input['reason'] ?? null
            ]);

            if (!$success) {
                throw new Exception('Failed to log transition', 500);
            }

            // Return success response
            echo json_encode([
                'status' => 'success',
                'transition_id' => $pdo->lastInsertId(),
                'timestamp' => date('c')
            ]);
        } catch (Exception $e) {
            http_response_code(is_numeric($e->getCode()) ? $e->getCode() : 500);
            echo json_encode([
                'error' => [
                    'code' => 'TRANSITION_ERROR',
                    'message' => $e->getMessage()
                ]
            ]);
        }
    }

    /**
     * Get transition history for an entity
     * GET /api/status/transitions
     */
    public static function getTransitions() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_GET['entity_type']) || !isset($_GET['entity_id'])) {
                throw new Exception('Entity type and ID required', 400);
            }

            global $pdo;
            $stmt = $pdo->prepare("
                SELECT * FROM status_transitions 
                WHERE entity_type = :entity_type AND entity_id = :entity_id
                ORDER BY transition_time DESC
            ");

            $stmt->execute([
                ':entity_type' => $_GET['entity_type'],
                ':entity_id' => $_GET['entity_id']
            ]);

            $transitions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($transitions);
        } catch (Exception $e) {
            http_response_code(is_numeric($e->getCode()) ? $e->getCode() : 500);
            echo json_encode([
                'error' => [
                    'code' => 'TRANSITION_QUERY_ERROR',
                    'message' => $e->getMessage()
                ]
            ]);
        }
    }
}
