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

    public function register(): void
    {
        $request = new Request();
        $name = trim($request->post('name', ''));
        $email = trim($request->post('email', ''));
        $password = $request->post('password', '');
        $passwordConfirm = $request->post('password_confirm', '');

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

        $pdo = db();

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

        // Create user
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, status, created_at, updated_at) VALUES (?, ?, ?, 'user', 'active', NOW(), NOW())");
        $stmt->execute([$name, $email, $hash]);
        $userId = (int)$pdo->lastInsertId();

        // Auto-login
        Session::setUser($userId, $name, $email, 'user');

        // Dispatch event
        if (function_exists('cms_event')) {
            cms_event('user.registered', ['user_id' => $userId, 'email' => $email]);
        }

        Session::flash('success', 'Welcome! Your account has been created.');
        Response::redirect('/account');
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
            Session::flash('error', 'Your account is inactive. Please verify your email.');
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
}
