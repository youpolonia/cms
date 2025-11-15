<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Role Management API Controller
 */
class RoleController {
    private RoleService $roleService;

    public function __construct(RoleService $roleService) {
        $this->roleService = $roleService;
    }

    public function createRole(array $request): array {
        if (empty($request['name'])) {
            throw new InvalidArgumentException("Role name required");
        }

        return [
            'status' => 'success',
            'data' => $this->roleService->createRole(
                $request['name'],
                $request['permissions'] ?? []
            )
        ];
    }

    public function getRole(int $roleId): array {
        $role = $this->roleService->getRole($roleId);
        if (!$role) {
            throw new RuntimeException("Role not found");
        }

        return [
            'status' => 'success',
            'data' => $role
        ];
    }

    public function updateRole(array $request): array {
        require_once __DIR__ . '/../core/csrf.php';
        csrf_validate_or_403();

        if (empty($request['role_id'])) {
            throw new InvalidArgumentException("Role ID required");
        }

        return [
            'status' => 'success',
            'data' => $this->roleService->updateRole(
                (int)$request['role_id'],
                $request['name'] ?? null,
                $request['permissions'] ?? null
            )
        ];
    }

    public function deleteRole(int $roleId): array {
        require_once __DIR__ . '/../core/csrf.php';
        csrf_validate_or_403();

        $success = $this->roleService->deleteRole($roleId);
        return [
            'status' => $success ? 'success' : 'error',
            'message' => $success ? 'Role deleted' : 'Failed to delete role'
        ];
    }

    public function listRoles(): array {
        return [
            'status' => 'success',
            'data' => $this->roleService->listRoles()
        ];
    }

    public function assignRoleToUser(array $request): array {
        if (empty($request['user_id']) || empty($request['role_id'])) {
            throw new InvalidArgumentException("User ID and Role ID required");
        }

        $success = $this->roleService->assignRoleToUser(
            (int)$request['user_id'],
            (int)$request['role_id']
        );

        return [
            'status' => $success ? 'success' : 'error',
            'message' => $success ? 'Role assigned' : 'Failed to assign role'
        ];
    }
}
