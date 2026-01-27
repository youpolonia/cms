<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class CommentsController
{
    public function index(Request $request): void
    {
        $pdo = db();
        $status = $request->get('status', '');

        $where = '';
        $params = [];
        if ($status && in_array($status, ['pending', 'approved', 'spam', 'trash'])) {
            $where = 'WHERE c.status = ?';
            $params[] = $status;
        }

        $sql = "SELECT c.*, a.title as article_title, p.title as page_title
                FROM comments c
                LEFT JOIN articles a ON c.article_id = a.id
                LEFT JOIN pages p ON c.page_id = p.id
                {$where}
                ORDER BY c.created_at DESC
                LIMIT 100";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $comments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get counts
        $counts = ['all' => 0, 'pending' => 0, 'approved' => 0, 'spam' => 0, 'trash' => 0];
        $countStmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM comments GROUP BY status");
        foreach ($countStmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $counts[$row['status']] = (int)$row['cnt'];
            $counts['all'] += (int)$row['cnt'];
        }

        render('admin/comments/index', [
            'comments' => $comments,
            'status' => $status,
            'counts' => $counts,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function approve(Request $request): void
    {
        $id = (int)$request->param('id');
        $this->updateStatus($id, 'approved');
        Session::flash('success', 'Comment approved.');
        Response::redirect('/admin/comments');
    }

    public function spam(Request $request): void
    {
        $id = (int)$request->param('id');
        $this->updateStatus($id, 'spam');
        Session::flash('success', 'Comment marked as spam.');
        Response::redirect('/admin/comments');
    }

    public function trash(Request $request): void
    {
        $id = (int)$request->param('id');
        $this->updateStatus($id, 'trash');
        Session::flash('success', 'Comment moved to trash.');
        Response::redirect('/admin/comments');
    }

    public function restore(Request $request): void
    {
        $id = (int)$request->param('id');
        $this->updateStatus($id, 'pending');
        Session::flash('success', 'Comment restored to pending.');
        Response::redirect('/admin/comments');
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'Comment permanently deleted.');
        Response::redirect('/admin/comments?status=trash');
    }

    public function bulkAction(Request $request): void
    {
        $action = $request->post('bulk_action', '');
        $ids = $request->post('comment_ids', []);

        if (empty($ids) || !is_array($ids)) {
            Session::flash('error', 'No comments selected.');
            Response::redirect('/admin/comments');
        }

        $pdo = db();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        switch ($action) {
            case 'approve':
                $stmt = $pdo->prepare("UPDATE comments SET status = 'approved' WHERE id IN ({$placeholders})");
                $stmt->execute($ids);
                Session::flash('success', count($ids) . ' comments approved.');
                break;
            case 'spam':
                $stmt = $pdo->prepare("UPDATE comments SET status = 'spam' WHERE id IN ({$placeholders})");
                $stmt->execute($ids);
                Session::flash('success', count($ids) . ' comments marked as spam.');
                break;
            case 'trash':
                $stmt = $pdo->prepare("UPDATE comments SET status = 'trash' WHERE id IN ({$placeholders})");
                $stmt->execute($ids);
                Session::flash('success', count($ids) . ' comments moved to trash.');
                break;
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM comments WHERE id IN ({$placeholders})");
                $stmt->execute($ids);
                Session::flash('success', count($ids) . ' comments permanently deleted.');
                break;
            default:
                Session::flash('error', 'Invalid action.');
        }

        Response::redirect('/admin/comments');
    }

    private function updateStatus(int $id, string $status): void
    {
        $pdo = db();
        $stmt = $pdo->prepare("UPDATE comments SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }
}
