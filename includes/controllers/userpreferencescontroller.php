<?php

namespace Includes\Controllers;

use Core\ResponseHandler;

class UserPreferencesController {
    private $model;

    /**
     * Initialize controller and model
     */
    public function __construct() {
        require_once __DIR__ . '/../models/userpreferencesmodel.php';
        $this->model = new UserPreferencesModel();
    }

    /**
     * Show user preferences page
     * @return void
     */
    public function showPreferences(): void {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            ResponseHandler::error('Not authenticated', 401);
            return;
        }

        try {
            $preferences = $this->model->getAllPreferences($userId);
            require_once __DIR__.'/../../templates/user/preferences.php';
        } catch (\Exception $e) {
            error_log('Preferences error: ' . $e->getMessage());
            ResponseHandler::error('Failed to load preferences', 500);
        }
    }

    /**
     * Save user preferences via API
     * @return void
     */
    public function savePreferences(): void {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            ResponseHandler::error('Not authenticated', 401);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['key']) || !isset($input['value'])) {
                ResponseHandler::error('Invalid input', 400);
                return;
            }

            $success = $this->model->setPreference($userId, $input['key'], $input['value']);
            ResponseHandler::success(['saved' => $success]);
        } catch (\Exception $e) {
            error_log('Save preferences error: ' . $e->getMessage());
            ResponseHandler::error('Failed to save preferences', 500);
        }
    }
}
