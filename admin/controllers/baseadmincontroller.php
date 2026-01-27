<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../../core/csrf.php';

class BaseAdminController {
    public function requirePostMethod(): void {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            exit;
        }
    }
    
    public function verifyCsrfToken(): void {
        csrf_validate_or_403();
    }
}
