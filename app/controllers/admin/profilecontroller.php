<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class ProfileController
{
    public function index(Request $request): void
    {
        $adminId = Session::getAdminId();
        if (!$adminId) {
            Response::redirect('/admin/login');
            return;
        }

        $pdo = db();
        $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM admins WHERE id = ?");
        $stmt->execute([$adminId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            Session::flash('error', 'User not found.');
            Response::redirect('/admin');
            return;
        }

        render('admin/profile/index', [
            'user' => $user,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function update(Request $request): void
    {
        $adminId = Session::getAdminId();
        if (!$adminId) {
            Response::redirect('/admin/login');
            return;
        }

        $username = trim($request->post('username', ''));
        $email = trim($request->post('email', ''));

        if (empty($username)) {
            Session::flash('error', 'Username is required.');
            Response::redirect('/admin/profile');
            return;
        }

        $pdo = db();

        // Check if username taken by another user
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
        $stmt->execute([$username, $adminId]);
        if ($stmt->fetch()) {
            Session::flash('error', 'Username already taken.');
            Response::redirect('/admin/profile');
            return;
        }

        // Update user
        $stmt = $pdo->prepare("UPDATE admins SET username = ?, email = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$username, $email, $adminId]);

        // Update session
        $_SESSION['admin_username'] = $username;

        Session::flash('success', 'Profile updated successfully.');
        Response::redirect('/admin/profile');
    }

    public function password(Request $request): void
    {
        render('admin/profile/password', [
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function updatePassword(Request $request): void
    {
        $adminId = Session::getAdminId();
        if (!$adminId) {
            Response::redirect('/admin/login');
            return;
        }

        $currentPassword = $request->post('current_password', '');
        $newPassword = $request->post('new_password', '');
        $confirmPassword = $request->post('confirm_password', '');

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            Session::flash('error', 'All fields are required.');
            Response::redirect('/admin/profile/password');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            Session::flash('error', 'New passwords do not match.');
            Response::redirect('/admin/profile/password');
            return;
        }

        if (strlen($newPassword) < 8) {
            Session::flash('error', 'Password must be at least 8 characters.');
            Response::redirect('/admin/profile/password');
            return;
        }

        $pdo = db();
        $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->execute([$adminId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            Session::flash('error', 'Current password is incorrect.');
            Response::redirect('/admin/profile/password');
            return;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admins SET password = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$hashedPassword, $adminId]);

        Session::flash('success', 'Password changed successfully.');
        Response::redirect('/admin/profile');
    }
}
