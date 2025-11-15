<?php

class Notification {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($userId, $title, $message, $categoryId = null) {
        $stmt = $this->db->prepare("
            INSERT INTO notifications
            (user_id, category_id, title, message, view_count)
            VALUES (?, ?, ?, ?, 0)
        ");
        return $stmt->execute([$userId, $categoryId, $title, $message]);
    }

    public function markAsRead($notificationId) {
        try {
            $this->db->beginTransaction();
            
            // Update notification read status
            $stmt = $this->db->prepare("
                UPDATE notifications
                SET is_read = TRUE,
                    read_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $success = $stmt->execute([$notificationId]);
            
            if ($success) {
                // Create read receipt
                $stmt = $this->db->prepare("
                    INSERT INTO read_receipts
                    (notification_id, user_id, read_at)
                    VALUES (?, ?, CURRENT_TIMESTAMP)
                ");
                $success = $stmt->execute([
                    $notificationId,
                    $_SESSION['user_id']
                ]);
            }
            
            if ($success) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function markAsUnread($notificationId) {
        try {
            $this->db->beginTransaction();
            
            // Update notification read status
            $stmt = $this->db->prepare("
                UPDATE notifications
                SET is_read = FALSE,
                    read_at = NULL
                WHERE id = ?
            ");
            $success = $stmt->execute([$notificationId]);
            
            if ($success) {
                // Delete read receipt
                $stmt = $this->db->prepare("
                    DELETE FROM read_receipts
                    WHERE notification_id = ?
                    AND user_id = ?
                ");
                $success = $stmt->execute([
                    $notificationId,
                    $_SESSION['user_id']
                ]);
            }
            
            if ($success) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function incrementViewCount($notificationId) {
        $stmt = $this->db->prepare("
            UPDATE notifications
            SET view_count = view_count + 1,
                last_viewed_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        return $stmt->execute([$notificationId]);
    }

    public function getForUser($userId, $filters = []) {
        $query = "SELECT * FROM notifications WHERE user_id = ?";
        $params = [$userId];

        if (!empty($filters['category_id'])) {
            $query .= " AND category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['unread_only'])) {
            $query .= " AND is_read = FALSE";
        }

        if (!empty($filters['type'])) {
            $query .= " AND type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (title LIKE ? OR message LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $orderBy = 'created_at DESC';
        if (!empty($filters['sort'])) {
            $validSorts = ['created_at', 'read_at', 'view_count'];
            $direction = strtoupper($filters['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
            if (in_array($filters['sort'], $validSorts)) {
                $orderBy = "{$filters['sort']} $direction";
            }
        }
        $query .= " ORDER BY $orderBy";

        if (!empty($filters['limit'])) {
            $query .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function bulkMarkAsRead($notificationIds) {
        try {
            $this->db->beginTransaction();
            
            // Update notifications read status
            $placeholders = implode(',', array_fill(0, count($notificationIds), '?'));
            $stmt = $this->db->prepare("
                UPDATE notifications
                SET is_read = TRUE,
                    read_at = CURRENT_TIMESTAMP
                WHERE id IN ($placeholders)
            ");
            $success = $stmt->execute($notificationIds);
            
            if ($success) {
                // Create read receipts for each notification
                $userId = $_SESSION['user_id'];
                $stmt = $this->db->prepare("
                    INSERT INTO read_receipts
                    (notification_id, user_id, read_at)
                    VALUES (?, ?, CURRENT_TIMESTAMP)
                ");
                
                foreach ($notificationIds as $id) {
                    $success = $stmt->execute([$id, $userId]);
                    if (!$success) break;
                }
            }
            
            if ($success) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getStats($userId) {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total,
                SUM(is_read) as read_count,
                SUM(view_count) as total_views,
                MAX(created_at) as last_notification
            FROM notifications
            WHERE user_id = ?
            AND deleted_at IS NULL
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function softDelete($notificationId) {
        try {
            $this->db->beginTransaction();
            
            // Update notification deleted status
            $stmt = $this->db->prepare("
                UPDATE notifications
                SET deleted_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $success = $stmt->execute([$notificationId]);
            
            if ($success) {
                // Delete associated read receipts
                $stmt = $this->db->prepare("
                    DELETE FROM read_receipts
                    WHERE notification_id = ?
                ");
                $success = $stmt->execute([$notificationId]);
            }
            
            if ($success) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getNewSince($timestamp, $userId = null) {
        $query = "SELECT * FROM notifications WHERE created_at > ?";
        $params = [$timestamp];

        if ($userId !== null) {
            $query .= " AND user_id = ?";
            $params[] = $userId;
        }

        $query .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
