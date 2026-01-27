<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

class ContentArchivalController {
    private $db;
    private $csrfToken;
    
    public function __construct($db) {
        $this->db = $db;
        $this->csrfToken = bin2hex(random_bytes(32));
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = $this->csrfToken;
        }
    }

    // List archived content
    public function index() {
        $this->checkAdminAccess();
        $archives = $this->db->query("SELECT * FROM content_archives ORDER BY archived_at DESC")->fetchAll();
        require_once __DIR__ . '/../views/content_archives/index.php';
    }

    // Show archive form
    public function create() {
        $this->checkAdminAccess();
        require_once __DIR__ . '/../views/content_archives/create.php';
    }

    // Archive content
    public function store() {
        $this->checkAdminAccess();
        $this->validateCsrf();
        
        $contentId = filter_input(INPUT_POST, 'content_id', FILTER_VALIDATE_INT);
        $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING);
        
        $stmt = $this->db->prepare("INSERT INTO content_archives (content_id, reason) VALUES (?, ?)");
        $stmt->execute([$contentId, $reason]);
        
        header("Location: ?action=index");
    }

    // Restore archived content
    public function restore($id) {
        $this->checkAdminAccess();
        $this->validateCsrf();
        
        $this->db->beginTransaction();
        try {
            $archive = $this->db->query("SELECT * FROM content_archives WHERE id = ?", [$id])->fetch();
            // Restore logic here
            
            $this->db->commit();
            $_SESSION['success'] = "Content restored successfully";
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Failed to restore content";
        }
        
        header("Location: ?action=index");
    }

    private function checkAdminAccess() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /admin/login.php");
            exit;
        }
    }

    private function validateCsrf() {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "Invalid CSRF token";
            header("Location: /admin");
            exit;
        }
    }
}
