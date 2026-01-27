<?php
/**
 * Theme Builder 3.0 - Preset Library Functions
 *
 * Pre-built template designs for quick start.
 * Add to /core/theme-builder/presets.php
 * Then add: require_once __DIR__ . '/presets.php'; to init.php
 *
 * @package ThemeBuilder
 * @version 3.0
 */

// ═══════════════════════════════════════════════════════════════════════════════
// TB PRESET LIBRARY - Pre-built templates for quick start
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Ensure the tb_preset_library table exists
 */
function tb_ensure_presets_table(): void
{
    $db = \core\Database::connection();
    
    $stmt = $db->query("SHOW TABLES LIKE 'tb_preset_library'");
    if ($stmt->fetch()) {
        return;
    }
    
    $db->exec("CREATE TABLE IF NOT EXISTS tb_preset_library (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        type ENUM('header', 'footer', 'archive', 'single', 'sidebar', '404') NOT NULL,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(100) NOT NULL,
        description TEXT,
        thumbnail VARCHAR(500) DEFAULT NULL COMMENT 'URL to preview image',
        content_json LONGTEXT NOT NULL,
        tags VARCHAR(255) DEFAULT NULL COMMENT 'Comma-separated tags for filtering',
        is_premium TINYINT(1) DEFAULT 0,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY idx_type_slug (type, slug),
        INDEX idx_type (type),
        INDEX idx_premium (is_premium)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

/**
 * Get all presets for a specific template type
 */
function tb_get_presets(string $type): array
{
    tb_ensure_presets_table();
    $db = \core\Database::connection();
    
    $stmt = $db->prepare("
        SELECT id, type, name, slug, description, thumbnail, tags, is_premium, sort_order
        FROM tb_preset_library
        WHERE type = ?
        ORDER BY sort_order ASC, name ASC
    ");
    $stmt->execute([$type]);
    
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Get all presets grouped by type
 */
function tb_get_all_presets(): array
{
    tb_ensure_presets_table();
    $db = \core\Database::connection();
    
    $stmt = $db->query("
        SELECT id, type, name, slug, description, thumbnail, tags, is_premium, sort_order
        FROM tb_preset_library
        ORDER BY type, sort_order ASC, name ASC
    ");
    
    $grouped = [];
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $grouped[$row['type']][] = $row;
    }
    
    return $grouped;
}

/**
 * Get a single preset by ID with full content
 */
function tb_get_preset(int $id): ?array
{
    tb_ensure_presets_table();
    $db = \core\Database::connection();
    
    $stmt = $db->prepare("SELECT * FROM tb_preset_library WHERE id = ?");
    $stmt->execute([$id]);
    $preset = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    if ($preset && isset($preset['content_json'])) {
        $preset['content'] = json_decode($preset['content_json'], true);
    }
    
    return $preset ?: null;
}

/**
 * Get preset by type and slug
 */
function tb_get_preset_by_slug(string $type, string $slug): ?array
{
    tb_ensure_presets_table();
    $db = \core\Database::connection();
    
    $stmt = $db->prepare("SELECT * FROM tb_preset_library WHERE type = ? AND slug = ?");
    $stmt->execute([$type, $slug]);
    $preset = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    if ($preset && isset($preset['content_json'])) {
        $preset['content'] = json_decode($preset['content_json'], true);
    }
    
    return $preset ?: null;
}

/**
 * Save a preset (insert or update)
 */
function tb_save_preset(array $data, ?int $id = null): int
{
    tb_ensure_presets_table();
    $db = \core\Database::connection();
    
    $contentJson = is_array($data['content'] ?? null)
        ? json_encode($data['content'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        : ($data['content_json'] ?? '{"sections":[]}');
    
    if ($id) {
        $stmt = $db->prepare("
            UPDATE tb_preset_library
            SET name = ?, slug = ?, type = ?, description = ?, thumbnail = ?, 
                content_json = ?, tags = ?, is_premium = ?, sort_order = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data['name'] ?? 'Untitled',
            $data['slug'] ?? 'untitled',
            $data['type'] ?? 'header',
            $data['description'] ?? '',
            $data['thumbnail'] ?? null,
            $contentJson,
            $data['tags'] ?? null,
            (int)($data['is_premium'] ?? 0),
            (int)($data['sort_order'] ?? 0),
            $id
        ]);
        return $id;
    }
    
    // Check if preset with same type+slug exists
    $existing = tb_get_preset_by_slug($data['type'] ?? 'header', $data['slug'] ?? 'untitled');
    if ($existing) {
        return tb_save_preset($data, (int)$existing['id']);
    }
    
    $stmt = $db->prepare("
        INSERT INTO tb_preset_library (type, name, slug, description, thumbnail, content_json, tags, is_premium, sort_order)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $data['type'] ?? 'header',
        $data['name'] ?? 'Untitled',
        $data['slug'] ?? 'untitled',
        $data['description'] ?? '',
        $data['thumbnail'] ?? null,
        $contentJson,
        $data['tags'] ?? null,
        (int)($data['is_premium'] ?? 0),
        (int)($data['sort_order'] ?? 0)
    ]);
    
    return (int)$db->lastInsertId();
}

/**
 * Delete a preset
 */
function tb_delete_preset(int $id): bool
{
    $db = \core\Database::connection();
    $stmt = $db->prepare("DELETE FROM tb_preset_library WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Import preset content into a template (replaces existing content)
 */
function tb_import_preset_to_template(int $presetId, int $templateId): bool
{
    $preset = tb_get_preset($presetId);
    if (!$preset || !isset($preset['content'])) {
        return false;
    }
    
    $template = tb_get_template($templateId);
    if (!$template) {
        return false;
    }
    
    return tb_save_template([
        'name' => $template['name'],
        'type' => $template['type'],
        'content' => $preset['content'],
        'conditions' => $template['conditions'],
        'priority' => $template['priority'],
        'is_active' => $template['is_active'],
        'updated_by' => $_SESSION['admin_user_id'] ?? null
    ], $templateId) > 0;
}

/**
 * Get preset count by type
 */
function tb_get_preset_counts(): array
{
    tb_ensure_presets_table();
    $db = \core\Database::connection();
    
    $stmt = $db->query("
        SELECT type, COUNT(*) as count
        FROM tb_preset_library
        GROUP BY type
    ");
    
    $counts = [];
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $counts[$row['type']] = (int)$row['count'];
    }
    
    return $counts;
}

/**
 * Search presets by name or tags
 */
function tb_search_presets(string $query, ?string $type = null): array
{
    tb_ensure_presets_table();
    $db = \core\Database::connection();
    
    $searchTerm = '%' . $query . '%';
    
    if ($type) {
        $stmt = $db->prepare("
            SELECT id, type, name, slug, description, thumbnail, tags, is_premium, sort_order
            FROM tb_preset_library
            WHERE type = ? AND (name LIKE ? OR tags LIKE ? OR description LIKE ?)
            ORDER BY sort_order ASC, name ASC
        ");
        $stmt->execute([$type, $searchTerm, $searchTerm, $searchTerm]);
    } else {
        $stmt = $db->prepare("
            SELECT id, type, name, slug, description, thumbnail, tags, is_premium, sort_order
            FROM tb_preset_library
            WHERE name LIKE ? OR tags LIKE ? OR description LIKE ?
            ORDER BY type, sort_order ASC, name ASC
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    }
    
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
