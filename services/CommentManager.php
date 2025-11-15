<?php

class CommentManager {
    private static $instance;
    private $db;

    private function __construct() {
        $this->db = \core\Database::connection();
    }

    public static function getInstance(): CommentManager {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function deleteComment(int $commentId): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM comments WHERE id = ?");
            return $stmt->execute([$commentId]);
        } catch (PDOException $e) {
            error_log("Comment deletion failed: " . $e->getMessage());
            return false;
        }
    }
}
