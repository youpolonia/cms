<?php
/**
 * Security Policy Model
 * Handles security policy management and permission inheritance
 */

require_once __DIR__ . '/../../../core/database.php';
class SecurityPolicy {
    private static $table = 'security_policies';

    /**
     * Create or update a security policy
     * @param string $policyName Unique policy name
     * @param array $settings Policy settings array
     * @param int|null $parentId Optional parent policy ID for inheritance
     * @return bool True if successful
     */
    public static function savePolicy(string $policyName, array $settings, ?int $parentId = null): bool {
        $db = \core\Database::connection();
        
        // Check if policy exists
        $stmt = $db->prepare("SELECT id FROM ".self::$table." WHERE name = ?");
        $stmt->execute([$policyName]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update existing policy
            $stmt = $db->prepare("UPDATE ".self::$table." 
                SET settings = ?, parent_id = ?, updated_at = NOW() 
                WHERE id = ?");
            return $stmt->execute([
                json_encode($settings),
                $parentId,
                $existing['id']
            ]);
        } else {
            // Create new policy
            $stmt = $db->prepare("INSERT INTO ".self::$table." 
                (name, settings, parent_id, created_at, updated_at) 
                VALUES (?, ?, ?, NOW(), NOW())");
            return $stmt->execute([
                $policyName,
                json_encode($settings),
                $parentId
            ]);
        }
    }

    /**
     * Get policy with inherited settings
     * @param string $policyName Policy name to retrieve
     * @return array Combined policy settings with inheritance
     */
    public static function getPolicy(string $policyName): array {
        $db = \core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM ".self::$table." WHERE name = ?");
        $stmt->execute([$policyName]);
        $policy = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$policy) {
            return [];
        }

        $settings = json_decode($policy['settings'], true);
        
        // Apply inheritance if parent exists
        if ($policy['parent_id']) {
            $parent = self::getPolicyById($policy['parent_id']);
            if ($parent) {
                $settings = array_merge(
                    json_decode($parent['settings'], true),
                    $settings
                );
            }
        }

        return $settings;
    }

    /**
     * Get policy by ID (internal use)
     * @param int $id Policy ID
     * @return array|null Policy data or null if not found
     */
    private static function getPolicyById(int $id): ?array {
        $db = \core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM ".self::$table." WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
