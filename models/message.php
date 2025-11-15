<?php

require_once __DIR__ . '/../config.php';

class Message {
    private $db;

    public function __construct() {
        $this->db = \core\Database::connection();
    }

    public function getThreads($userId, $filters = [], $page = 1, $perPage = 10) {
        $where = "(t.sender_id = ? OR t.recipient_id = ?)";
        $params = [$userId, $userId];
        $types = "ii";

        // Apply filters
        if (!empty($filters['status'])) {
            $where .= " AND t.archived = ?";
            $params[] = $filters['status'] === 'archived' ? 1 : 0;
            $types .= "i";
        }

        if (!empty($filters['date_from'])) {
            $where .= " AND t.created_at >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }

        if (!empty($filters['date_to'])) {
            $where .= " AND t.created_at <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }

        // Count total threads for pagination
        $countStmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM threads t
            WHERE $where
        ");
        $countStmt->bind_param($types, ...$params);
        $countStmt->execute();
        $total = $countStmt->get_result()->fetch(PDO::FETCH_ASSOC)['total'];

        // Get paginated threads
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("
            SELECT t.*,
                   (SELECT content FROM messages WHERE thread_id = t.id ORDER BY created_at DESC LIMIT 1) as last_message,
                   (SELECT created_at FROM messages WHERE thread_id = t.id ORDER BY created_at DESC LIMIT 1) as last_message_date
            FROM threads t
            WHERE $where
            ORDER BY last_message_date DESC
            LIMIT ?, ?
        ");
        $params[] = $offset;
        $params[] = $perPage;
        $types .= "ii";
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        return [
            'threads' => $stmt->get_result()->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage
        ];
    }

    public function getThreadMessages($threadId) {
        $stmt = $this->db->prepare("
            SELECT m.*, u.username as sender_name
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.thread_id = ?
            ORDER BY m.created_at ASC
        ");
        $stmt->bind_param("i", $threadId);
        $stmt->execute();
        return $stmt->get_result()->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createThread($recipientId, $senderId, $subject, $message) {
        $this->db->begin_transaction();
        
        try {
            // Create thread
            $stmt = $this->db->prepare("
                INSERT INTO threads (subject, sender_id, recipient_id, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->bind_param("sii", $subject, $senderId, $recipientId);
            $stmt->execute();
            $threadId = $this->db->insert_id;

            // Add first message
            $this->addMessage($threadId, $senderId, $message);

            $this->db->commit();
            return $threadId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function addReply($threadId, $senderId, $message) {
        return $this->addMessage($threadId, $senderId, $message);
    }

    private function addMessage($threadId, $senderId, $message) {
        $stmt = $this->db->prepare("
            INSERT INTO messages (thread_id, sender_id, content, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->bind_param("iis", $threadId, $senderId, $message);
        return $stmt->execute();
    }

    public function archiveThread($threadId, $userId) {
        $stmt = $this->db->prepare("
            UPDATE threads 
            SET archived = 1 
            WHERE id = ? AND (sender_id = ? OR recipient_id = ?)
        ");
        $stmt->bind_param("iii", $threadId, $userId, $userId);
        return $stmt->execute();
    }
}
