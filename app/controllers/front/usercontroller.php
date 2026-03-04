<?php
declare(strict_types=1);

namespace App\Controllers\Front;

use Core\Request;
use Core\Response;
use Core\Session;

/**
 * Frontend User Authentication & Account
 * Handles: register, login, logout, account, password change
 */
class UserController
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    // ─── Registration ───

    public function showRegister(): void
    {
        if (Session::isUserLoggedIn()) {
            Response::redirect('/account');
        }
        render('front/auth/register', [
            'error' => Session::getFlash('error'),
            'success' => Session::getFlash('success'),
            'old' => Session::getFlash('old_input'),
        ]);
    }

    private const MAX_REGISTER_PER_IP = 5;
    private const REGISTER_WINDOW_MINUTES = 60;

    public function register(): void
    {
        $request = new Request();
        $name = trim($request->post('name', ''));
        $email = trim($request->post('email', ''));
        $password = $request->post('password', '');
        $passwordConfirm = $request->post('password_confirm', '');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            Session::flash('error', 'All fields are required.');
            Session::flash('old_input', ['name' => $name, 'email' => $email]);
            Response::redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please enter a valid email address.');
            Session::flash('old_input', ['name' => $name, 'email' => $email]);
            Response::redirect('/register');
        }

        if (strlen($password) < 8) {
            Session::flash('error', 'Password must be at least 8 characters.');
            Session::flash('old_input', ['name' => $name, 'email' => $email]);
            Response::redirect('/register');
        }

        if ($password !== $passwordConfirm) {
            Session::flash('error', 'Passwords do not match.');
            Session::flash('old_input', ['name' => $name, 'email' => $email]);
            Response::redirect('/register');
        }

        // Honeypot — hidden field bots fill in
        $honeypot = trim($request->post('website_url', ''));
        if (!empty($honeypot)) {
            // Bot detected — pretend success
            Session::flash('success', 'Please check your email to verify your account.');
            Response::redirect('/register');
        }

        $pdo = db();

        // Rate limiting: max registrations per IP per hour
        $this->ensureRateLimitTable($pdo);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM rate_limits WHERE ip = ? AND action = 'register' AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)");
        $stmt->execute([$ip, self::REGISTER_WINDOW_MINUTES]);
        if ((int)$stmt->fetchColumn() >= self::MAX_REGISTER_PER_IP) {
            Session::flash('error', 'Too many registration attempts. Please try again later.');
            Session::flash('old_input', ['name' => $name, 'email' => $email]);
            Response::redirect('/register');
        }

        // Log this attempt
        $pdo->prepare("INSERT INTO rate_limits (ip, action, created_at) VALUES (?, 'register', NOW())")->execute([$ip]);

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            Session::flash('error', 'An account with this email already exists.');
            Session::flash('old_input', ['name' => $name, 'email' => $email]);
            Response::redirect('/register');
        }

        // Check username uniqueness
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            Session::flash('error', 'This username is already taken.');
            Session::flash('old_input', ['name' => $name, 'email' => $email]);
            Response::redirect('/register');
        }

        // Create user (status=pending until email verified)
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $verifyToken = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, status, email_verify_token, created_at, updated_at) VALUES (?, ?, ?, 'user', 'pending', ?, NOW(), NOW())");
        $stmt->execute([$name, $email, $hash, $verifyToken]);
        $userId = (int)$pdo->lastInsertId();

        // Send verification email
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $verifyUrl = "{$protocol}://{$host}/verify-email?token={$verifyToken}";

        require_once CMS_ROOT . '/core/mailer.php';
        $body = "<p>Hi {$name},</p>"
            . "<p>Thanks for signing up! Please verify your email address:</p>"
            . "<p><a href=\"{$verifyUrl}\" style=\"background:#6366f1;color:#fff;padding:10px 24px;border-radius:6px;text-decoration:none;display:inline-block;\">Verify Email</a></p>"
            . "<p style=\"color:#94a3b8;font-size:13px;\">If you didn't create this account, ignore this email.</p>";
        cms_send_email($email, 'Verify Your Email', $body, ['Content-Type' => 'text/html; charset=UTF-8']);

        // Dispatch event
        if (function_exists('cms_event')) {
            cms_event('user.registered', ['user_id' => $userId, 'email' => $email]);
        }

        Session::flash('success', 'Account created! Please check your email to verify your address.');
        Response::redirect('/login');
    }

    /**
     * Verify email address via token
     * GET /verify-email?token=...
     */
    public function verifyEmail(): void
    {
        $token = trim($_GET['token'] ?? '');
        if (empty($token) || strlen($token) !== 64) {
            Session::flash('error', 'Invalid verification link.');
            Response::redirect('/login');
            return;
        }

        $pdo = db();

        // Add email_verify_token column if not exists (safe migration)
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN email_verify_token VARCHAR(64) DEFAULT NULL");
        } catch (\Exception $e) {}

        $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE email_verify_token = ? AND status = 'pending' LIMIT 1");
        $stmt->execute([$token]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            Session::flash('error', 'Invalid or already used verification link.');
            Response::redirect('/login');
            return;
        }

        // Activate user
        $pdo->prepare("UPDATE users SET status = 'active', email_verify_token = NULL, updated_at = NOW() WHERE id = ?")->execute([$user['id']]);

        // Auto-login after verification
        Session::setUser((int)$user['id'], $user['username'], $user['email'], 'user');

        Session::flash('success', 'Email verified! Welcome aboard.');
        Response::redirect('/account');
    }

    /**
     * Resend verification email
     * POST /resend-verification
     */
    public function resendVerification(): void
    {
        $request = new Request();
        $email = trim($request->post('email', ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please enter a valid email address.');
            Response::redirect('/login');
            return;
        }

        $pdo = db();
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ? AND status = 'pending' LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Always show success (prevent enumeration)
        if ($user) {
            $verifyToken = bin2hex(random_bytes(32));
            $pdo->prepare("UPDATE users SET email_verify_token = ? WHERE id = ?")->execute([$verifyToken, $user['id']]);

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $verifyUrl = "{$protocol}://{$host}/verify-email?token={$verifyToken}";

            require_once CMS_ROOT . '/core/mailer.php';
            $body = "<p>Hi {$user['username']},</p>"
                . "<p>Here's your new verification link:</p>"
                . "<p><a href=\"{$verifyUrl}\" style=\"background:#6366f1;color:#fff;padding:10px 24px;border-radius:6px;text-decoration:none;display:inline-block;\">Verify Email</a></p>";
            cms_send_email($email, 'Verify Your Email', $body, ['Content-Type' => 'text/html; charset=UTF-8']);
        }

        Session::flash('success', 'If that email is pending verification, a new link has been sent.');
        Response::redirect('/login');
    }

    /**
     * Ensure rate_limits table exists
     */
    private function ensureRateLimitTable(\PDO $pdo): void
    {
        static $checked = false;
        if ($checked) return;
        $pdo->exec("CREATE TABLE IF NOT EXISTS rate_limits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip VARCHAR(45) NOT NULL,
            action VARCHAR(32) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ip_action (ip, action),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        // Cleanup old entries (> 24h)
        $pdo->exec("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $checked = true;
    }

    // ─── Login ───

    public function showLogin(): void
    {
        if (Session::isUserLoggedIn()) {
            Response::redirect('/account');
        }
        render('front/auth/login', [
            'error' => Session::getFlash('error'),
            'success' => Session::getFlash('success'),
        ]);
    }

    public function login(): void
    {
        $request = new Request();
        $email = trim($request->post('email', ''));
        $password = $request->post('password', '');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (empty($email) || empty($password)) {
            Session::flash('error', 'Please enter your email and password.');
            Response::redirect('/login');
        }

        // Rate limiting
        if ($this->isRateLimited($ip)) {
            Session::flash('error', 'Too many login attempts. Please wait 15 minutes.');
            Response::redirect('/login');
        }

        $pdo = db();
        $stmt = $pdo->prepare("SELECT id, username, email, password, role, status FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->logAttempt($email, $ip, false);
            Session::flash('error', 'Invalid email or password.');
            Response::redirect('/login');
        }

        if ($user['status'] === 'banned') {
            Session::flash('error', 'Your account has been suspended. Please contact support.');
            Response::redirect('/login');
        }

        if ($user['status'] === 'inactive') {
            Session::flash('error', 'Your account is inactive. Please contact support.');
            Response::redirect('/login');
        }

        if ($user['status'] === 'pending') {
            Session::flash('error', 'Please verify your email address before signing in.');
            Session::flash('pending_email', $email);
            Response::redirect('/login');
        }

        $this->logAttempt($email, $ip, true);
        Session::setUser((int)$user['id'], $user['username'], $user['email'], $user['role'] ?? 'user');

        // Redirect to intended URL or account
        $redirect = Session::getFlash('redirect_after_login') ?: '/account';
        Response::redirect($redirect);
    }

    // ─── Logout ───

    public function logout(): void
    {
        Session::userLogout();
        Response::redirect('/');
    }

    // ─── Account Dashboard ───

    public function account(): void
    {
        Session::requireUser();

        $pdo = db();
        $userId = Session::getUserId();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            Session::userLogout();
            Response::redirect('/login');
        }

        // Gather user's data from various tables
        $stats = [];
        $tables = [
            'orders' => "SELECT COUNT(*) FROM orders WHERE user_id = ? OR customer_email = ?",
            'bookings' => "SELECT COUNT(*) FROM booking_appointments WHERE customer_email = ?",
            'memberships' => "SELECT COUNT(*) FROM membership_members WHERE user_id = ?",
            'enrollments' => "SELECT COUNT(*) FROM lms_enrollments WHERE user_id = ?",
        ];

        foreach ($tables as $key => $sql) {
            try {
                $stmt = $pdo->prepare($sql);
                $paramCount = substr_count($sql, '?');
                if ($paramCount === 2) {
                    $stmt->execute([$userId, $user['email']]);
                } else {
                    $stmt->execute([$userId]);
                }
                $stats[$key] = (int)$stmt->fetchColumn();
            } catch (\Throwable $e) {
                $stats[$key] = 0;
            }
        }

        render('front/auth/account', [
            'user' => $user,
            'stats' => $stats,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error'),
        ]);
    }

    // ─── Update Profile ───

    public function updateProfile(): void
    {
        Session::requireUser();

        $request = new Request();
        $userId = Session::getUserId();
        $name = trim($request->post('name', ''));
        $email = trim($request->post('email', ''));

        if (empty($name) || empty($email)) {
            Session::flash('error', 'Name and email are required.');
            Response::redirect('/account');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Invalid email address.');
            Response::redirect('/account');
        }

        $pdo = db();

        // Check email uniqueness (exclude self)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            Session::flash('error', 'This email is already in use.');
            Response::redirect('/account');
        }

        $pdo->prepare("UPDATE users SET username = ?, email = ?, updated_at = NOW() WHERE id = ?")
            ->execute([$name, $email, $userId]);

        // Update session
        Session::set('user_name', $name);
        Session::set('user_email', $email);

        Session::flash('success', 'Profile updated successfully.');
        Response::redirect('/account');
    }

    // ─── Forgot Password ───

    public function showForgotPassword(): void
    {
        render('front/auth/forgot-password', [
            'error' => Session::getFlash('error'),
            'success' => Session::getFlash('success'),
        ]);
    }

    public function forgotPassword(): void
    {
        $request = new Request();
        $email = trim($request->post('email', ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please enter a valid email address.');
            Response::redirect('/forgot-password');
        }

        $pdo = db();

        // Ensure table exists
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

        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Always show success (prevent enumeration)
        if ($user) {
            // Rate limit: 3 per hour
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM password_resets WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
            $stmt->execute([$email]);
            if ((int)$stmt->fetchColumn() < 3) {
                $token = bin2hex(random_bytes(32));
                $pdo->prepare("UPDATE password_resets SET used = 1 WHERE email = ? AND used = 0")->execute([$email]);
                $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)")
                    ->execute([$email, $token, date('Y-m-d H:i:s', time() + 3600)]);

                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $resetUrl = "{$protocol}://{$host}/reset-password?token={$token}";

                require_once CMS_ROOT . '/core/mailer.php';
                $body = "<p>Hi {$user['username']},</p><p>Click the link below to reset your password:</p>"
                    . "<p><a href=\"{$resetUrl}\" style=\"background:#6366f1;color:#fff;padding:10px 24px;border-radius:6px;text-decoration:none;display:inline-block;\">Reset Password</a></p>"
                    . "<p style=\"color:#94a3b8;font-size:13px;\">This link expires in 1 hour. If you didn't request this, ignore this email.</p>";
                cms_send_email($email, 'Password Reset', $body, ['Content-Type' => 'text/html; charset=UTF-8']);
            }
        }

        Session::flash('success', 'If that email is registered, you\'ll receive a reset link shortly.');
        Response::redirect('/forgot-password');
    }

    public function showResetPassword(): void
    {
        $token = trim($_GET['token'] ?? '');
        if (empty($token)) {
            Session::flash('error', 'Invalid reset link.');
            Response::redirect('/forgot-password');
        }

        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW() LIMIT 1");
        $stmt->execute([$token]);
        if (!$stmt->fetch()) {
            Session::flash('error', 'This reset link is invalid or expired.');
            Response::redirect('/forgot-password');
        }

        render('front/auth/reset-password', [
            'token' => $token,
            'error' => Session::getFlash('error'),
        ]);
    }

    public function resetPassword(): void
    {
        $request = new Request();
        $token = trim($request->post('token', ''));
        $password = $request->post('password', '');
        $confirm = $request->post('password_confirm', '');

        if (empty($token) || strlen($password) < 8) {
            Session::flash('error', 'Password must be at least 8 characters.');
            Response::redirect("/reset-password?token={$token}");
        }

        if ($password !== $confirm) {
            Session::flash('error', 'Passwords do not match.');
            Response::redirect("/reset-password?token={$token}");
        }

        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$reset) {
            Session::flash('error', 'Reset link expired.');
            Response::redirect('/forgot-password');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?")->execute([$hash, $reset['email']]);
        $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = ?")->execute([$reset['id']]);
        $pdo->exec("DELETE FROM password_resets WHERE expires_at < NOW() OR used = 1");

        Session::flash('success', 'Password reset! You can now sign in.');
        Response::redirect('/login');
    }

    // ─── Change Password ───

    public function changePassword(): void
    {
        Session::requireUser();

        $request = new Request();
        $userId = Session::getUserId();
        $current = $request->post('current_password', '');
        $newPass = $request->post('new_password', '');
        $confirm = $request->post('new_password_confirm', '');

        if (empty($current) || empty($newPass)) {
            Session::flash('error', 'All password fields are required.');
            Response::redirect('/account');
        }

        if (strlen($newPass) < 8) {
            Session::flash('error', 'New password must be at least 8 characters.');
            Response::redirect('/account');
        }

        if ($newPass !== $confirm) {
            Session::flash('error', 'New passwords do not match.');
            Response::redirect('/account');
        }

        $pdo = db();
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !password_verify($current, $user['password'])) {
            Session::flash('error', 'Current password is incorrect.');
            Response::redirect('/account');
        }

        $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
        $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?")->execute([$hash, $userId]);

        Session::flash('success', 'Password changed successfully.');
        Response::redirect('/account');
    }

    // ─── Private helpers ───

    private function isRateLimited(string $ip): bool
    {
        try {
            $pdo = db();
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND success = 0 AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)"
            );
            $stmt->execute([$ip, self::LOCKOUT_MINUTES]);
            return (int)$stmt->fetchColumn() >= self::MAX_LOGIN_ATTEMPTS;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function logAttempt(string $email, string $ip, bool $success): void
    {
        try {
            $pdo = db();
            $stmt = $pdo->prepare(
                "INSERT INTO login_attempts (username, ip_address, user_agent, success, attempted_at) VALUES (?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                substr($email, 0, 190),
                $ip,
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
                $success ? 1 : 0,
            ]);
        } catch (\Throwable $e) {
            // login_attempts table might not exist for frontend
        }
    }

    // ─── GDPR: Account Deletion ───

    /**
     * Show account deletion confirmation page
     * GET /account/delete
     */
    public function showDeleteAccount(): void
    {
        Session::requireUser();
        render('front/auth/delete-account', [
            'error' => Session::getFlash('error'),
        ]);
    }

    /**
     * Process account deletion (GDPR right to erasure)
     * POST /account/delete
     */
    public function deleteAccount(): void
    {
        Session::requireUser();

        $request = new Request();
        $password = $request->post('password', '');
        $confirm = trim($request->post('confirm_delete', ''));
        $userId = Session::getUserId();

        // Require password confirmation
        $pdo = db();
        $stmt = $pdo->prepare("SELECT password, email, username FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            Session::flash('error', 'Incorrect password. Please try again.');
            Response::redirect('/account/delete');
            return;
        }

        if ($confirm !== 'DELETE') {
            Session::flash('error', 'Please type DELETE to confirm account deletion.');
            Response::redirect('/account/delete');
            return;
        }

        // Anonymize user data (GDPR compliant — don't hard delete, anonymize)
        $anonEmail = 'deleted_' . $userId . '@removed.local';
        $anonName = 'Deleted User #' . $userId;
        $pdo->prepare("UPDATE users SET 
            username = ?, 
            email = ?, 
            password = ?, 
            status = 'deleted', 
            email_verify_token = NULL, 
            updated_at = NOW() 
            WHERE id = ?")
            ->execute([$anonName, $anonEmail, password_hash(bin2hex(random_bytes(32)), PASSWORD_BCRYPT), $userId]);

        // Clean up related data
        try { $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$user['email']]); } catch (\Exception $e) {}
        try { $pdo->prepare("DELETE FROM rate_limits WHERE ip = ?")->execute([$_SERVER['REMOTE_ADDR'] ?? '']); } catch (\Exception $e) {}

        // Dispatch event
        if (function_exists('cms_event')) {
            cms_event('user.deleted', ['user_id' => $userId, 'email' => $user['email']]);
        }

        // Logout
        Session::userLogout();

        Session::flash('success', 'Your account has been deleted. We\'re sorry to see you go.');
        Response::redirect('/');
    }

    /**
     * Export user data (GDPR right to data portability)
     * GET /account/export
     */
    public function exportData(): void
    {
        Session::requireUser();
        $userId = Session::getUserId();

        $pdo = db();
        $stmt = $pdo->prepare("SELECT id, username, email, role, status, created_at, updated_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        $export = [
            'exported_at' => date('Y-m-d H:i:s'),
            'user' => $user,
        ];

        // Collect orders if they exist
        try {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE email = ? OR user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$user['email'], $userId]);
            $export['orders'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        // Collect contact submissions
        try {
            $stmt = $pdo->prepare("SELECT * FROM contact_submissions WHERE email = ? ORDER BY created_at DESC");
            $stmt->execute([$user['email']]);
            $export['contact_submissions'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="my-data-export-' . date('Y-m-d') . '.json"');
        header('Cache-Control: no-cache, no-store');
        echo json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
