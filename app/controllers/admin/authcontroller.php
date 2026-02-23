<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class AuthController
{
    /** Max failed attempts before lockout */
    private const MAX_ATTEMPTS = 5;
    /** Lockout window in minutes */
    private const LOCKOUT_MINUTES = 15;

    public function showLogin(): void
    {
        if (Session::isLoggedIn()) {
            Response::redirect('/admin');
        }

        $error = Session::getFlash('error');
        render('admin/auth/login', ['error' => $error]);
    }

    public function login(): void
    {
        $request = new Request();
        $username = trim($request->post('username', ''));
        $password = $request->post('password', '');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (empty($username) || empty($password)) {
            Session::flash('error', 'Please enter username and password.');
            Response::redirect('/admin/login');
        }

        // Rate limiting: max 5 failed attempts per IP in 15 minutes
        if ($this->isRateLimited($ip)) {
            $this->logAttempt($username, $ip, false, 'rate_limited');
            Session::flash('error', 'Too many login attempts. Please wait 15 minutes.');
            Response::redirect('/admin/login');
        }

        $user = $this->authenticate($username, $password);

        if ($user) {
            $this->logAttempt($username, $ip, true);
            Session::setAdmin((int)$user['id'], $user['username'], 'admin');
            Session::flash('success', 'Welcome back, ' . esc($user['username']) . '!');
            Response::redirect('/admin');
        }

        $this->logAttempt($username, $ip, false, 'invalid_credentials');
        Session::flash('error', 'Invalid username or password.');
        Response::redirect('/admin/login');
    }

    public function logout(): void
    {
        Session::logout();
        Response::redirect('/admin/login');
    }

    private function authenticate(string $username, string $password): ?array
    {
        try {
            $pdo = db();
            $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                unset($user['password_hash']);
                return $user;
            }
        } catch (\Throwable $e) {
            error_log('[Auth] ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Check if IP is rate-limited (too many failed attempts)
     */
    private function isRateLimited(string $ip): bool
    {
        try {
            $pdo = db();
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM login_attempts
                 WHERE ip_address = ? AND success = 0
                 AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)"
            );
            $stmt->execute([$ip, self::LOCKOUT_MINUTES]);
            return (int)$stmt->fetchColumn() >= self::MAX_ATTEMPTS;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Log a login attempt (success or failure)
     */
    private function logAttempt(string $username, string $ip, bool $success, string $reason = ''): void
    {
        try {
            $pdo = db();
            $stmt = $pdo->prepare(
                "INSERT INTO login_attempts (username, ip_address, user_agent, success, failure_reason, attempted_at)
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                substr($username, 0, 190),
                $ip,
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
                $success ? 1 : 0,
                $reason,
            ]);
        } catch (\Throwable $e) {
            error_log('[Auth] Failed to log attempt: ' . $e->getMessage());
        }
    }
}
