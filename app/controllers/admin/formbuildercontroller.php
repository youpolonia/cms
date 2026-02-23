<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;

class FormBuilderController
{
    private function fb(): \Core\FormBuilder
    {
        require_once CMS_ROOT . '/core/form_builder.php';
        return new \Core\FormBuilder();
    }

    /**
     * List all forms
     * GET /admin/form-builder
     */
    public function index(): void
    {
        $forms = $this->fb()->listForms();
        $title = 'Form Builder';

        ob_start();
        require CMS_APP . '/views/admin/form-builder/index.php';
        $content = ob_get_clean();
        require CMS_APP . '/views/admin/layouts/topbar.php';
    }

    /**
     * Show builder for new form
     * GET /admin/form-builder/create
     */
    public function create(): void
    {
        $form = null;
        $title = 'Create Form';

        ob_start();
        require CMS_APP . '/views/admin/form-builder/editor.php';
        $content = ob_get_clean();
        require CMS_APP . '/views/admin/layouts/topbar.php';
    }

    /**
     * Show builder for existing form
     * GET /admin/form-builder/edit/{id}
     */
    public function edit(Request $request): void
    {
        $id = (int)$request->param('id', '0');
        $form = $this->fb()->getForm($id);

        if (!$form) {
            $_SESSION['flash_error'] = 'Form not found.';
            Response::redirect('/admin/form-builder');
        }

        $title = 'Edit Form: ' . ($form['name'] ?? '');

        ob_start();
        require CMS_APP . '/views/admin/form-builder/editor.php';
        $content = ob_get_clean();
        require CMS_APP . '/views/admin/layouts/topbar.php';
    }

    /**
     * AJAX save new form
     * POST /admin/form-builder/store
     */
    public function store(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $fields = $input['fields'] ?? [];
        if (is_string($fields)) {
            $fields = json_decode($fields, true) ?: [];
        }

        $data = [
            'name'            => trim($input['name'] ?? 'Untitled Form'),
            'fields'          => $fields,
            'settings'        => $input['settings'] ?? null,
            'success_message' => trim($input['success_message'] ?? 'Thank you! Your form has been submitted.'),
            'redirect_url'    => trim($input['redirect_url'] ?? ''),
            'email_to'        => trim($input['email_to'] ?? ''),
            'active'          => (int)($input['active'] ?? 1),
        ];

        if (empty($data['name'])) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Form name is required.']);
            return;
        }

        $id = $this->fb()->createForm($data);
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Form created successfully.']);
    }

    /**
     * AJAX update form
     * POST /admin/form-builder/update/{id}
     */
    public function update(Request $request): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = (int)$request->param('id', '0');
        $fb = $this->fb();
        $form = $fb->getForm($id);

        if (!$form) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Form not found.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $fields = $input['fields'] ?? null;
        if (is_string($fields)) {
            $fields = json_decode($fields, true);
        }

        $data = [];
        if (isset($input['name'])) $data['name'] = trim($input['name']);
        if ($fields !== null) $data['fields'] = $fields;
        if (isset($input['settings'])) $data['settings'] = $input['settings'];
        if (isset($input['success_message'])) $data['success_message'] = trim($input['success_message']);
        if (isset($input['redirect_url'])) $data['redirect_url'] = trim($input['redirect_url']);
        if (isset($input['email_to'])) $data['email_to'] = trim($input['email_to']);
        if (isset($input['active'])) $data['active'] = (int)$input['active'];

        $fb->updateForm($id, $data);
        echo json_encode(['success' => true, 'message' => 'Form updated successfully.']);
    }

    /**
     * Delete a form
     * POST /admin/form-builder/delete/{id}
     */
    public function delete(Request $request): void
    {
        $id = (int)$request->param('id', '0');
        $this->fb()->deleteForm($id);

        $_SESSION['flash_success'] = 'Form deleted.';
        Response::redirect('/admin/form-builder');
    }

    /**
     * View submissions
     * GET /admin/form-builder/submissions/{id}
     */
    public function submissions(Request $request): void
    {
        $id = (int)$request->param('id', '0');
        $fb = $this->fb();
        $form = $fb->getForm($id);

        if (!$form) {
            $_SESSION['flash_error'] = 'Form not found.';
            Response::redirect('/admin/form-builder');
        }

        $page = max(1, (int)($request->get('page', '1')));
        $result = $fb->getSubmissions($id, $page);
        $submissions = $result['submissions'];
        $totalPages = $result['totalPages'];
        $total = $result['total'];
        $title = 'Submissions: ' . ($form['name'] ?? '');

        ob_start();
        require CMS_APP . '/views/admin/form-builder/submissions.php';
        $content = ob_get_clean();
        require CMS_APP . '/views/admin/layouts/topbar.php';
    }

    /**
     * Export CSV
     * GET /admin/form-builder/export/{id}
     */
    public function exportCsv(Request $request): void
    {
        $id = (int)$request->param('id', '0');
        $fb = $this->fb();
        $form = $fb->getForm($id);

        if (!$form) {
            http_response_code(404);
            echo 'Form not found';
            return;
        }

        $csv = $fb->exportCsv($id);
        $filename = preg_replace('/[^a-z0-9_-]/i', '_', $form['name'] ?? 'form') . '_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($csv));
        echo $csv;
        exit;
    }

    /**
     * AJAX mark submission as read
     * POST /admin/form-builder/mark-read/{id}
     */
    public function markRead(Request $request): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = (int)$request->param('id', '0');
        $this->fb()->markRead($id);
        echo json_encode(['success' => true]);
    }
}
