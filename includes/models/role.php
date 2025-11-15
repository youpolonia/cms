<?php
/**
 * Role Model
 *
 * Handles database interactions for the roles table and role_permissions pivot table.
 *
 * @package CMS
 * @subpackage Models
 */

// Prevent direct access
defined('CMS_ROOT') or die('No direct script access allowed');

class Role
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    /**
     * Creates a new role.
     *
     * @param string $name The name of the role.
     * @param string|null $description A description for the role.
     * @return int|false The ID of the newly created role, or false on failure.
     */
    public function createRole($name, $description = null)
    {
        $sql = "INSERT INTO roles (name, description) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            // Log error: prepare failed $this->db->error
            return false;
        }
        $stmt->bind_param("ss", $name, $description);
        if ($stmt->execute()) {
            return $this->db->insert_id;
        } else {
            // Log error: execute failed $stmt->error
            return false;
        }
    }

    /**
     * Finds a role by its ID.
     *
     * @param int $id The role's ID.
     * @return array|false The role data as an associative array, or false if not found.
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT id, name, description, created_at, updated_at FROM roles WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Finds a role by its name.
     *
     * @param string $name The role's name.
     * @return array|false The role data as an associative array, or false if not found.
     */
    public function findByName($name)
    {
        $stmt = $this->db->prepare("SELECT id, name, description, created_at, updated_at FROM roles WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves all roles.
     *
     * @return array An array of role data.
     */
    public function getAllRoles()
    {
        $result = $this->db->query("SELECT id, name, description FROM roles ORDER BY name ASC");
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Updates an existing role.
     *
     * @param int $id The ID of the role to update.
     * @param string $name The new name for the role.
     * @param string|null $description The new description for the role.
     * @return bool True on success, false on failure.
     */
    public function updateRole($id, $name, $description = null)
    {
        $sql = "UPDATE roles SET name = ?, description = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("ssi", $name, $description, $id);
        return $stmt->execute();
    }

    /**
     * Deletes a role.
     * Note: This will also delete associated user_roles and role_permissions due to CASCADE constraints.
     *
     * @param int $id The ID of the role to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteRole($id)
    {
        $stmt = $this->db->prepare("DELETE FROM roles WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Assigns a permission to a role.
     *
     * @param int $roleId
     * @param int $permissionId
     * @return bool True on success, false on failure or if already assigned.
     */
    public function assignPermission($roleId, $permissionId)
    {
        // Check if already assigned to prevent duplicate entry errors
        $checkStmt = $this->db->prepare("SELECT role_id FROM role_permissions WHERE role_id = ? AND permission_id = ?");
        $checkStmt->bind_param("ii", $roleId, $permissionId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows > 0) {
            return true; // Already assigned
        }

        $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("ii", $roleId, $permissionId);
        return $stmt->execute();
    }

    /**
     * Removes a permission from a role.
     *
     * @param int $roleId
     * @param int $permissionId
     * @return bool True on success, false on failure.
     */
    public function removePermission($roleId, $permissionId)
    {
        $sql = "DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("ii", $roleId, $permissionId);
        return $stmt->execute();
    }

    /**
     * Gets all permissions assigned to a specific role.
     *
     * @param int $roleId
     * @return array An array of permission IDs or permission data.
     */
    public function getRolePermissions($roleId)
    {
        // This query joins with the permissions table to get permission names as well.
        $sql = "SELECT p.id, p.name, p.description 
                FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $roleId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
}
