<?php
/**
 * Theme Builder 3.0 - Database Functions
 *
 * Database operations for Theme Builder content storage.
 * Uses \core\Database::connection() singleton for all queries.
 *
 * @package ThemeBuilder
 * @version 3.0
 */

/**
 * CRITICAL FIX: Normalize empty arrays to objects for settings/design/content keys
 *
 * PHP's json_decode($json, true) converts {} to [] (empty object to empty array).
 * This breaks JS where mod.settings.foo = 'bar' fails silently on arrays.
 * This function recursively converts empty arrays back to objects for specific keys.
 *
 * @param array &$data The content array to normalize (modified in place)
 * @return void
 */
function tb_normalize_content(array &$data): void
{
    // Keys that should always be objects, not arrays
    $objectKeys = ['settings', 'design', 'content', 'advanced', 'typography', 'hover', 'responsive'];

    foreach ($data as $key => &$value) {
        if (is_array($value)) {
            // If this key should be an object and it's an empty array, convert to stdClass marker
            if (in_array($key, $objectKeys, true) && empty($value)) {
                // Mark for JSON_FORCE_OBJECT treatment - we'll handle in encoding
                $data[$key] = new \stdClass();
            } elseif (is_array($value) && !empty($value)) {
                // Check if it's an associative array (object-like) or indexed array
                $isAssoc = array_keys($value) !== range(0, count($value) - 1);

                // If it's one of our object keys but has content, still recursively normalize
                if (in_array($key, $objectKeys, true) || $isAssoc) {
                    // It's associative - recursively normalize
                    tb_normalize_content($value);
                    $data[$key] = $value;
                } else {
                    // It's an indexed array (like sections, rows, columns, modules)
                    foreach ($value as $idx => &$item) {
                        if (is_array($item)) {
                            tb_normalize_content($item);
                        }
                    }
                }
            }
        }
    }
}

/**
 * Encode content to JSON with proper empty object handling
 *
 * Ensures that settings, design, content, advanced keys are encoded as {} not []
 *
 * @param array $content The content array to encode
 * @return string JSON string with proper object encoding
 */
function tb_encode_content(array $content): string
{
    // First normalize the content
    tb_normalize_content($content);

    // Encode with standard flags
    $json = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // Post-process: convert empty arrays to empty objects for specific keys
    // This is a safety net in case normalization missed anything
    $patterns = [
        '/"settings":\[\]/' => '"settings":{}',
        '/"design":\[\]/' => '"design":{}',
        '/"content":\[\]/' => '"content":{}',
        '/"advanced":\[\]/' => '"advanced":{}',
        '/"typography":\[\]/' => '"typography":{}',
        '/"hover":\[\]/' => '"hover":{}',
        '/"responsive":\[\]/' => '"responsive":{}',
    ];

    $json = preg_replace(array_keys($patterns), array_values($patterns), $json);

    return $json;
}

/**
 * Get page builder content for a specific page
 *
 * @param int $page_id The page ID to retrieve content for
 * @return array|null Decoded JSON content array or null if not found
 */
