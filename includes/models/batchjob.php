<?php
/**
 * Batch Job Model
 * Pure PHP implementation - no framework dependencies
 */

class BatchJob {
    private $connection;
    
    public function __construct() {
        $this->connection = \core\Database::connection();
    }

    public function create(array $data) {
        $stmt = $this->connection->prepare(
            "INSERT INTO batch_jobs 
            (type, status, payload) 
            VALUES (?, ?, ?)"
        );
        
        $payload = json_encode($data['payload'] ?? []);
        $stmt->bind_param(
            "sss", 
            $data['type'],
            $data['status'] ?? 'pending',
            $payload
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create batch job: " . $stmt->error);
        }
        
        return $this->connection->insert_id;
    }

    public function getById(int $id) {
        $stmt = $this->connection->prepare(
            "SELECT * FROM batch_jobs WHERE id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function updateStatus(int $id, string $status) {
        $stmt = $this->connection->prepare(
            "UPDATE batch_jobs 
            SET status = ?, 
            started_at = IF(? = 'processing' AND started_at IS NULL, NOW(), started_at),
            completed_at = IF(? IN ('completed', 'failed'), NOW(), completed_at)
            WHERE id = ?"
        );
        $stmt->bind_param("sssi", $status, $status, $status, $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update batch job status: " . $stmt->error);
        }
        
        return $stmt->affected_rows > 0;
    }
}
