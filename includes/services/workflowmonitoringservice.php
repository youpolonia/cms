<?php
/**
 * Workflow Monitoring Service
 * 
 * Tracks and manages workflow states tied to scheduled events
 * Provides integration with n8n workflows via REST API
 */

class WorkflowMonitoringService {
    private $db;
    private $apiClient;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->apiClient = new RestApiClient();
    }

    /**
     * Log workflow state change
     */
    public function logStateChange($eventId, $fromState, $toState, $userId) {
        $query = "INSERT INTO workflow_monitoring 
                 (event_id, from_state, to_state, changed_by, changed_at)
                 VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$eventId, $fromState, $toState, $userId]);
    }

    /**
     * Trigger n8n workflow webhook
     */
    public function triggerN8nWorkflow($workflowId, $payload) {
        $endpoint = "https://n8n.example.com/webhook/{$workflowId}";
        return $this->apiClient->post($endpoint, $payload);
    }

    /**
     * Get workflow history for an event
     */
    public function getEventHistory($eventId) {
        $query = "SELECT * FROM workflow_monitoring 
                 WHERE event_id = ? ORDER BY changed_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

class RestApiClient {
    public function post($url, $data) {
        // Implementation would make HTTP request
        return true;
    }
}
