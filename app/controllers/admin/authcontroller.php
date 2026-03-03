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

    /**
     * Show forgot password form
     */
    public function showForgotPassword(): void
    {
        render('admin/auth/forgot-password', [
            'error' => Session::getFlash('error'),
            'success' => Session::getFlash('success'),
        ]);
    }

    /**
     * Handle forgot password request — generate token, send email
     */
    public function forgotPassword(): void
    {
        $request = new Request();
        $email = trim($request->post('email', ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please enter a valid email address.');
            Response::redirect('/admin/forgot-password');
        }

        $pdo = db();

        // Ensure password_resets table exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(191) NOT NULL,
            token VARCHAR(64) NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            used TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token),
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Check if admin with this email exists
        $stmt = $pdo->prepare("SELECT id, username, email FROM admins WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Always show success (prevent email enumeration)
        if (!$admin) {
            Session::flash('success', 'If that email address is in our system, you will receive a password reset link shortly.');
            Response::redirect('/admin/forgot-password');
        }

        // Rate limit: max 3 reset requests per email per hour
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM password_resets WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $stmt->execute([$email]);
        if ((int)$stmt->fetchColumn() >= 3) {
            Session::flash('success', 'If that email address is in our system, you will receive a password reset link shortly.');
            Response::redirect('/admin/forgot-password');
        }

        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        // Invalidate previous tokens for this email
        $pdo->prepare("UPDATE password_resets SET used = 1 WHERE email = ? AND used = 0")->execute([$email]);

        // Insert new token
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expiresAt]);

        // Build reset URL
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $resetUrl = "{$protocol}://{$host}/admin/reset-password?token={$token}";

        // Send email
        $siteName = 'Jessie CMS';
        try {
            $pdo2 = db();
            $stmt2 = $pdo2->prepare("SELECT `value` FROM settings WHERE `key` = 'site_name' LIMIT 1");
            $stmt2->execute();
            $row = $stmt2->fetch(\PDO::FETCH_ASSOC);
            if ($row) $siteName = $row['value'];
        } catch (\Throwable $e) {}

        $subject = "Password Reset — {$siteName}";
        $body = $this->buildResetEmail($admin['username'], $resetUrl, $siteName);

        require_once CMS_ROOT . '/core/mailer.php';
        cms_send_email($email, $subject, $body, ['Content-Type' => 'text/html; charset=UTF-8']);

        Session::flash('success', 'If that email address is in our system, you will receive a password reset link shortly.');
        Response::redirect('/admin/forgot-password');
    }

    /**
     * Show reset password form (with token)
     */
    public function showResetPassword(): void
    {
        $token = trim($_GET['token'] ?? '');

        if (empty($token)) {
            Session::flash('error', 'Invalid or missing reset token.');
            Response::redirect('/admin/forgot-password');
        }

        // Validate token
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$reset) {
            Session::flash('error', 'This reset link is invalid or has expired. Please request a new one.');
            Response::redirect('/admin/forgot-password');
        }

        render('admin/auth/reset-password', [
            'token' => $token,
            'error' => Session::getFlash('error'),
        ]);
    }

    /**
     * Handle password reset (POST)
     */
    public function resetPassword(): void
    {
        $request = new Request();
        $token = trim($request->post('token', ''));
        $password = $request->post('password', '');
        $passwordConfirm = $request->post('password_confirm', '');

        if (empty($token)) {
            Session::flash('error', 'Invalid token.');
            Response::redirect('/admin/forgot-password');
        }

        // Validate password
        if (strlen($password) < 8) {
            Session::flash('error', 'Password must be at least 8 characters.');
            Response::redirect("/admin/reset-password?token={$token}");
        }

        if ($password !== $passwordConfirm) {
            Session::flash('error', 'Passwords do not match.');
            Response::redirect("/admin/reset-password?token={$token}");
        }

        $pdo = db();

        // Validate token
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$reset) {
            Session::flash('error', 'This reset link is invalid or has expired.');
            Response::redirect('/admin/forgot-password');
        }

        // Update password
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE email = ?");
        $stmt->execute([$hash, $reset['email']]);

        // Mark token as used
        $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = ?")->execute([$reset['id']]);

        // Clean up old tokens
        $pdo->exec("DELETE FROM password_resets WHERE expires_at < NOW() OR used = 1");

        Session::flash('success', 'Password has been reset successfully. You can now log in.');
        Response::redirect('/admin/login');
    }

    /**
     * Build HTML email for password reset
     */
    private function buildResetEmail(string $username, string $resetUrl, string $siteName): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f1f5f9; padding: 40px 20px;">
<div style="max-width: 500px; margin: 0 auto; background: #fff; border-radius: 8px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <h2 style="margin: 0 0 8px; color: #1e293b;">Password Reset</h2>
    <p style="color: #64748b; margin: 0 0 24px;">Hi {$username}, you requested a password reset for your {$siteName} account.</p>
    <a href="{$resetUrl}" style="display: inline-block; background: #6366f1; color: #fff; text-decoration: none; padding: 12px 32px; border-radius: 6px; font-weight: 600; font-size: 15px;">Reset Password</a>
    <p style="color: #94a3b8; font-size: 13px; margin: 24px 0 0;">This link expires in 1 hour. If you didn't request this, you can safely ignore this email.</p>
    <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;">
    <p style="color: #cbd5e1; font-size: 12px; margin: 0;">If the button doesn't work, copy this URL:<br><a href="{$resetUrl}" style="color: #94a3b8; word-break: break-all;">{$resetUrl}</a></p>
</div>
</body>
</html>
HTML;
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
