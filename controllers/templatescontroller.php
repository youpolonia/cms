<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Templates Controller
 * Handles notification template management
 */
class TemplatesController {
    private $templateModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->templateModel = new NotificationTemplate($db);
    }

    /**
     * List all templates
     */
    public function index() {
        try {
            $templates = $this->templateModel->getAll();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $templates]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get single template
     */
    public function show($template_id) {
        try {
            $template = $this->templateModel->getById($template_id);
            if (!$template) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Template not found']);
                return;
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $template]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Create new template
     */
    public function store() {
        require_once __DIR__ . '/../core/csrf.php';
        csrf_validate_or_403();

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Basic validation
            if (empty($data['name']) || empty($data['type']) || 
                empty($data['subject_template']) || empty($data['body_template'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                return;
            }

            $result = $this->templateModel->create($data);
            if ($result) {
                http_response_code(201);
                echo json_encode(['success' => true, 'message' => 'Template created']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create template']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Update template
     */
    public function update($template_id) {
        require_once __DIR__ . '/../core/csrf.php';
        csrf_validate_or_403();

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Basic validation
            if (empty($data['name']) || empty($data['type']) || 
                empty($data['subject_template']) || empty($data['body_template'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                return;
            }

            $result = $this->templateModel->update($template_id, $data);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Template updated']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update template']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Delete template
     */
    public function destroy($template_id) {
        try {
            $result = $this->templateModel->delete($template_id);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Template deleted']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete template']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
