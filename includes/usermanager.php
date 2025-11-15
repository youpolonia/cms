<?php
/**
 * User Management System
 */
class UserManager {
    const CORE_DIR = __DIR__ . '/../core/';
    const USERS_DIR = __DIR__ . '/../users/';
    const CMS_ROOT = __DIR__ . '/../';

    private $db;
    private $session;
    private $permissionManager;

    public function __construct() {
        require_once __DIR__ . '/permission/permissionmanager.php';
        $this->db = \core\Database::connection();
        $this->permissionManager = new PermissionManager();
        $this->initSession();
    }

    private function initSession() {
        require_once __DIR__ . '/../config.php';
        require_once __DIR__ . '/../core/session_boot.php';
        cms_session_start('public');
        $this->session = &$_SESSION;
    }

    public function authenticate($username, $password) {
        if (empty($username) || empty($password)) {
            return false;
        }

        $stmt = $this->db->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        $this->session['user_id'] = $user['id'];
        $this->session['username'] = $user['username'];
        return true;
    }

    public function hasPermission($permission) {
        if (!isset($this->session['user_id'])) {
            return false;
        }

        // Check GDPR permissions first
        if (in_array($permission, PermissionManager::GDPR_PERMISSIONS)) {
            return $this->permissionManager->hasGdprAccess($this->session['user_id']);
        }

        // Existing permission check logic
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM user_permissions up
            JOIN permissions p ON up.permission_id = p.id
            WHERE up.user_id = ? AND p.name = ?"
        );
        $stmt->execute([$this->session['user_id'], $permission]);
        return $stmt->fetchColumn() > 0;
    }

    // ... rest of existing methods unchanged
}
