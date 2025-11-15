<?php
/**
 * Widget Region Binding Manager
 * Manages widget-to-region bindings for themes
 */

require_once __DIR__ . '/../core/database.php';

/**
 * Add a widget region binding
 * @param int $tenant_id
 * @param string $widget_name
 * @param string $theme
 * @param string $region
 * @param array|null $settings
 * @return bool
 */
function add_binding(int $tenant_id, string $widget_name, string $theme, string $region, ?array $settings = null): bool {
    $pdo = \core\Database::connection();

    $sql = "INSERT INTO widget_region_bindings 
            (tenant_id, widget_name, theme, region, settings) 
            VALUES (:tenant_id, :widget_name, :theme, :region, :settings)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':tenant_id', $tenant_id, PDO::PARAM_INT);
    $stmt->bindValue(':widget_name', $widget_name, PDO::PARAM_STR);
    $stmt->bindValue(':theme', $theme, PDO::PARAM_STR);
    $stmt->bindValue(':region', $region, PDO::PARAM_STR);
    $stmt->bindValue(':settings', $settings ? json_encode($settings) : null, PDO::PARAM_STR);

    return $stmt->execute();
}

/**
 * Get all bindings for a specific theme
 * @param int $tenant_id
 * @param string $theme
 * @return array
 */
function get_bindings_by_theme(int $tenant_id, string $theme): array {
    $pdo = \core\Database::connection();

    $sql = "SELECT * FROM widget_region_bindings 
            WHERE tenant_id = :tenant_id AND theme = :theme";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':tenant_id', $tenant_id, PDO::PARAM_INT);
    $stmt->bindValue(':theme', $theme, PDO::PARAM_STR);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Decode JSON settings
    return array_map(function($row) {
        if ($row['settings']) {
            $row['settings'] = json_decode($row['settings'], true);
        }
        return $row;
    }, $results);
}

/**
 * Get all bindings for a specific region
 * @param int $tenant_id
 * @param string $region
 * @return array
 */
function get_bindings_by_region(int $tenant_id, string $region): array {
    $pdo = \core\Database::connection();

    $sql = "SELECT * FROM widget_region_bindings 
            WHERE tenant_id = :tenant_id AND region = :region";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':tenant_id', $tenant_id, PDO::PARAM_INT);
    $stmt->bindValue(':region', $region, PDO::PARAM_STR);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Decode JSON settings
    return array_map(function($row) {
        if ($row['settings']) {
            $row['settings'] = json_decode($row['settings'], true);
        }
        return $row;
    }, $results);
}

/**
 * Remove a widget region binding
 * @param int $tenant_id
 * @param int $binding_id
 * @return bool
 */
function remove_binding(int $tenant_id, int $binding_id): bool {
    $pdo = \core\Database::connection();

    $sql = "DELETE FROM widget_region_bindings 
            WHERE id = :id AND tenant_id = :tenant_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $binding_id, PDO::PARAM_INT);
    $stmt->bindValue(':tenant_id', $tenant_id, PDO::PARAM_INT);

    return $stmt->execute();
}
