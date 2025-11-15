<?php
// Admin Authentication v1.0
// Handles session-based authentication for admin area

class AdminAuth {
    const SESSION_KEY = 'admin_auth';
    const IDLE_TIMEOUT = 1800; // 30 minutes

    public static function authenticate($username, $password) {
        $user = self::validateCredentials($username, $password);
        if ($user) {
            $_SESSION[self::SESSION_KEY] = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'roles' => $user['roles'],
                'last_activity' => time()
            ];
            return true;
        }
        return false;
    }

    public static function isAuthenticated() {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return false;
        }

        $auth = $_SESSION[self::SESSION_KEY];
        
        // Check idle timeout
        if (time() - $auth['last_activity'] > self::IDLE_TIMEOUT) {
            self::logout();
            return false;
        }

        // Update last activity
        $_SESSION[self::SESSION_KEY]['last_activity'] = time();
        return true;
    }

    public static function logout() {
        unset($_SESSION[self::SESSION_KEY]);
    }

    public static function hasRole($role) {
        if (!self::isAuthenticated()) {
            return false;
        }
        return in_array($role, $_SESSION[self::SESSION_KEY]['roles']);
    }

    private static function validateCredentials($username, $password) {
        // Get user from database
        $user = DB::queryFirstRow("SELECT * FROM admin_users WHERE username = %s", $username);
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        return [
            'id' => $user['id'],
            'username' => $user['username'],
            'roles' => json_decode($user['roles'], true),
            'last_login' => $user['last_login']
        ];
    }
}
