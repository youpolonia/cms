<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

class PageController {
    protected $db;

    public function __construct() {
        $this->db = DBModel::getInstance();
    }

    public function index() {
        $pages = $this->db->query("SELECT * FROM pages ORDER BY created_at DESC");
        require_once 'admin/pages.php';
    }

    public function create() {
        require_once 'admin/pages/form.php';
    }

    public function store() {
        $data = [
            'title' => $this->sanitize($_POST['title']),
            'slug' => $this->sanitize($_POST['slug']),
            'content' => $this->sanitize($_POST['content'], false),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if ($this->validate($data)) {
            $this->db->insert('pages', $data);
            header('Location: /admin/pages.php');
            exit;
        }
    }

    public function edit($id) {
        $page = $this->db->queryFirst("SELECT * FROM pages WHERE id = ?", [$id]);
        require_once 'admin/pages/form.php';
    }

    public function update($id) {
        $data = [
            'title' => $this->sanitize($_POST['title']),
            'slug' => $this->sanitize($_POST['slug']),
            'content' => $this->sanitize($_POST['content'], false),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($this->validate($data)) {
            $this->db->update('pages', $data, ['id' => $id]);
            header('Location: /admin/pages.php');
            exit;
        }
    }

    public function delete($id) {
        $this->db->delete('pages', ['id' => $id]);
        header('Location: /admin/pages.php');
        exit;
    }

    protected function sanitize($input, $stripTags = true) {
        if ($stripTags) {
            $input = strip_tags($input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    protected function validate($data) {
        if (empty($data['title']) || empty($data['slug'])) {
            $_SESSION['error'] = 'Title and slug are required';
            return false;
        }
        return true;
    }
}
