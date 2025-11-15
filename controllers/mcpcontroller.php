<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

namespace Controllers;

class MCPController {
    public function handleRequest() {
        http_response_code(200);
        echo json_encode(['status' => 'success']);
    }
}
