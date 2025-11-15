<?php
$content = ob_start();

?><h1>Welcome to Our CMS</h1>
<p>This is the default home page content. The system is working but the home page content hasn't been configured yet.</p>
<p>Please contact the administrator to set up your home page content.</p>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
