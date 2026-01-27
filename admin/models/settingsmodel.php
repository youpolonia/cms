<?php
/**
 * Settings Model
 * Handles CRUD operations for the settings table (key-value store)
 *
 * Table structure:
 *   id (int, PK, auto_increment)
 *   key (varchar(100), unique)
 *   value (text)
 *   group_name (varchar(50))
 *   updated_at (datetime)
 */

require_once __DIR__ . '/../../core/database.php';

class SettingsModel
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = \core\Database::connection();
    }

    /**
     * Get all settings
     * @return array Array of setting rows
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            "SELECT id, `key`, value, group_name, updated_at
             FROM settings
             ORDER BY group_name, `key`"
        );
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get settings grouped by group_name
     * @return array Associative array with group_name as keys
     */
    public function getAllGrouped(): array
    {
        $settings = $this->getAll();
        $grouped = [];

        foreach ($settings as $setting) {
            $group = $setting['group_name'] ?: 'general';
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $grouped[$group][] = $setting;
        }

        return $grouped;
    }

    /**
     * Get setting by ID
     * @param int $id Setting ID
     * @return array|null Setting row or null
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, `key`, value, group_name, updated_at
             FROM settings
             WHERE id = ?"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get setting by key
     * @param string $key Setting key
     * @return array|null Setting row or null
     */
    public function getByKey(string $key): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, `key`, value, group_name, updated_at
             FROM settings
             WHERE `key` = ?"
        );
        $stmt->execute([$key]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get setting value by key
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed Setting value or default
     */
    public function getValue(string $key, $default = null)
    {
        $setting = $this->getByKey($key);
        return $setting ? $setting['value'] : $default;
    }

    /**
     * Get settings by group
     * @param string $groupName Group name
     * @return array Array of setting rows
     */
    public function getByGroup(string $groupName): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, `key`, value, group_name, updated_at
             FROM settings
             WHERE group_name = ?
             ORDER BY `key`"
        );
        $stmt->execute([$groupName]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all group names
     * @return array Array of unique group names
     */
    public function getGroups(): array
    {
        $stmt = $this->db->query(
            "SELECT DISTINCT group_name
             FROM settings
             WHERE group_name IS NOT NULL AND group_name != ''
             ORDER BY group_name"
        );
        return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'group_name');
    }

    /**
     * Create new setting
     * @param string $key Setting key
     * @param string $value Setting value
     * @param string|null $groupName Group name
     * @return int|false New setting ID or false on failure
     */
    public function create(string $key, string $value, ?string $groupName = null)
    {
        // Check if key already exists
        if ($this->getByKey($key)) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO settings (`key`, value, group_name, updated_at)
             VALUES (?, ?, ?, NOW())"
        );

        if ($stmt->execute([$key, $value, $groupName])) {
            return (int) $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Update setting by ID
     * @param int $id Setting ID
     * @param array $data Data to update (key, value, group_name)
     * @return bool Success status
     */
    public function update(int $id, array $data): bool
    {
        $existing = $this->getById($id);
        if (!$existing) {
            return false;
        }

        // Check for key uniqueness if key is being changed
        if (isset($data['key']) && $data['key'] !== $existing['key']) {
            $keyExists = $this->getByKey($data['key']);
            if ($keyExists) {
                return false;
            }
        }

        $stmt = $this->db->prepare(
            "UPDATE settings
             SET `key` = ?, value = ?, group_name = ?, updated_at = NOW()
             WHERE id = ?"
        );

        return $stmt->execute([
            $data['key'] ?? $existing['key'],
            $data['value'] ?? $existing['value'],
            $data['group_name'] ?? $existing['group_name'],
            $id
        ]);
    }

    /**
     * Set setting value (create or update by key)
     * @param string $key Setting key
     * @param string $value Setting value
     * @param string|null $groupName Group name (only used for new settings)
     * @return bool Success status
     */
    public function set(string $key, string $value, ?string $groupName = null): bool
    {
        $existing = $this->getByKey($key);

        if ($existing) {
            return $this->update($existing['id'], ['value' => $value]);
        }

        return $this->create($key, $value, $groupName) !== false;
    }

    /**
     * Delete setting by ID
     * @param int $id Setting ID
     * @return bool Success status
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM settings WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Delete setting by key
     * @param string $key Setting key
     * @return bool Success status
     */
    public function deleteByKey(string $key): bool
    {
        $stmt = $this->db->prepare("DELETE FROM settings WHERE `key` = ?");
        return $stmt->execute([$key]);
    }

    /**
     * Search settings by key or value
     * @param string $search Search term
     * @return array Array of matching settings
     */
    public function search(string $search): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, `key`, value, group_name, updated_at
             FROM settings
             WHERE `key` LIKE ? OR value LIKE ?
             ORDER BY group_name, `key`"
        );
        $searchTerm = '%' . $search . '%';
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get total count of settings
     * @return int Total count
     */
    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM settings");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Validate setting data
     * @param array $data Setting data to validate
     * @return array Array of error messages (empty if valid)
     */
    public function validate(array $data): array
    {
        $errors = [];

        if (empty($data['key'])) {
            $errors[] = 'Setting key is required';
        } elseif (strlen($data['key']) > 100) {
            $errors[] = 'Setting key must be 100 characters or less';
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $data['key'])) {
            $errors[] = 'Setting key can only contain letters, numbers, underscores, dots, and hyphens';
        }

        if (isset($data['group_name']) && strlen($data['group_name']) > 50) {
            $errors[] = 'Group name must be 50 characters or less';
        }

        return $errors;
    }
}
