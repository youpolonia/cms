<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
// Start session before checking permissions
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/../core/csrf.php';
csrf_boot('admin');
// RBAC: Require admin access
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
require_once __DIR__ . '/includes/admin_layout.php';
admin_render_page_start('Extensions');
echo '<p>MODULE STUB: extensions.php</p>';
admin_render_page_end();
