<?php
require_once __DIR__ . '/../../../includes/widgetmanager.php';
require_once __DIR__ . '/../../controllers/baseadmincontroller.php';
require_once __DIR__ . '/../../../core/csrf.php';

csrf_boot();

class WidgetToggleController extends BaseAdminController {
    public function handleRequest() {
        header('Content-Type: application/json');

        try {
            $this->verifyCsrfToken();
            $this->requirePostMethod();

            $widgetId = $_POST['widget_id'] ?? null;
            if (!$widgetId) {
                throw new Exception('Widget ID is required');
            }

            $widgetManager = new WidgetManager();
            $result = $widgetManager->toggleWidgetStatus($widgetId);

            if ($result === false) {
                throw new Exception('Widget not found or toggle failed');
            }

            $response = [
                'success' => true,
                'message' => 'Widget status updated',
                'widget_id' => $widgetId,
                'new_status' => $result['status']
            ];

            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(500);
            error_log($e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Widget operation failed'
            ]);
        }
    }
}

(new WidgetToggleController())->handleRequest();
