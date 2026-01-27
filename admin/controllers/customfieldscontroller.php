<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../../core/csrf.php';

/**
 * Custom Fields Admin Controller
 */
class CustomFieldsController {
    private $db;
    private $security;

    public function __construct() {
        require_once __DIR__ . '/../../database/Connection.php';
        require_once __DIR__ . '/../../includes/security.php';
        $this->db = new Database\Connection();
        $this->security = new Security();
    }

    /**
     * List all custom fields
     */
    public function index() {
        $this->security->verifyAdminAccess();
        
        $fields = $this->db->query("SELECT * FROM custom_fields");
        require_once __DIR__ . '/../views/custom-fields/list.php';
    }

    /**
     * Show form to create new custom field
     */
    public function create() {
        $this->security->verifyAdminAccess();
        require_once __DIR__ . '/../views/custom-fields/create.php';
    }

    /**
     * Store new custom field
     */
    public function store() {
        csrf_validate_or_403();
        $this->security->verifyAdminAccess();
        $this->security->verifyCsrfToken();

        $data = [
            'name' => $this->security->sanitize($_POST['name']),
            'label' => $this->security->sanitize($_POST['label']),
            'type' => $this->security->sanitize($_POST['type']),
            'options' => json_encode($_POST['options'] ?? [])
        ];

        $this->db->insert('custom_fields', $data);
        header('Location: /admin/custom-fields');
    }

    /**
     * Show form to assign fields to content types
     */
    public function assign() {
        $this->security->verifyAdminAccess();
        
        $fields = $this->db->query("SELECT id, name FROM custom_fields");
        $contentTypes = $this->db->query("SELECT id, name FROM content_types");
        
        require_once __DIR__ . '/../views/custom-fields/assign.php';
    }

    /**
     * Save field assignments
     */
    public function saveAssignments() {
        $this->security->verifyAdminAccess();
        $this->security->verifyCsrfToken();

        $contentTypeId = (int)$_POST['content_type_id'];
        $fieldIds = array_map('intval', $_POST['field_ids'] ?? []);

        // Delete existing assignments
        $this->db->delete('content_type_fields', ['content_type_id' => $contentTypeId]);

        // Add new assignments
        foreach ($fieldIds as $fieldId) {
            $this->db->insert('content_type_fields', [
                'content_type_id' => $contentTypeId,
                'field_id' => $fieldId
            ]);
        }

        header('Location: /admin/custom-fields/assign');
    }
}
