<?php
require_once __DIR__ . '/../../core/database.php';

namespace Admin\Security\Models;

use Database;

class Policy
{
    protected $db;

    public function __construct()
    {
        $this->db = \core\Database::connection();
    }

    public function canAccessSecurityPanel(int $userId): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 FROM user_permissions 
            WHERE user_id = ? AND permission = 'admin.security'
        ");
        $stmt->execute([$userId]);
        return (bool)$stmt->fetchColumn();
    }

    public function validate(array $policyData): bool
    {
        if (empty($policyData['name'])) {
            return false;
        }

        if (!is_array($policyData['rules'] ?? null)) {
            return false;
        }

        return true;
    }

    public function save(array $policyData): bool
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO security_policies 
                (name, description, rules, created_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                description = VALUES(description),
                rules = VALUES(rules)
            ");
            return $stmt->execute([
                $policyData['name'],
                $policyData['description'],
                json_encode($policyData['rules'])
            ]);
        } catch (\PDOException $e) {
            error_log("Policy save error: " . $e->getMessage());
            return false;
        }
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT name, description, rules 
            FROM security_policies
        ");
        $policies = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($policies as &$policy) {
            $policy['rules'] = json_decode($policy['rules'], true);
        }
        
        return $policies;
    }
}
