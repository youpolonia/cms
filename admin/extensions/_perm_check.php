<?php
require_once __DIR__ . '/../../../config.php';

// RBAC: Require admin access
require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
http_response_code(403);
header('Content-Type: application/json');
echo json_encode(['error' => 'Disabled: _perm_check.php is not permitted in production'], JSON_PRETTY_PRINT);
