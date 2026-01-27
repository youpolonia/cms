<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session

require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');

// RBAC: Require admin access
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
/**
 * Admin Dashboard Module
 *
 * Provides:
 * - User management interface
 * - Content moderation tools
 * - System configuration
 * - Tenant-specific settings
 */

class AdminModule {
    private $authService;
    private $permissionManager;
    private $tenantId;
    private $csrfToken;

    public function __construct(
        \Includes\Auth\AuthService $authService,
        \Includes\Auth\PermissionManager $permissionManager,
        string $tenantId
    ) {
        $this->authService = $authService;
        $this->permissionManager = $permissionManager;
        $this->tenantId = $tenantId;
        $this->csrfToken = \Includes\Auth\CSRFToken::generate();
    }

    /**
     * Check if user has admin access
     */
    public function isAdmin(): bool {
        return $this->permissionManager->validatePermission('admin_access');
    }

    /**
     * User Management Section
     */
    public function getUserManagementHTML(): string {
        if (!$this->isAdmin()) {
            return $this->renderError('Insufficient permissions');
        }

        return <<<HTML
        <div class="user-management">
            <h2>User Management</h2>
            <div id="user-list"><!-- AJAX loaded --></div>
            <button onclick="loadUsers()">Refresh Users</button>
            <button onclick="showAddUserForm()">Add User</button>
        </div>
HTML;
    }

    /**
     * Content Moderation Tools
     */
    public function getContentModerationHTML(): string {
        if (!$this->permissionManager->validatePermission('content_moderate')) {
            return $this->renderError('Insufficient permissions');
        }

        return <<<HTML
        <div class="content-moderation">
            <h2>Content Moderation</h2>
            <div id="approval-queue"><!-- AJAX loaded --></div>
            <div id="version-control"><!-- AJAX loaded --></div>
        </div>
HTML;
    }

    /**
     * System Configuration
     */
    public function getSystemConfigHTML(): string {
        if (!$this->isAdmin()) {
            return $this->renderError('Insufficient permissions');
        }

        return <<<HTML
        <div class="system-config">
            <h2>System Configuration</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="{$this->csrfToken}">
                <!-- Tenant-specific settings -->
                <div id="tenant-settings"><!-- AJAX loaded --></div>
            </form>
        </div>
HTML;
    }

    private function renderError(string $message): string {
        return "<div class='error'>{$message}</div>";
    }
}
