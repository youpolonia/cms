<?php
/**
 * Permission Model
 *
 * Handles database interactions for the permissions table.
 *
 * @package CMS
 * @subpackage Models
 */

// Prevent direct access
defined('CMS_ROOT') or die('No direct script access allowed');

class Permission
{
    private $db;

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    /**
     * Creates a new permission.
     *
     * @param string $name The name of the permission (e.g., 'create_page', 'edit_user').
     * @param string|null $description A description for the permission.
     * @return int|false The ID of the newly created permission, or false on failure.
     */
    public function createPermission($name, $description = null)
    {
        $sql = "INSERT INTO permissions (name, description) VALUES (?, ?)";
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
     * Finds a permission by its ID.
     *
     * @param int $id The permission's ID.
     * @return array|false The permission data as an associative array, or false if not found.
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT id, name, description, created_at, updated_at FROM permissions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Finds a permission by its name.
     *
     * @param string $name The permission's name.
     * @return array|false The permission data as an associative array, or false if not found.
     */
    public function findByName($name)
    {
        $stmt = $this->db->prepare("SELECT id, name, description, created_at, updated_at FROM permissions WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Retrieves all permissions.
     *
     * @return array An array of permission data.
     */
    public function getAllPermissions()
    {
        $result = $this->db->query("SELECT id, name, description FROM permissions ORDER BY name ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Updates an existing permission.
     *
     * @param int $id The ID of the permission to update.
     * @param string $name The new name for the permission.
     * @param string|null $description The new description for the permission.
     * @return bool True on success, false on failure.
     */
    public function updatePermission($id, $name, $description = null)
    {
        $sql = "UPDATE permissions SET name = ?, description = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("ssi", $name, $description, $id);
        return $stmt->execute();
    }

    /**
     * Deletes a permission.
     * Note: This will also delete associated role_permissions due to CASCADE constraints.
     *
     * @param int $id The ID of the permission to delete.
     * @return bool True on success, false on failure.
     */
    public function deletePermission($id)
    {
        $stmt = $this->db->prepare("DELETE FROM permissions WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
