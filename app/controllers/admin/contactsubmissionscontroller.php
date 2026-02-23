<?php
declare(strict_types=1);

namespace Admin;

use Core\Request;

class ContactSubmissionsController
{
    public function index(Request $request): void
    {
        $pdo = db();
        $status = $_GET['status'] ?? '';
        $search = trim($_GET['q'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $offset  = ($page - 1) * $perPage;

        $where = [];
        $params = [];

        if ($status && in_array($status, ['new', 'read', 'replied', 'spam', 'archived'])) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        if ($search !== '') {
            $where[] = '(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)';
            $like = "%{$search}%";
            $params = array_merge($params, [$like, $like, $like, $like]);
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM contact_submissions {$whereClause}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));

        // Status counts
        $statusCounts = [];
        $sc = $pdo->query("SELECT status, COUNT(*) as cnt FROM contact_submissions GROUP BY status")->fetchAll(\PDO::FETCH_KEY_PAIR);
        $statusCounts = $sc;
        $newCount = (int)($statusCounts['new'] ?? 0);

        // Fetch
        $stmt = $pdo->prepare(
            "SELECT * FROM contact_submissions {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute($params);
        $submissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        render('admin/contact-submissions/index', [
            'submissions' => $submissions,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'status' => $status,
            'search' => $search,
            'statusCounts' => $statusCounts,
            'newCount' => $newCount,
        ]);
    }

    public function show(Request $request): void
    {
        $id = (int)$request->param('id');
        $pdo = db();

        $stmt = $pdo->prepare("SELECT * FROM contact_submissions WHERE id = ?");
        $stmt->execute([$id]);
        $submission = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$submission) {
            http_response_code(404);
            render('admin/404', []);
            return;
        }

        // Mark as read
        if ($submission['status'] === 'new') {
            $pdo->prepare("UPDATE contact_submissions SET status = 'read' WHERE id = ?")->execute([$id]);
            $submission['status'] = 'read';
        }

        render('admin/contact-submissions/show', [
            'submission' => $submission,
        ]);
    }

    public function updateStatus(Request $request): void
    {
        $id = (int)$request->param('id');
        $data = $GLOBALS['_JSON_DATA'] ?? $_POST;
        $newStatus = $data['status'] ?? '';

        if (!in_array($newStatus, ['new', 'read', 'replied', 'spam', 'archived'])) {
            http_response_code(422);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid status']);
            return;
        }

        $pdo = db();
        $stmt = $pdo->prepare("UPDATE contact_submissions SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);

        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Location: /admin/contact-submissions');
            exit;
        }
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->param('id');
        $pdo = db();
        $pdo->prepare("DELETE FROM contact_submissions WHERE id = ?")->execute([$id]);

        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Location: /admin/contact-submissions');
            exit;
        }
    }

    public function bulkAction(Request $request): void
    {
        $data = $GLOBALS['_JSON_DATA'] ?? $_POST;
        $ids = $data['ids'] ?? [];
        $action = $data['action'] ?? '';

        if (empty($ids) || !is_array($ids)) {
            header('Location: /admin/contact-submissions');
            exit;
        }

        $pdo = db();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $idValues = array_map('intval', $ids);

        switch ($action) {
            case 'mark_read':
                $pdo->prepare("UPDATE contact_submissions SET status = 'read' WHERE id IN ({$placeholders})")->execute($idValues);
                break;
            case 'mark_spam':
                $pdo->prepare("UPDATE contact_submissions SET status = 'spam' WHERE id IN ({$placeholders})")->execute($idValues);
                break;
            case 'archive':
                $pdo->prepare("UPDATE contact_submissions SET status = 'archived' WHERE id IN ({$placeholders})")->execute($idValues);
                break;
            case 'delete':
                $pdo->prepare("DELETE FROM contact_submissions WHERE id IN ({$placeholders})")->execute($idValues);
                break;
        }

        header('Location: /admin/contact-submissions');
        exit;
    }
}
