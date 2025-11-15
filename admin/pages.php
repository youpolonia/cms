<?php
if (!defined('DEV_MODE')) { require_once __DIR__ . '/../config.php'; }
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';
?>
<main class="container">
  <h1>Pages</h1>
  <div class="card"><p class="muted">Pages module scaffold.</p></div>
</main>
<?php require_once __DIR__ . '/includes/footer.php';
