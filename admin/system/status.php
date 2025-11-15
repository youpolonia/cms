<?php
// Verify admin access and permissions
require_once __DIR__ . '/../security/admin-check.php';
require_once __DIR__ . '/../security/role-check.php';

// Check if current user has permission to view system status
if (!has_permission('view_system_status')) {
    header('HTTP/1.1 403 Forbidden');
    exit('You do not have permission to access this page');
}

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Start of page-specific content

?><div class="container-fluid px-4">
    <h1 class="mt-4">System Status</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/">Dashboard</a></li>
        <li class="breadcrumb-item active">System Status</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-info-circle me-1"></i>
            System Information
        </div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <strong>Overall Status:</strong> <span class="text-success">Operational</span>
                </li>
                <li class="list-group-item">
                    <strong>Last Checked:</strong> <?php echo date('Y-m-d H:i:s'); 
?>                </li>
                <!-- Add more system information here as needed -->
            </ul>
        </div>
    </div>

    <!-- You can add more cards or sections for different types of system information -->
    <!-- For example: Disk Space, Memory Usage, Database Connectivity etc. (to be implemented later) -->

</div>

<?php
// End of page-specific content
// Assuming admin_footer() or similar is called by the layout
