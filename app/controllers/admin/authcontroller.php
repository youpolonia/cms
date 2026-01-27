<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class AuthController
{
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

        if (empty($username) || empty($password)) {
            Session::flash('error', 'Please enter username and password.');
            Response::redirect('/admin/login');
        }

        $user = $this->authenticate($username, $password);

        if ($user) {
            Session::setAdmin((int)$user['id'], $user['username'], 'admin');
            Session::flash('success', 'Welcome back, ' . esc($user['username']) . '!');
            Response::redirect('/admin');
        }

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
}
