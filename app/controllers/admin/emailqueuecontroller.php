<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class EmailQueueController
{
    public function index(Request $request): void
    {
        $pdo = db();
        $status = $request->get('status', '');
        $page = max(1, (int)$request->get('page', 1));
        $perPage = 30;
        $offset = ($page - 1) * $perPage;

        // Build query
        $where = '';
        $params = [];
        if ($status && in_array($status, ['pending', 'sending', 'sent', 'failed'])) {
            $where = 'WHERE status = ?';
            $params[] = $status;
        }

        // Get counts
        $counts = [];
        $stmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM email_queue GROUP BY status");
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $counts[$row['status']] = (int)$row['cnt'];
        }
        $counts['all'] = array_sum($counts);

        // Get total for pagination
        $countSql = "SELECT COUNT(*) FROM email_queue {$where}";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        // Get emails
        $sql = "SELECT * FROM email_queue {$where} ORDER BY priority ASC, created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $emails = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        render('admin/email-queue/index', [
            'emails' => $emails,
            'counts' => $counts,
            'currentStatus' => $status,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage),
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function view(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM email_queue WHERE id = ?");
        $stmt->execute([$id]);
        $email = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$email) {
            Session::flash('error', 'Email not found.');
            Response::redirect('/admin/email-queue');
        }

        render('admin/email-queue/view', [
            'email' => $email
        ]);
    }

    public function retry(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        $stmt = $pdo->prepare("UPDATE email_queue SET status = 'pending', attempts = 0, last_error = NULL WHERE id = ? AND status = 'failed'");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            Session::flash('success', 'Email queued for retry.');
        } else {
            Session::flash('error', 'Could not retry email. Only failed emails can be retried.');
        }

        Response::redirect('/admin/email-queue');
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        $stmt = $pdo->prepare("DELETE FROM email_queue WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'Email deleted from queue.');
        Response::redirect('/admin/email-queue');
    }

    public function bulkAction(Request $request): void
    {
        $action = $request->post('action', '');
        $ids = $request->post('ids', []);

        if (empty($ids) || !is_array($ids)) {
            Session::flash('error', 'No emails selected.');
            Response::redirect('/admin/email-queue');
        }

        $pdo = db();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $ids = array_map('intval', $ids);

        switch ($action) {
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM email_queue WHERE id IN ({$placeholders})");
                $stmt->execute($ids);
                Session::flash('success', "Deleted {$stmt->rowCount()} email(s).");
                break;

            case 'retry':
                $stmt = $pdo->prepare("UPDATE email_queue SET status = 'pending', attempts = 0, last_error = NULL WHERE id IN ({$placeholders}) AND status = 'failed'");
                $stmt->execute($ids);
                Session::flash('success', "Queued {$stmt->rowCount()} email(s) for retry.");
                break;

            default:
                Session::flash('error', 'Invalid action.');
        }

        Response::redirect('/admin/email-queue');
    }

    public function clearOld(Request $request): void
    {
        $days = (int)$request->post('days', 30);
        $status = $request->post('clear_status', 'sent');

        $pdo = db();

        if ($status === 'all') {
            $stmt = $pdo->prepare("DELETE FROM email_queue WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
            $stmt->execute([$days]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM email_queue WHERE status = ? AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
            $stmt->execute([$status, $days]);
        }

        Session::flash('success', "Deleted {$stmt->rowCount()} old email(s).");
        Response::redirect('/admin/email-queue');
    }

    public function compose(Request $request): void
    {
        render('admin/email-queue/compose', [
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function send(Request $request): void
    {
        $toEmail = trim($request->post('to_email', ''));
        $toName = trim($request->post('to_name', ''));
        $subject = trim($request->post('subject', ''));
        $bodyHtml = $request->post('body_html', '');
        $priority = max(1, min(10, (int)$request->post('priority', 5)));

        if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Valid email address is required.');
            Response::redirect('/admin/email-queue/compose');
        }

        if (empty($subject)) {
            Session::flash('error', 'Subject is required.');
            Response::redirect('/admin/email-queue/compose');
        }

        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO email_queue (to_email, to_name, subject, body_html, body_text, priority, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->execute([
            $toEmail,
            $toName ?: null,
            $subject,
            $bodyHtml,
            strip_tags($bodyHtml),
            $priority
        ]);

        Session::flash('success', 'Email added to queue.');
        Response::redirect('/admin/email-queue');
    }
}
