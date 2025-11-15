<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

declare(strict_types=1);
error_reporting(E_ALL);

/**
 * Widget Settings Controller
 */
class WidgetSettingsController {
    private PDO $db;

    public function __construct() {
        $this->db = \core\Database::connection();
    }

    public function create(): void {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $tenantId = $this->getTenantId();
            $userId = $this->getUserId();

            if (empty($data['type'])) {
                throw new InvalidArgumentException('Widget type is required');
            }

            $id = WidgetSettings::create($data, $tenantId);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'id' => $id
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Database error, please try again later.'
            ]);
        }
    }

    public function get(int $id): void {
        try {
            $tenantId = $this->getTenantId();
            $setting = WidgetSettings::getById($id, $tenantId);

            if (!$setting) {
                throw new RuntimeException('Widget setting not found');
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $setting
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Database error, please try again later.'
            ]);
        }
    }

    public function list(): void {
        try {
            $tenantId = $this->getTenantId();
            $settings = WidgetSettings::list($tenantId);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $settings
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Database error, please try again later.'
            ]);
        }
    }

    public function update(int $id): void {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $tenantId = $this->getTenantId();

            if (empty($data['type'])) {
                throw new InvalidArgumentException('Widget type is required');
            }

            $success = WidgetSettings::update($id, $data, $tenantId);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $success
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Database error, please try again later.'
            ]);
        }
    }

    public function delete(int $id): void {
        try {
            $tenantId = $this->getTenantId();
            $success = WidgetSettings::delete($id, $tenantId);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $success
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Database error, please try again later.'
            ]);
        }
    }

    public function preview(int $id): void {
        try {
            $tenantId = $this->getTenantId();
            $widget = WidgetSettings::getById($id, $tenantId);
            
            if (!$widget) {
                throw new RuntimeException('Widget not found');
            }

            // Get theme regions (implementation depends on your CMS)
            $regions = []; // TODO: Replace with actual region fetching logic
            
            ob_start();
            require_once __DIR__ . '/../views/widgets/preview.php';
            $previewHtml = ob_get_clean();

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'html' => $previewHtml
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Database error, please try again later.'
            ]);
        }
    }

    private function getTenantId(): int {
        return $_SESSION['tenant_id'] ?? 1;
    }

    private function getUserId(): int {
        return $_SESSION['user_id'] ?? 1;
    }
}
