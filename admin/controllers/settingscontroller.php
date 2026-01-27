<?php
/**
 * Settings Controller
 * Handles CRUD operations for system settings
 */

require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/baseadmincontroller.php';
require_once __DIR__ . '/../models/settingsmodel.php';

class SettingsController extends BaseAdminController
{
    private SettingsModel $model;

    public function __construct()
    {
        $this->model = new SettingsModel();
    }

    /**
     * List all settings (grouped)
     * @return array View data
     */
    public function index(): array
    {
        $groupFilter = $_GET['group'] ?? null;
        $search = $_GET['search'] ?? null;

        if ($search) {
            $settings = $this->model->search($search);
            $grouped = [];
            foreach ($settings as $setting) {
                $group = $setting['group_name'] ?: 'general';
                if (!isset($grouped[$group])) {
                    $grouped[$group] = [];
                }
                $grouped[$group][] = $setting;
            }
        } elseif ($groupFilter) {
            $grouped = [$groupFilter => $this->model->getByGroup($groupFilter)];
        } else {
            $grouped = $this->model->getAllGrouped();
        }

        $groups = $this->model->getGroups();

        return [
            'settings' => $grouped,
            'groups' => $groups,
            'currentGroup' => $groupFilter,
            'search' => $search,
            'totalCount' => $this->model->count()
        ];
    }

    /**
     * Show single setting for editing
     * @param int $id Setting ID
     * @return array|null View data or null if not found
     */
    public function show(int $id): ?array
    {
        $setting = $this->model->getById($id);

        if (!$setting) {
            return null;
        }

        return [
            'setting' => $setting,
            'groups' => $this->model->getGroups()
        ];
    }

    /**
     * Create new setting
     * @return array Result with success/error info
     */
    public function create(): array
    {
        $this->requirePostMethod();
        $this->verifyCsrfToken();

        $data = [
            'key' => trim($_POST['key'] ?? ''),
            'value' => $_POST['value'] ?? '',
            'group_name' => trim($_POST['group_name'] ?? '') ?: null
        ];

        // Validate
        $errors = $this->model->validate($data);
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors,
                'data' => $data
            ];
        }

        // Check for duplicate key
        if ($this->model->getByKey($data['key'])) {
            return [
                'success' => false,
                'errors' => ['A setting with this key already exists'],
                'data' => $data
            ];
        }

        $id = $this->model->create($data['key'], $data['value'], $data['group_name']);

        if ($id) {
            return [
                'success' => true,
                'id' => $id,
                'message' => 'Setting created successfully'
            ];
        }

        return [
            'success' => false,
            'errors' => ['Failed to create setting'],
            'data' => $data
        ];
    }

    /**
     * Update existing setting
     * @param int $id Setting ID
     * @return array Result with success/error info
     */
    public function update(int $id): array
    {
        $this->requirePostMethod();
        $this->verifyCsrfToken();

        $existing = $this->model->getById($id);
        if (!$existing) {
            return [
                'success' => false,
                'errors' => ['Setting not found']
            ];
        }

        $data = [
            'key' => trim($_POST['key'] ?? ''),
            'value' => $_POST['value'] ?? '',
            'group_name' => trim($_POST['group_name'] ?? '') ?: null
        ];

        // Validate
        $errors = $this->model->validate($data);
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors,
                'data' => $data
            ];
        }

        // Check for duplicate key (if key changed)
        if ($data['key'] !== $existing['key'] && $this->model->getByKey($data['key'])) {
            return [
                'success' => false,
                'errors' => ['A setting with this key already exists'],
                'data' => $data
            ];
        }

        if ($this->model->update($id, $data)) {
            return [
                'success' => true,
                'message' => 'Setting updated successfully'
            ];
        }

        return [
            'success' => false,
            'errors' => ['Failed to update setting'],
            'data' => $data
        ];
    }

    /**
     * Delete setting
     * @param int $id Setting ID
     * @return array Result with success/error info
     */
    public function delete(int $id): array
    {
        $this->requirePostMethod();
        $this->verifyCsrfToken();

        $existing = $this->model->getById($id);
        if (!$existing) {
            return [
                'success' => false,
                'errors' => ['Setting not found']
            ];
        }

        if ($this->model->delete($id)) {
            return [
                'success' => true,
                'message' => 'Setting deleted successfully'
            ];
        }

        return [
            'success' => false,
            'errors' => ['Failed to delete setting']
        ];
    }

    /**
     * Bulk update settings (for quick edit form)
     * @return array Result with success/error info
     */
    public function bulkUpdate(): array
    {
        $this->requirePostMethod();
        $this->verifyCsrfToken();

        $settings = $_POST['settings'] ?? [];
        $updated = 0;
        $errors = [];

        foreach ($settings as $id => $value) {
            $id = (int) $id;
            $existing = $this->model->getById($id);

            if ($existing) {
                if ($this->model->update($id, ['value' => $value])) {
                    $updated++;
                } else {
                    $errors[] = "Failed to update setting: {$existing['key']}";
                }
            }
        }

        if (empty($errors)) {
            return [
                'success' => true,
                'message' => "Updated {$updated} setting(s) successfully"
            ];
        }

        return [
            'success' => false,
            'errors' => $errors,
            'updated' => $updated
        ];
    }

    /**
     * Get model instance (for direct access if needed)
     * @return SettingsModel
     */
    public function getModel(): SettingsModel
    {
        return $this->model;
    }
}
