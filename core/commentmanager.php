<?php
require_once __DIR__ . '/database.php';

/**
 * Comment Manager - Handles comment submission, approval, and display
 */
class CommentManager {
    /**
     * Submit a new comment
     * @param array $data Comment data (post_id, author_name, author_email, content, parent_id)
     * @return int|false Inserted comment ID or false on failure
     */
    public static function submitComment(array $data): int|false {
        // Validate required fields
        $required = ['post_id', 'author_name', 'author_email', 'content'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException("Missing required field: $field");
            }
        }

        // Validate email format
        if (!filter_var($data['author_email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format");
        }

        // Sanitize input
        $comment = [
            'post_id' => (int)$data['post_id'],
            'author_name' => htmlspecialchars($data['author_name'], ENT_QUOTES),
            'author_email' => filter_var($data['author_email'], FILTER_SANITIZE_EMAIL),
            'content' => htmlspecialchars($data['content'], ENT_QUOTES),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Handle parent comment if provided
        if (!empty($data['parent_id'])) {
            $comment['parent_id'] = (int)$data['parent_id'];
        }

        // Insert into database
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, author_name, author_email, content, status, created_at, parent_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $comment['post_id'],
            $comment['author_name'],
            $comment['author_email'],
            $comment['content'],
            $comment['status'],
            $comment['created_at'],
            $comment['parent_id'] ?? null
        ]);
        return $pdo->lastInsertId();
    }

    /**
     * Approve a comment
     * @param int $id Comment ID
     * @return bool True on success
     */
    public static function approveComment(int $id): bool {
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("UPDATE comments SET status = 'approved' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Delete a comment
     * @param int $id Comment ID
     * @return bool True on success
     */
    public static function deleteComment(int $id): bool {
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * List comments for a post
     * @param int $post_id Post ID
     * @param string $status Filter by status (optional)
     * @return array Array of comments
     */
    public static function listComments(int $post_id, string $status = ''): array {
        $pdo = \core\Database::connection();
        
        if ($status) {
            $stmt = $pdo->prepare("SELECT * FROM comments WHERE post_id = ? AND status = ? ORDER BY created_at DESC");
            $stmt->execute([$post_id, $status]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at DESC");
            $stmt->execute([$post_id]);
        }
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Render comments for a post
     * @param int $post_id Post ID
     * @return string HTML of rendered comments
     */
    public static function renderComments(int $post_id): string {
        $comments = self::listComments($post_id, 'approved');
        if (empty($comments)) {
            return '';
        }

        $output = '<div class="comments-section">';
        $output .= '<h3>Comments</h3>';
        
        // Group comments by parent_id
        $grouped = [];
        foreach ($comments as $comment) {
            $parent_id = $comment['parent_id'] ?? 0;
            if (!isset($grouped[$parent_id])) {
                $grouped[$parent_id] = [];
            }
            $grouped[$parent_id][] = $comment;
        }

        // Recursive function to render comments tree
        $renderTree = function($parent_id = 0) use (&$renderTree, $grouped) {
            if (empty($grouped[$parent_id])) {
                return '';
            }

            $html = '<ul>';
            foreach ($grouped[$parent_id] as $comment) {
                $html .= '<li>';
                $html .= '<div class="comment">';
                $html .= '<div class="comment-author">' . htmlspecialchars($comment['author_name']) . '</div>';
                $html .= '<div class="comment-date">' . date('M j, Y', strtotime($comment['created_at'])) . '</div>';
                $html .= '<div class="comment-content">' . nl2br(htmlspecialchars($comment['content'])) . '</div>';
                $html .= '</div>';
                $html .= $renderTree($comment['id']); // Render child comments
                $html .= '</li>';
            }
            $html .= '</ul>';

            return $html;
        };

        $output .= $renderTree();
        $output .= '</div>';

        return $output;
    }

    /**
     * Generate CSRF token for comment forms
     * @return string CSRF token
     */
    public static function generateCsrfToken(): string {
        if (empty($_SESSION['comment_csrf_token'])) {
            $_SESSION['comment_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['comment_csrf_token'];
    }

    /**
     * Validate CSRF token
     * @param string $token Token to validate
     * @return bool True if valid
     */
    public static function validateCsrfToken(string $token): bool {
        return isset($_SESSION['comment_csrf_token']) && 
               hash_equals($_SESSION['comment_csrf_token'], $token);
    }
}