<?php
if (!defined('DEV_MODE')) { require_once __DIR__ . '/../config.php'; }
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
// Start session before checking permissions
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');
// RBAC: Require admin access
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';
?>
<main class="container">
  <h1>Galleries</h1>
  <div class="card"><p class="muted">Galleries module scaffold.</p></div>
</main>
<?php require_once __DIR__ . '/includes/footer.php';
