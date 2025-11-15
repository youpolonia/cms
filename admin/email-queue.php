<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/csrf.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
csrf_boot();
require_once __DIR__ . '/includes/admin_layout.php';
admin_render_page_start('Email Queue');
echo '<p>MODULE STUB: email-queue.php</p>';
<?php admin_render_page_end();
