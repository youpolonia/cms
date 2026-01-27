<?php
/**
 * Dashboard Renderer - Renders the admin dashboard view
 * Framework-free, FTP-deployable implementation
 */
// session boot (admin)
require_once __DIR__ . '/../../core/session_boot.php';
class DashboardRenderer {
    /**
     * Render the dashboard view
     */
    public static function render(): void {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            cms_session_start('admin');
        }

        // Verify authentication and session security
        if (!isset($_SESSION['user_id'])) {
            header('Location: /admin/login.php');
            exit;
        }

        // Validate CSRF token
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                header('HTTP/1.1 403 Forbidden');
                exit('Invalid CSRF token');
            }
        }

        // Regenerate session ID if privileges changed
        if (isset($_SESSION['privileges_changed']) && $_SESSION['privileges_changed'] === true) {
            session_regenerate_id(true);
            unset($_SESSION['privileges_changed']);
        }

        // Validate session IP
        if (!isset($_SESSION['init_ip'])) {
            $_SESSION['init_ip'] = $_SERVER['REMOTE_ADDR'];
        } elseif ($_SESSION['init_ip'] !== $_SERVER['REMOTE_ADDR']) {
            session_unset();
            session_destroy();
            header('Location: /admin/login.php?reason=ip_mismatch');
            exit;
        }

        // Get last login from session
        $lastLogin = $_SESSION['last_login'] ?? date('Y-m-d H:i:s');

        // Render the dashboard view
        require_once __DIR__ . '/../views/includes/header.php';
        
        echo '<div class="dashboard-welcome">
            <h1>Welcome to CMS Admin Dashboard</h1>
            <p>Last login: ' . htmlspecialchars($lastLogin) . '</p>
        </div>';

        echo '<div class="dashboard-section">
            <h2>System Diagnostics</h2>
            <div class="dashboard-content">
                <p>System status: <span class="status-good">Operational</span></p>
                <p>Placeholder for system diagnostics widget</p>
            </div>
        </div>';

        echo '<div class="dashboard-section">
            <h2>Recent Activity Logs</h2>
            <div class="dashboard-content">
                <p>Placeholder for recent logs widget</p>
            </div>
        </div>';

        echo '<div class="dashboard-section">
            <h2>System Statistics</h2>
            <div class="dashboard-content">
                <p>Placeholder for statistics widget</p>
            </div>
        </div>';
        require_once __DIR__ . '/../views/includes/footer.php';
    }
}
