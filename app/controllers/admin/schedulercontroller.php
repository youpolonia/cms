<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use Core\Request;
use Core\Response;
use Core\Session;

class SchedulerController
{
    public function index(Request $request): void
    {
        $pdo = db();
        $tableExists = $this->checkTableExists($pdo);
        
        if (!$tableExists) {
            render('admin/scheduler/index', [
                'tableExists' => false, 'jobs' => [],
                'stats' => ['total' => 0, 'active' => 0, 'disabled' => 0, 'failed' => 0],
                'success' => Session::getFlash('success'), 'error' => Session::getFlash('error')
            ]);
            return;
        }
        
        $search = trim($request->get('q', ''));
        $status = $request->get('status', 'all');
        $page = max(1, (int)$request->get('page', 1));
        $perPage = 20;
        $where = []; $params = [];
        
        if ($search) { $where[] = '(name LIKE :search OR job_type LIKE :search)'; $params[':search'] = "%{$search}%"; }
        if ($status !== 'all') { $where[] = 'status = :status'; $params[':status'] = $status; }
        
        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM scheduler_jobs {$whereSQL}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        
        $offset = ($page - 1) * $perPage;
        $stmt = $pdo->prepare("SELECT * FROM scheduler_jobs {$whereSQL} ORDER BY next_run_at IS NULL, next_run_at ASC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $jobs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        render('admin/scheduler/index', [
            'tableExists' => true, 'jobs' => $jobs, 'stats' => $this->getStats($pdo),
            'total' => $total, 'page' => $page, 'perPage' => $perPage,
            'totalPages' => (int)ceil($total / $perPage) ?: 1,
            'search' => $search, 'statusFilter' => $status,
            'success' => Session::getFlash('success'), 'error' => Session::getFlash('error')
        ]);
    }
    
    public function create(Request $request): void { render('admin/scheduler/form', ['job' => null, 'isEdit' => false]); }
    
    public function store(Request $request): void
    {
        $pdo = db();
        $name = trim($request->post('name', '')); $jobType = trim($request->post('job_type', 'cron'));
        $schedule = trim($request->post('schedule_expression', '')); $status = $request->post('status', 'active');
        if (!$name || !$schedule) { Session::setFlash('error', 'Name and schedule required'); Response::redirect('/admin/scheduler/create'); return; }
        $stmt = $pdo->prepare("INSERT INTO scheduler_jobs (name, job_type, schedule_expression, status, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$name, $jobType, $schedule, $status]);
        Session::setFlash('success', 'Job created'); Response::redirect('/admin/scheduler');
    }
    
    public function edit(Request $request, int $id): void
    {
        $pdo = db(); $stmt = $pdo->prepare("SELECT * FROM scheduler_jobs WHERE id = ?"); $stmt->execute([$id]);
        $job = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$job) { Session::setFlash('error', 'Job not found'); Response::redirect('/admin/scheduler'); return; }
        render('admin/scheduler/form', ['job' => $job, 'isEdit' => true]);
    }
    
    public function update(Request $request, int $id): void
    {
        $pdo = db();
        $name = trim($request->post('name', '')); $jobType = trim($request->post('job_type', 'cron'));
        $schedule = trim($request->post('schedule_expression', '')); $status = $request->post('status', 'active');
        if (!$name || !$schedule) { Session::setFlash('error', 'Name and schedule required'); Response::redirect("/admin/scheduler/{$id}/edit"); return; }
        $stmt = $pdo->prepare("UPDATE scheduler_jobs SET name=?, job_type=?, schedule_expression=?, status=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$name, $jobType, $schedule, $status, $id]);
        Session::setFlash('success', 'Job updated'); Response::redirect('/admin/scheduler');
    }
    
    public function toggle(Request $request, int $id): void
    {
        $pdo = db(); $stmt = $pdo->prepare("SELECT status FROM scheduler_jobs WHERE id = ?"); $stmt->execute([$id]);
        $job = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$job) { Session::setFlash('error', 'Job not found'); Response::redirect('/admin/scheduler'); return; }
        $newStatus = $job['status'] === 'active' ? 'disabled' : 'active';
        $stmt = $pdo->prepare("UPDATE scheduler_jobs SET status=?, updated_at=NOW() WHERE id=?"); $stmt->execute([$newStatus, $id]);
        Session::setFlash('success', "Job {$newStatus}"); Response::redirect('/admin/scheduler');
    }
    
    public function run(Request $request, int $id): void
    {
        $pdo = db(); $stmt = $pdo->prepare("SELECT * FROM scheduler_jobs WHERE id = ?"); $stmt->execute([$id]);
        if (!$stmt->fetch(\PDO::FETCH_ASSOC)) { Session::setFlash('error', 'Job not found'); Response::redirect('/admin/scheduler'); return; }
        $result = 'Executed manually at ' . date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("UPDATE scheduler_jobs SET last_run_at=NOW(), last_result=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$result, $id]);
        Session::setFlash('success', 'Job executed'); Response::redirect('/admin/scheduler');
    }
    
    public function destroy(Request $request, int $id): void
    {
        $pdo = db(); $stmt = $pdo->prepare("DELETE FROM scheduler_jobs WHERE id = ?"); $stmt->execute([$id]);
        Session::setFlash('success', 'Job deleted'); Response::redirect('/admin/scheduler');
    }
    
    private function checkTableExists(\PDO $pdo): bool { try { $pdo->query('SELECT 1 FROM scheduler_jobs LIMIT 1'); return true; } catch (\Exception $e) { return false; } }
    
    private function getStats(\PDO $pdo): array
    {
        $stats = ['total' => 0, 'active' => 0, 'disabled' => 0, 'failed' => 0];
        try { $stmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM scheduler_jobs GROUP BY status");
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) { $stats[$row['status']] = (int)$row['cnt']; $stats['total'] += (int)$row['cnt']; }
        } catch (\Exception $e) {} return $stats;
    }
}
