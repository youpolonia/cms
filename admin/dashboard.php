<?php
if (!defined('DEV_MODE')) { require_once __DIR__ . '/../config.php'; }
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';
?>
<main class="container">
  <h1>Dashboard</h1>
  <div class="dashboard-grid">
    <div class="card"><h2>System</h2><p class="muted">Status OK.</p></div>
  </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php';
