<?php
namespace Modules\Admin\Controllers;

use Core\Database;
use Core\Request;
use Core\Response;
use Core\Security;

class UserController
{
    protected $db;

    public function __construct()
    {
        $this->db = \core\Database::connection();
    }

    public function index(Request $request, Response $response)
    {
        Security::verifyCsrfToken($request);
        Security::requirePermission('users.read');

        $users = $this->db->query("
            SELECT u.*, GROUP_CONCAT(r.name) as roles
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r ON r.id = ur.role_id
            WHERE u.tenant_id = ?
            GROUP BY u.id
        ", [Security::getTenantId()]);

        return $response->render('admin/users/index', [
            'users' => $users,
            'csrfToken' => Security::generateCsrfToken()
        ]);
    }

    public function create(Request $request, Response $response)
    {
        Security::verifyCsrfToken($request);
        Security::requirePermission('users.create');

        if ($request->isPost()) {
            $data = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8'
            ]);

            $data['password'] = Security::hashPassword($data['password']);
            $data['tenant_id'] = Security::getTenantId();

            $userId = $this->db->insert('users', $data);
            
            return $response->redirect('/admin/users')->withSuccess('User created');
        }

        $roles = $this->db->query("SELECT * FROM roles WHERE tenant_id = ?", [Security::getTenantId()]);
        return $response->render('admin/users/form', [
            'roles' => $roles,
            'csrfToken' => Security::generateCsrfToken()
        ]);
    }

    public function edit(Request $request, Response $response, $id)
    {
        Security::verifyCsrfToken($request);
        Security::requirePermission('users.update');

        $user = $this->db->queryOne("SELECT * FROM users WHERE id = ? AND tenant_id = ?", [
            $id, Security::getTenantId()
        ]);

        if (!$user) {
            return $response->redirect('/admin/users')->withError('User not found');
        }

        if ($request->isPost()) {
            $data = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email,'.$id
            ]);

            if ($request->post('password')) {
                $data['password'] = Security::hashPassword($request->post('password'));
            }

            $this->db->update('users', $data, ['id' => $id]);
            
            // Update roles
            $this->db->delete('user_roles', ['user_id' => $id]);
            if ($roles = $request->post('roles')) {
                foreach ($roles as $roleId) {
                    $this->db->insert('user_roles', [
                        'user_id' => $id,
                        'role_id' => $roleId
                    ]);
                }
            }

            return $response->redirect('/admin/users')->withSuccess('User updated');
        }

        $userRoles = $this->db->queryColumn("SELECT role_id FROM user_roles WHERE user_id = ?", [$id]);
        $roles = $this->db->query("SELECT * FROM roles WHERE tenant_id = ?", [Security::getTenantId()]);

        return $response->render('admin/users/form', [
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles,
            'csrfToken' => Security::generateCsrfToken()
        ]);
    }

    public function delete(Request $request, Response $response, $id)
    {
        Security::verifyCsrfToken($request);
        Security::requirePermission('users.delete');

        $user = $this->db->queryOne("SELECT id FROM users WHERE id = ? AND tenant_id = ?", [
            $id, Security::getTenantId()
        ]);

        if ($user) {
            $this->db->delete('users', ['id' => $id]);
            $this->db->delete('user_roles', ['user_id' => $id]);
            return $response->redirect('/admin/users')->withSuccess('User deleted');
        }

        return $response->redirect('/admin/users')->withError('User not found');
    }
}
