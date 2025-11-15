<?php
/**
 * Batch Job Item Model
 * Pure PHP implementation - no framework dependencies
 */

class BatchJobItem {
    private $connection;
    
    public function __construct() {
        $this->connection = \core\Database::connection();
    }

    public function create(int $jobId, string $itemId, array $data = []) {
        $stmt = $this->connection->prepare(
            "INSERT INTO batch_job_items 
            (job_id, item_id, status, result) 
            VALUES (?, ?, ?, ?)"
        );
        
        $result = json_encode($data['result'] ?? []);
        $stmt->bind_param(
            "isss", 
            $jobId,
            $itemId,
            $data['status'] ?? 'pending',
            $result
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create batch job item: " . $stmt->error);
        }
        
        return $this->connection->insert_id;
    }

    public function getByJobAndItem(int $jobId, string $itemId) {
        $stmt = $this->connection->prepare(
            "SELECT * FROM batch_job_items 
            WHERE job_id = ? AND item_id = ?"
        );
        $stmt->bind_param("is", $jobId, $itemId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function updateStatus(int $id, string $status, array $result = []) {
        $stmt = $this->connection->prepare(
            "UPDATE batch_job_items 
            SET status = ?, 
            result = ?,
            processed_at = IF(? IN ('completed', 'failed'), NOW(), processed_at)
            WHERE id = ?"
        );
        
        $resultJson = json_encode($result);
        $stmt->bind_param("sssi", $status, $resultJson, $status, $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update batch job item: " . $stmt->error);
        }
        
        return $stmt->affected_rows > 0;
    }

    public function getItemsByJob(int $jobId, string $status = null) {
        $sql = "SELECT * FROM batch_job_items WHERE job_id = ?";
        $params = [$jobId];
        $types = "i";
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
}
