<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class UsersController
{
    public function index(Request $request): void
    {
        $pdo = db();
        $stmt = $pdo->query("SELECT id, username, email, role, last_login, created_at FROM admins ORDER BY id ASC");
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        render('admin/users/index', [
            'users' => $users,
            'currentUserId' => Session::getAdminId(),
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function create(Request $request): void
    {
        render('admin/users/form', [
            'user' => null,
            'action' => 'create'
        ]);
    }

    public function store(Request $request): void
    {
        $username = trim($request->post('username', ''));
        $email = trim($request->post('email', '')) ?: null;
        $password = $request->post('password', '');
        $password_confirm = $request->post('password_confirm', '');
        $role = in_array($request->post('role'), ['admin', 'editor', 'viewer']) ? $request->post('role') : 'admin';

        if (empty($username)) {
            Session::flash('error', 'Username is required.');
            Response::redirect('/admin/users/create');
        }

        if (strlen($username) < 3) {
            Session::flash('error', 'Username must be at least 3 characters.');
            Response::redirect('/admin/users/create');
        }

        if (empty($password)) {
            Session::flash('error', 'Password is required.');
            Response::redirect('/admin/users/create');
        }

        if (strlen($password) < 8) {
            Session::flash('error', 'Password must be at least 8 characters.');
            Response::redirect('/admin/users/create');
        }

        if ($password !== $password_confirm) {
            Session::flash('error', 'Passwords do not match.');
            Response::redirect('/admin/users/create');
        }

        $pdo = db();

        $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            Session::flash('error', 'Username already exists.');
            Response::redirect('/admin/users/create');
        }

        if ($email) {
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                Session::flash('error', 'Email already exists.');
                Response::redirect('/admin/users/create');
            }
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$username, $email, $password_hash, $role]);

        Session::flash('success', 'User created successfully.');
        Response::redirect('/admin/users');
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');
        $user = $this->findUser($id);

        if (!$user) {
            Session::flash('error', 'User not found.');
            Response::redirect('/admin/users');
        }

        render('admin/users/form', [
            'user' => $user,
            'action' => 'edit'
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int)$request->param('id');
        $user = $this->findUser($id);

        if (!$user) {
            Session::flash('error', 'User not found.');
            Response::redirect('/admin/users');
        }

        $username = trim($request->post('username', ''));
        $email = trim($request->post('email', '')) ?: null;
        $password = $request->post('password', '');
        $password_confirm = $request->post('password_confirm', '');
        $role = in_array($request->post('role'), ['admin', 'editor', 'viewer']) ? $request->post('role') : 'admin';

        if (empty($username)) {
            Session::flash('error', 'Username is required.');
            Response::redirect("/admin/users/{$id}/edit");
        }

        if (strlen($username) < 3) {
            Session::flash('error', 'Username must be at least 3 characters.');
            Response::redirect("/admin/users/{$id}/edit");
        }

        // Check password only if provided
        if (!empty($password)) {
            if (strlen($password) < 8) {
                Session::flash('error', 'Password must be at least 8 characters.');
                Response::redirect("/admin/users/{$id}/edit");
            }

            if ($password !== $password_confirm) {
                Session::flash('error', 'Passwords do not match.');
                Response::redirect("/admin/users/{$id}/edit");
            }
        }

        $pdo = db();

        $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
        $stmt->execute([$username, $id]);
        if ($stmt->fetch()) {
            Session::flash('error', 'Username already exists.');
            Response::redirect("/admin/users/{$id}/edit");
        }

        if ($email) {
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                Session::flash('error', 'Email already exists.');
                Response::redirect("/admin/users/{$id}/edit");
            }
        }

        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admins SET username = ?, email = ?, password_hash = ?, role = ? WHERE id = ?");
            $stmt->execute([$username, $email, $password_hash, $role, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE admins SET username = ?, email = ?, role = ? WHERE id = ?");
            $stmt->execute([$username, $email, $role, $id]);
        }

        Session::flash('success', 'User updated successfully.');
        Response::redirect('/admin/users');
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->param('id');
        $currentUserId = Session::getAdminId();

        if ($id === $currentUserId) {
            Session::flash('error', 'You cannot delete yourself.');
            Response::redirect('/admin/users');
        }

        $pdo = db();

        // Check if this is the last admin
        $stmt = $pdo->query("SELECT COUNT(*) FROM admins WHERE role = 'admin'");
        $adminCount = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT role FROM admins WHERE id = ?");
        $stmt->execute([$id]);
        $userRole = $stmt->fetchColumn();

        if ($adminCount <= 1 && $userRole === 'admin') {
            Session::flash('error', 'Cannot delete the last admin user.');
            Response::redirect('/admin/users');
        }

        $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'User deleted successfully.');
        Response::redirect('/admin/users');
    }

    private function findUser(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT id, username, email, role, last_login, created_at FROM admins WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user ?: null;
    }
}
