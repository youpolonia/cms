<?php
require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../models/personalizationmodel.php';

class PersonalizationController extends Controller {
    protected $model;

    public function __construct() {
        $this->model = new PersonalizationModel();
    }

    public function index() {
        $rules = $this->model->getAllRules();
        require_once __DIR__ . '/../../../templates/admin/personalization-rules.php';
    }

    public function saveRule() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $data = [
            'name' => $_POST['name'] ?? '',
            'conditions' => json_decode($_POST['conditions'] ?? '[]', true),
            'actions' => json_decode($_POST['actions'] ?? '[]', true),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        $id = $_POST['id'] ?? null;
        if ($id) {
            $this->model->updateRule($id, $data);
        } else {
            $id = $this->model->createRule($data);
        }

        echo json_encode(['success' => true, 'id' => $id]);
    }

    public function deleteRule() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $id = $_POST['id'] ?? null;
        if ($id) {
            $this->model->deleteRule($id);
        }

        echo json_encode(['success' => true]);
    }

    public function testRule() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $conditions = json_decode($_POST['conditions'] ?? '[]', true);
        $testData = json_decode($_POST['test_data'] ?? '[]', true);

        $engine = new PersonalizationEngine();
        $result = $engine->testConditions($conditions, $testData);

        echo json_encode(['matches' => $result]);
    }
}
