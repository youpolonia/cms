<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Hooks Controller
 */
class HooksController {
    /**
     * List all hooks
     */
    public function list() {
        $hooks = Hook::getAll();
        require_once 'admin/views/hooks/list.php';
    }

    /**
     * Show create form
     */
    public function createForm() {
        require_once 'admin/views/hooks/create.php';
    }

    /**
     * Process hook creation
     */
    public function create() {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        
        if (empty($name)) {
            $_SESSION['error'] = 'Hook name is required';
            header('Location: /admin/hooks/create');
            exit;
        }

        $hook = new Hook();
        $hook->name = $name;
        $hook->description = $description;
        $hook->save();

        $_SESSION['success'] = 'Hook created successfully';
        header('Location: /admin/hooks');
    }

    /**
     * Show edit form
     */
    public function editForm($id) {
        $hook = Hook::find($id);
        if (!$hook) {
            header('Location: /admin/hooks');
            exit;
        }
        require_once 'admin/views/hooks/edit.php';
    }

    /**
     * Process hook update
     */
    public function update($id) {
        $hook = Hook::find($id);
        if (!$hook) {
            header('Location: /admin/hooks');
            exit;
        }

        $hook->name = $_POST['name'] ?? $hook->name;
        $hook->description = $_POST['description'] ?? $hook->description;
        $hook->save();

        $_SESSION['success'] = 'Hook updated successfully';
        header('Location: /admin/hooks');
    }

    /**
     * Delete a hook
     */
    public function delete($id) {
        $hook = Hook::find($id);
        if ($hook) {
            $hook->delete();
            $_SESSION['success'] = 'Hook deleted successfully';
        }
        header('Location: /admin/hooks');
    }
}
