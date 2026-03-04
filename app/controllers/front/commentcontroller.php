<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Request;
use Core\Response;
use Core\Session;

/**
 * Frontend Comment Submission
 * Handles user comments on articles and pages
 */
class CommentController
{
    private const MAX_COMMENTS_PER_IP = 10;
    private const RATE_WINDOW_MINUTES = 60;

    /**
     * Submit a comment
     * POST /comment
     */
    public function store(): void
    {
        $request = new Request();
        $articleId = (int)$request->post('article_id', '0');
        $pageId = (int)$request->post('page_id', '0');
        $parentId = (int)$request->post('parent_id', '0');
        $name = trim($request->post('author_name', ''));
        $email = trim($request->post('author_email', ''));
        $content = trim($request->post('content', ''));
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $redirect = $request->post('redirect', '/');

        // If user is logged in, use their data
        if (Session::isUserLoggedIn()) {
            $name = Session::get('user_name', $name);
            $email = Session::get('user_email', $email);
        }

        // Validation
        if (empty($name) || empty($email) || empty($content)) {
            Session::flash('comment_error', 'Please fill in all required fields.');
            Response::redirect($redirect);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('comment_error', 'Please enter a valid email address.');
            Response::redirect($redirect);
            return;
        }

        if ($articleId === 0 && $pageId === 0) {
            Session::flash('comment_error', 'Invalid target for comment.');
            Response::redirect($redirect);
            return;
        }

        if (mb_strlen($content) > 5000) {
            Session::flash('comment_error', 'Comment is too long (max 5000 characters).');
            Response::redirect($redirect);
            return;
        }

        // Honeypot
        $honeypot = trim($request->post('website_url', ''));
        if (!empty($honeypot)) {
            Session::flash('comment_success', 'Thank you for your comment! It will appear after moderation.');
            Response::redirect($redirect);
            return;
        }

        $pdo = db();

        // Rate limiting
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS rate_limits (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip VARCHAR(45) NOT NULL,
                action VARCHAR(32) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_ip_action (ip, action),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $pdo->exec("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)");

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM rate_limits WHERE ip = ? AND action = 'comment' AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)");
            $stmt->execute([$ip, self::RATE_WINDOW_MINUTES]);
            if ((int)$stmt->fetchColumn() >= self::MAX_COMMENTS_PER_IP) {
                Session::flash('comment_error', 'Too many comments. Please try again later.');
                Response::redirect($redirect);
                return;
            }
            $pdo->prepare("INSERT INTO rate_limits (ip, action, created_at) VALUES (?, 'comment', NOW())")->execute([$ip]);
        } catch (\Exception $e) {}

        // Auto-approve if admin setting allows, otherwise pending
        $autoApprove = false;
        try {
            $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'comments_auto_approve'");
            $stmt->execute();
            $autoApprove = $stmt->fetchColumn() === '1';
        } catch (\Exception $e) {}

        $status = $autoApprove ? 'approved' : 'pending';

        $stmt = $pdo->prepare("INSERT INTO comments (article_id, page_id, parent_id, author_name, author_email, content, status, ip_address, user_agent, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([
            $articleId ?: null,
            $pageId ?: null,
            $parentId ?: null,
            substr($name, 0, 100),
            substr($email, 0, 255),
            $content,
            $status,
            $ip,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ]);

        // Notify admin
        if (function_exists('cms_event')) {
            cms_event('comment.created', [
                'comment_id' => (int)$pdo->lastInsertId(),
                'author_name' => $name,
                'author_email' => $email,
                'article_id' => $articleId,
                'status' => $status,
            ]);
        }

        if ($status === 'approved') {
            Session::flash('comment_success', 'Your comment has been posted!');
        } else {
            Session::flash('comment_success', 'Thank you! Your comment will appear after moderation.');
        }
        Response::redirect($redirect . '#comments');
    }

    /**
     * Get approved comments for an article (JSON for AJAX or direct use)
     * GET /api/comments?article_id=X
     */
    public function list(): void
    {
        $articleId = (int)($_GET['article_id'] ?? 0);
        $pageId = (int)($_GET['page_id'] ?? 0);
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $pdo = db();

        $where = "status = 'approved'";
        $params = [];
        if ($articleId > 0) {
            $where .= " AND article_id = ?";
            $params[] = $articleId;
        } elseif ($pageId > 0) {
            $where .= " AND page_id = ?";
            $params[] = $pageId;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['comments' => [], 'total' => 0]);
            return;
        }

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE {$where}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT id, author_name, content, parent_id, created_at FROM comments WHERE {$where} ORDER BY created_at ASC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $comments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Generate gravatar URLs
        foreach ($comments as &$c) {
            $c['created_at_human'] = date('M j, Y g:i A', strtotime($c['created_at']));
        }

        header('Content-Type: application/json');
        echo json_encode(['comments' => $comments, 'total' => $total, 'page' => $page, 'pages' => ceil($total / $perPage)]);
    }
}