function tb_get_page_content(int $page_id): ?array
{
    $db = \core\Database::connection();

    $stmt = $db->prepare("
        SELECT content_json, version, updated_at
        FROM tb_pages
        WHERE page_id = ?
        ORDER BY id DESC
        LIMIT 1
    ");
    $stmt->execute([$page_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return null;
    }

    $content = json_decode($row['content_json'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return null;
    }

    return [
        'content' => $content,
        'version' => $row['version'],
        'updated_at' => $row['updated_at'],
    ];
}

/**
 * Save page builder content
 *
 * @param int $page_id The page ID to save content for
 * @param array $content The content array to save as JSON
 * @return bool True on success, false on failure
 */
function tb_save_page_content(int $page_id, array $content): bool
{
    $db = \core\Database::connection();

    // CRITICAL FIX: Use tb_encode_content() to preserve object types
    $json = tb_encode_content($content);
    if ($json === false || $json === 'null') {
        return false;
    }

    // Check if record exists
    $stmt = $db->prepare("SELECT id FROM tb_pages WHERE page_id = ? LIMIT 1");
    $stmt->execute([$page_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Update existing record
        $stmt = $db->prepare("
            UPDATE tb_pages
            SET content_json = ?, updated_at = NOW()
            WHERE page_id = ?
        ");
        return $stmt->execute([$json, $page_id]);
    }

    // Insert new record
    $stmt = $db->prepare("
        INSERT INTO tb_pages (page_id, content_json, version, created_at, updated_at)
        VALUES (?, ?, ?, NOW(), NOW())
    ");
    return $stmt->execute([$page_id, $json, TB_VERSION]);
}

/**
 * Create a revision snapshot of page content
 *
 * @param int $page_id The page ID
 * @param array $content The content array to save
 * @param int|null $user_id Optional user ID who made the change
 * @return int The new revision ID, or 0 on failure
 */
function tb_create_revision(int $page_id, array $content, ?int $user_id = null): int
{
    $db = \core\Database::connection();

    // CRITICAL FIX: Use tb_encode_content() to preserve object types
    $json = tb_encode_content($content);
    if ($json === false || $json === 'null') {
        return 0;
    }

    $stmt = $db->prepare("
        INSERT INTO tb_revisions (page_id, content_json, user_id, created_at)
        VALUES (?, ?, ?, NOW())
    ");

    if (!$stmt->execute([$page_id, $json, $user_id])) {
        return 0;
    }

    return (int) $db->lastInsertId();
}

/**
 * Get revision history for a page
 *
 * @param int $page_id The page ID
 * @param int $limit Maximum number of revisions to return
 * @return array Array of revision records
 */
function tb_get_revisions(int $page_id, int $limit = 20): array
{
    $db = \core\Database::connection();

    $stmt = $db->prepare("
        SELECT r.id, r.page_id, r.user_id, r.created_at, u.username
        FROM tb_revisions r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.page_id = ?
        ORDER BY r.created_at DESC
        LIMIT ?
    ");
    $stmt->bindValue(1, $page_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get a specific revision by ID
 *
 * @param int $revision_id The revision ID
 * @return array|null The revision data or null if not found
 */
function tb_get_revision(int $revision_id): ?array
{
    $db = \core\Database::connection();

    $stmt = $db->prepare("
        SELECT id, page_id, content_json, user_id, created_at
        FROM tb_revisions
        WHERE id = ?
    ");
    $stmt->execute([$revision_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return null;
    }

    $row['content'] = json_decode($row['content_json'], true);
    unset($row['content_json']);

    return $row;
}

/**
 * Delete old revisions keeping only the most recent N
 *
 * @param int $page_id The page ID
 * @param int $keep Number of revisions to keep
 * @return int Number of revisions deleted
 */
function tb_cleanup_revisions(int $page_id, int $keep = 50): int
{
    $db = \core\Database::connection();

    // Get IDs to keep
    $stmt = $db->prepare("
        SELECT id FROM tb_revisions
        WHERE page_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ");
    $stmt->bindValue(1, $page_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $keep, PDO::PARAM_INT);
    $stmt->execute();
    $keepIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($keepIds)) {
        return 0;
    }

    // Delete older revisions
    $placeholders = implode(',', array_fill(0, count($keepIds), '?'));
    $stmt = $db->prepare("
        DELETE FROM tb_revisions
        WHERE page_id = ? AND id NOT IN ($placeholders)
    ");

    $params = array_merge([$page_id], $keepIds);
    $stmt->execute($params);

    return $stmt->rowCount();
}

/**
 * Ensure the tb_site_templates table exists for global templates (Header, Footer, Archive, etc.)
 *
 * @return void
 */
function tb_ensure_templates_table(): void
{
    $db = \core\Database::connection();

    // Check if table exists
    $stmt = $db->query("SHOW TABLES LIKE 'tb_site_templates'");
    if ($stmt->fetch()) {
        return; // Table already exists
    }

    $db->exec("CREATE TABLE IF NOT EXISTS tb_site_templates (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        type ENUM('header', 'footer', 'archive', 'single', 'sidebar', '404') NOT NULL,
        name VARCHAR(255) NOT NULL DEFAULT 'Default',
        content_json LONGTEXT NOT NULL,
        conditions JSON DEFAULT NULL COMMENT 'Where this template applies',
        priority INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_by INT UNSIGNED NULL,
        updated_by INT UNSIGNED NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_type (type),
        INDEX idx_active (is_active),
        INDEX idx_priority (priority)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

/**
 * Get all templates, optionally filtered by type
 *
 * @param string|null $type Filter by template type
 * @return array Array of template records
 */
function tb_get_templates(?string $type = null): array
{
    $db = \core\Database::connection();

    if ($type) {
        $stmt = $db->prepare("
            SELECT * FROM tb_site_templates
            WHERE type = ?
            ORDER BY priority DESC, name ASC
        ");
        $stmt->execute([$type]);
    } else {
        $stmt = $db->query("
            SELECT * FROM tb_site_templates
            ORDER BY type, priority DESC, name ASC
        ");
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get a specific template by ID
 *
 * @param int $id Template ID
 * @return array|null Template record or null if not found
 */
function tb_get_template(int $id): ?array
{
    $db = \core\Database::connection();

    $stmt = $db->prepare("SELECT * FROM tb_site_templates WHERE id = ?");
    $stmt->execute([$id]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);

    return $template ?: null;
}

/**
 * Save a template (insert or update)
 *
 * @param array $data Template data
 * @param int|null $id Template ID for update, null for insert
 * @return int The template ID
 */
function tb_save_template(array $data, ?int $id = null): int
{
    $db = \core\Database::connection();

    // CRITICAL FIX: Use tb_encode_content() to preserve object types
    $contentJson = is_array($data['content'] ?? null)
        ? tb_encode_content($data['content'])
        : ($data['content_json'] ?? '{"sections":[]}');

    $conditionsJson = isset($data['conditions']) && is_array($data['conditions'])
        ? json_encode($data['conditions'], JSON_UNESCAPED_UNICODE)
        : ($data['conditions'] ?? null);

    if ($id) {
        // Update existing
        $stmt = $db->prepare("
            UPDATE tb_site_templates
            SET name = ?, type = ?, content_json = ?, conditions = ?, priority = ?, is_active = ?, updated_by = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $data['name'] ?? 'Untitled',
            $data['type'] ?? 'header',
            $contentJson,
            $conditionsJson,
            (int)($data['priority'] ?? 0),
            (int)($data['is_active'] ?? 1),
            $data['updated_by'] ?? null,
            $id
        ]);
        return $id;
    }

    // Insert new
    $stmt = $db->prepare("
        INSERT INTO tb_site_templates (type, name, content_json, conditions, priority, is_active, created_by, updated_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $data['type'] ?? 'header',
        $data['name'] ?? 'Untitled',
        $contentJson,
        $conditionsJson,
        (int)($data['priority'] ?? 0),
        (int)($data['is_active'] ?? 1),
        $data['created_by'] ?? null,
        $data['updated_by'] ?? null
    ]);

    return (int)$db->lastInsertId();
}

/**
 * Delete a template
 *
 * @param int $id Template ID
 * @return bool True on success
 */
function tb_delete_template(int $id): bool
{
    $db = \core\Database::connection();

    $stmt = $db->prepare("DELETE FROM tb_site_templates WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Toggle template active status
 *
 * @param int $id Template ID
 * @return bool New active status
 */
function tb_toggle_template(int $id): bool
{
    $db = \core\Database::connection();

    $stmt = $db->prepare("UPDATE tb_site_templates SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$id]);

    $stmt = $db->prepare("SELECT is_active FROM tb_site_templates WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return (bool)($result['is_active'] ?? false);
}

/**
 * Get template types with labels
 *
 * @return array Template type definitions
 */
function tb_get_template_types(): array
{
    return [
        'header' => [
            'label' => 'Header',
            'description' => 'Site header shown on all pages',
            'icon' => '📐'
        ],
        'footer' => [
            'label' => 'Footer',
            'description' => 'Site footer shown on all pages',
            'icon' => '📋'
        ],
        'archive' => [
            'label' => 'Archive',
            'description' => 'Template for blog/article archives',
            'icon' => '📚'
        ],
        'single' => [
            'label' => 'Single Post',
            'description' => 'Template for single blog posts',
            'icon' => '📄'
        ],
        'sidebar' => [
            'label' => 'Sidebar',
            'description' => 'Sidebar widget area template',
            'icon' => '📑'
        ],
        '404' => [
            'label' => '404 Page',
            'description' => 'Custom 404 error page',
            'icon' => '🚫'
        ]
    ];
}

/**
 * Check if template conditions match current page context
 *
 * @param array|null $conditions Conditions from database (JSON decoded)
 * @param array $context Current page context ['slug' => '...', 'category' => '...']
 * @return bool True if conditions match
 */
function tb_check_template_conditions(?array $conditions, array $context): bool
{
    // No conditions or empty = matches all pages
    if (empty($conditions)) {
        return true;
    }
    
    $type = $conditions['type'] ?? 'all';
    
    // All Pages - always matches
    if ($type === 'all') {
        return true;
    }
    
    // Specific Pages - check if current slug is in the list
    if ($type === 'specific') {
        $pages = $conditions['pages'] ?? [];
        $currentSlug = $context['slug'] ?? '';
        return in_array($currentSlug, $pages, true);
    }
    
    // By Category - check if current category is in the list
    if ($type === 'category') {
        $categories = $conditions['categories'] ?? [];
        $currentCategory = $context['category'] ?? '';
        return in_array($currentCategory, $categories, true);
    }
    
    // Unknown condition type - default to match
    return true;
}

/**
 * Get active template of specific type with context matching
 *
 * @param string $type Template type (header, footer, archive, single, sidebar, 404)
 * @param array $context Current page context ['slug' => '...', 'category' => '...']
 * @return array|null Template data or null if not found
 */
function tb_get_active_template(string $type, array $context = []): ?array
{
    $pdo = \core\Database::connection();
    
    // Get ALL active templates of this type, sorted by priority DESC
    $stmt = $pdo->prepare("
        SELECT * FROM tb_site_templates 
        WHERE type = ? AND is_active = 1 
        ORDER BY priority DESC
    ");
    $stmt->execute([$type]);
    $templates = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    if (empty($templates)) {
        return null;
    }
    
    // If no context provided, return first (highest priority) - backward compatible
    if (empty($context)) {
        return $templates[0];
    }
    
    // Find first template that matches conditions
    foreach ($templates as $template) {
        $conditions = null;
        if (!empty($template['conditions'])) {
            $conditions = json_decode($template['conditions'], true);
        }
        
        if (tb_check_template_conditions($conditions, $context)) {
            return $template;
        }
    }
    
    // No matching template found
    return null;
}

/**
 * Render active template of specific type
 *
 * @param string $type Template type (header, footer, archive, single, sidebar, 404)
 * @param array $context Page context ['slug' => '...', 'category' => '...'] for condition matching
 * @return string|null Rendered HTML or null if no active template
 */
function tb_render_site_template(string $type, array $context = []): ?string
{
    $template = tb_get_active_template($type, $context);
    if (!$template || empty($template['content_json'])) {
        return null;
    }
    
    $content = json_decode($template['content_json'], true);
    if (!$content || !is_array($content)) {
        return null;
    }
    
    // Ensure renderer is loaded
    $rendererPath = dirname(__FILE__) . '/renderer.php';
    if (!function_exists('tb_render_page')) {
        require_once $rendererPath;
    }
    
    // Merge context with template type info
    $renderOptions = array_merge($context, [
        'template_type' => $type,
        'is_site_template' => true
    ]);
    
    return tb_render_page($content, $renderOptions);
}
