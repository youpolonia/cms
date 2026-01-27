<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../includes/rate_limiter.php';
require_once __DIR__ . '/../../core/csrf.php';

class TasksController {
    const TASKS_PER_PAGE = 20;

    private function verifyAdmin() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('HTTP/1.0 403 Forbidden');
            exit('Access denied');
        }
    }

    private function verifyCsrfToken() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                header('HTTP/1.0 403 Forbidden');
                exit('Invalid CSRF token');
            }
        }
    }

    public function index() {
        $this->verifyAdmin();
        
        $page = $_GET['page'] ?? 1;
        $offset = ($page - 1) * self::TASKS_PER_PAGE;

        $totalTasks = DB::queryFirstField("SELECT COUNT(*) FROM scheduled_tasks");
        $totalPages = ceil($totalTasks / self::TASKS_PER_PAGE);

        $tasks = DB::query(
            "SELECT id, name, description, is_active, last_run, next_run 
             FROM scheduled_tasks 
             LIMIT %d OFFSET %d",
            self::TASKS_PER_PAGE,
            $offset
        );

        require_once __DIR__ . '/../views/tasks/index.php';
    }

    public function save($taskId = null) {
        $this->verifyAdmin();
        $this->verifyCsrfToken();

        if (!RateLimiter::check('task_save')) {
            http_response_code(429);
            die('Too many requests. Please try again later.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_validate_or_403();
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'command' => $_POST['command'],
                'schedule' => $_POST['schedule'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            try {
                if ($taskId) {
                    DB::update('scheduled_tasks', $data, 'id = %d', $taskId);
                } else {
                    DB::insert('scheduled_tasks', $data);
                }
                header('Location: /admin/tasks');
                exit;
            } catch (Exception $e) {
                error_log("Task save failed: " . $e->getMessage());
                http_response_code(500);
                die('Failed to save task. Please try again.');
            }
        }

        if ($taskId) {
            $task = DB::queryFirstRow("SELECT * FROM scheduled_tasks WHERE id = %d", $taskId);
        }
        require_once __DIR__ . '/../views/tasks/edit.php';
    }

    public function toggle($taskId) {
        $this->verifyAdmin();
        $this->verifyCsrfToken();

        if (!RateLimiter::check('task_toggle')) {
            http_response_code(429);
            die('Too many requests. Please try again later.');
        }

        $current = DB::queryFirstField("SELECT is_active FROM scheduled_tasks WHERE id = %d", $taskId);
        DB::update('scheduled_tasks', ['is_active' => $current ? 0 : 1], 'id = %d', $taskId);

        header('Location: /admin/tasks');
        exit;
    }

    public function history($taskId) {
        $this->verifyAdmin();

        $page = $_GET['page'] ?? 1;
        $offset = ($page - 1) * self::TASKS_PER_PAGE;

        $totalHistory = DB::queryFirstField(
            "SELECT COUNT(*) FROM task_execution_history WHERE task_id = %d",
            $taskId
        );
        $totalPages = ceil($totalHistory / self::TASKS_PER_PAGE);

        $history = DB::query(
            "SELECT id, executed_at, status, output 
             FROM task_execution_history 
             WHERE task_id = %d
             ORDER BY executed_at DESC
             LIMIT %d OFFSET %d",
            $taskId,
            self::TASKS_PER_PAGE,
            $offset
        );

        require_once __DIR__ . '/../views/tasks/history.php';
    }
}
