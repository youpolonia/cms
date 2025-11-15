<?php

require_once __DIR__ . '/../../core/database.php';

class WorkflowManager {
    private $db;
    
    public function __construct() {
        $this->db = \core\Database::connection();
    }
    
    public function listWorkflows(): array {
        $query = "SELECT * FROM approval_workflows WHERE is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getWorkflow(string $id): array {
        $query = "SELECT * FROM approval_workflows WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            throw new Exception("Workflow not found");
        }
        
        return $result;
    }
    
    public function createWorkflow(array $data): array {
        $query = "INSERT INTO approval_workflows 
                 (name, description, steps, created_by) 
                 VALUES (:name, :description, :steps, :created_by)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':steps', json_encode($data['steps']));
        $stmt->bindParam(':created_by', $data['created_by']);
        $stmt->execute();
        
        return $this->getWorkflow($this->db->lastInsertId());
    }
    
    public function updateWorkflow(string $id, array $data): array {
        $query = "UPDATE approval_workflows SET
                 name = :name,
                 description = :description,
                 steps = :steps,
                 updated_at = NOW()
                 WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':steps', json_encode($data['steps']));
        $stmt->execute();
        
        return $this->getWorkflow($id);
    }
    
    public function deleteWorkflow(string $id): array {
        $workflow = $this->getWorkflow($id);
        
        $query = "UPDATE approval_workflows SET 
                 is_active = 0,
                 deleted_at = NOW()
                 WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $workflow;
    }
}
