<?php
// Verify admin access
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /admin/login.php');
    exit;
}

require_once __DIR__ . '/../../includes/admin_header.php';


?><div class="admin-container">
    <h2>System Tools</h2>
    
    <div class="tool-section">
        <h3>PHP Information</h3>
        <div class="alert alert-warning">
            <strong>Warning:</strong> This page displays sensitive system information. Only share with trusted administrators.
        </div>
        
        <div class="phpinfo-container">
            <iframe src="/admin/system/phpinfo.php" frameborder="0" class="phpinfo-iframe"></iframe>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
