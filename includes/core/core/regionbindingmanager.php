<?php
require_once __DIR__ . '/../../../core/database.php';

namespace Core;

class RegionBindingManager {
    /**
     * Get widget bindings for a specific region
     * @param string $regionName The widget region name
     * @param int|null $tenantId Optional tenant ID for multi-tenant systems
     * @param string $theme Theme name (defaults to 'default')
     * @return array Array of widget bindings
     */
    public static function get_bindings_by_region(string $regionName, ?int $tenantId = null, string $theme = 'default'): array {
        $pdo = \core\Database::connection();
        $query = "SELECT * FROM widget_region_bindings
                 WHERE region = :region
                 AND (tenant_id = :tenantId OR tenant_id IS NULL)
                 AND theme = :theme
                 AND (visibility_conditions IS NULL OR JSON_LENGTH(visibility_conditions) = 0)
                 ORDER BY position ASC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':region' => $regionName,
            ':tenantId' => $tenantId,
            ':theme' => $theme
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
