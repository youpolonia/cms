<?php
/**
 * TB 4.0 API Handler
 *
 * Handles all AJAX requests for the Visual Builder.
 * Provides endpoints for page content, modules, library, and history.
 *
 * @package Core\TB4
 * @version 4.0
 */

namespace Core\TB4;

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/renderer.php';

class Api
{
    /**
     * Database connection
     */
    private \PDO $db;

    /**
     * Current user ID from session
     */
    private ?int $user_id = null;

    /**
     * Cached JSON input from php://input (can only be read once!)
     */
    private ?array $json_input = null;

    /**
     * Constructor - verify authentication and CSRF for POST
     */
    public function __construct()
    {
        // Database connection
        $this->db = \core\Database::connection();

        // Get user ID from session
        $this->user_id = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null;

        // Verify user is logged in
        if (!$this->user_id) {
            $this->send_error('Unauthorized - please log in', 401);
        }

        // Read and cache JSON input (php://input can only be read once!)
        $raw_input = file_get_contents('php://input');

        // DEBUG: Log raw input
        error_log('[TB4 API DEBUG] ============================================');
        error_log('[TB4 API DEBUG] Constructor called');
        error_log('[TB4 API DEBUG] Request method: ' . ($_SERVER['REQUEST_METHOD'] ?? 'unknown'));
        error_log('[TB4 API DEBUG] Content-Type: ' . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
        error_log('[TB4 API DEBUG] Raw input length: ' . strlen($raw_input));
        error_log('[TB4 API DEBUG] Raw input (first 1000 chars): ' . substr($raw_input, 0, 1000));

        if (!empty($raw_input)) {
            $this->json_input = json_decode($raw_input, true);

            // DEBUG: Log JSON decode result
            $json_error = json_last_error();
            error_log('[TB4 API DEBUG] JSON decode error code: ' . $json_error);
            error_log('[TB4 API DEBUG] JSON decode error msg: ' . json_last_error_msg());
            error_log('[TB4 API DEBUG] json_input is null: ' . ($this->json_input === null ? 'yes' : 'no'));
            error_log('[TB4 API DEBUG] json_input is array: ' . (is_array($this->json_input) ? 'yes' : 'no'));
            if (is_array($this->json_input)) {
                error_log('[TB4 API DEBUG] json_input keys: ' . implode(', ', array_keys($this->json_input)));
            }
        } else {
            error_log('[TB4 API DEBUG] Raw input is empty!');
        }
        error_log('[TB4 API DEBUG] ============================================');
    }

    /**
     * Route and handle the request
     */
    public function handle_request(): void
    {
        // Get action from request
        $action = $_GET['action'] ?? $_POST['action'] ?? '';

        if (empty($action)) {
            $this->send_error('No action specified', 400);
        }

        // Validate CSRF for POST requests
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method === 'POST') {
            $this->validate_csrf();
        }

        // Route to appropriate handler
        switch ($action) {
            // GET endpoints
            case 'get_page_content':
                $this->get_page_content();
                break;

            case 'get_modules_list':
                $this->get_modules_list();
                break;

            case 'get_module_fields':
                $this->get_module_fields();
                break;

            case 'get_library_items':
                $this->get_library_items();
                break;

            case 'get_library_item':
                $this->get_library_item();
                break;

            case 'get_history':
                $this->get_history();
                break;

            case 'get_posts':
                $this->get_posts();
                break;

            case 'get_categories':
                $this->get_categories();
                break;

            // POST endpoints
            case 'save_page_content':
                $this->save_page_content();
                break;

            case 'render_module_preview':
                $this->render_module_preview();
                break;

            case 'save_to_library':
                $this->save_to_library();
                break;

            case 'restore_history':
                $this->restore_history();
                break;

            case 'save_draft':
                $this->save_draft();
                break;

            default:
                $this->send_error('Unknown action: ' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8'), 400);
        }
    }

    /**
     * GET: Get page content from tb4_pages
     * NOTE: Uses tb4_pages.id as lookup (JS sends as 'page_id')
     *       Uses 'content' column (not 'content_json')
     */
    private function get_page_content(): void
    {
        // NOTE: JS sends row 'id' as 'page_id'
        $id = $this->get_int_param('page_id');

        if (!$id) {
            $this->send_error('page_id is required', 400);
        }

        $stmt = $this->db->prepare("
            SELECT id, title, slug, status, content, updated_at
            FROM tb4_pages
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
            $content = [];
            if (!empty($row['content'])) {
                $content = json_decode($row['content'], true) ?: [];
            }
            $this->send_success([
                'id' => (int)$row['id'],
                'page_id' => (int)$row['id'],
                'title' => $row['title'],
                'slug' => $row['slug'],
                'status' => $row['status'],
                'content' => $content,
                'updated_at' => $row['updated_at']
            ]);
        } else {
            $this->send_error('Page not found', 404);
        }
    }

    /**
     * POST: Save page content to tb4_pages
     * NOTE: This works with the tb4_pages schema created by Tb4Controller:
     *       id, title, slug, status, content (JSON), page_id (optional FK)
     *       The JS passes the row 'id' as 'page_id' parameter.
     */
    private function save_page_content(): void
    {
        // Use cached JSON input (php://input already read in constructor)
        $input = $this->json_input;

        // DEBUG: Detailed logging
        error_log('[TB4 API DEBUG] save_page_content() called');
        error_log('[TB4 API DEBUG] json_input variable: ' . var_export($input, true));
        error_log('[TB4 API DEBUG] json_input type: ' . gettype($input));
        error_log('[TB4 API DEBUG] json_input is null: ' . ($input === null ? 'YES' : 'NO'));
        error_log('[TB4 API DEBUG] json_input is false: ' . ($input === false ? 'YES' : 'NO'));
        error_log('[TB4 API DEBUG] json_input is empty: ' . (empty($input) ? 'YES' : 'NO'));

        if (is_array($input)) {
            error_log('[TB4 API DEBUG] input keys: ' . implode(', ', array_keys($input)));
            error_log('[TB4 API DEBUG] input has page_id: ' . (isset($input['page_id']) ? 'YES (' . $input['page_id'] . ')' : 'NO'));
            error_log('[TB4 API DEBUG] input has content: ' . (isset($input['content']) ? 'YES' : 'NO'));
            error_log('[TB4 API DEBUG] input has content key (array_key_exists): ' . (array_key_exists('content', $input) ? 'YES' : 'NO'));

            if (array_key_exists('content', $input)) {
                $content_val = $input['content'];
                error_log('[TB4 API DEBUG] content value type: ' . gettype($content_val));
                error_log('[TB4 API DEBUG] content is null: ' . ($content_val === null ? 'YES' : 'NO'));
                error_log('[TB4 API DEBUG] content is array: ' . (is_array($content_val) ? 'YES' : 'NO'));
                if (is_array($content_val)) {
                    error_log('[TB4 API DEBUG] content keys: ' . implode(', ', array_keys($content_val)));
                    error_log('[TB4 API DEBUG] content has sections: ' . (isset($content_val['sections']) ? 'YES' : 'NO'));
                    if (isset($content_val['sections'])) {
                        error_log('[TB4 API DEBUG] sections count: ' . count($content_val['sections']));
                    }
                }
                error_log('[TB4 API DEBUG] content JSON: ' . json_encode($content_val));
            }
        }

        if (!$input) {
            error_log('[TB4 API DEBUG] FAIL: input is falsy, returning error');
            $this->send_error('No JSON data received', 400);
        }

        // NOTE: JS sends row 'id' as 'page_id' - this is the tb4_pages.id
        $id = (int)($input['page_id'] ?? $this->get_int_param('page_id'));
        $content = $input['content'] ?? null;
        $title = $input['title'] ?? null;

        // DEBUG: More logging
        error_log('[TB4 API DEBUG] Extracted values:');
        error_log('[TB4 API DEBUG]   page_id = ' . $id);
        error_log('[TB4 API DEBUG]   content = ' . var_export($content, true));
        error_log('[TB4 API DEBUG]   content type = ' . gettype($content));
        error_log('[TB4 API DEBUG]   content === null: ' . ($content === null ? 'YES' : 'NO'));

        if (!$id) {
            error_log('[TB4 API DEBUG] FAIL: page_id is missing or zero');
            $this->send_error('page_id is required', 400);
        }

        // Content validation: must be array (even if empty sections)
        // Allow empty sections array: {"sections": []} is valid
        if ($content === null) {
            error_log('[TB4 API DEBUG] FAIL: content is null');
            $this->send_error('content is required', 400);
        }

        // If content is not an array, reject it
        if (!is_array($content)) {
            error_log('[TB4 API DEBUG] FAIL: content is not an array, type=' . gettype($content));
            $this->send_error('content must be an object', 400);
        }

        error_log('[TB4 API DEBUG] Validation passed, proceeding to save');

        // Encode content to JSON
        $content_json = json_encode($content, JSON_UNESCAPED_UNICODE);
        error_log('[TB4 API DEBUG] Encoded content JSON: ' . $content_json);

        try {
            // Check if page exists by id (not page_id column!)
            $stmt = $this->db->prepare("SELECT id, title FROM tb4_pages WHERE id = ?");
            $stmt->execute([$id]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$existing) {
                error_log('[TB4 API DEBUG] FAIL: Page not found with id=' . $id);
                $this->send_error('Page not found', 404);
            }

            error_log('[TB4 API DEBUG] Page found: ' . json_encode($existing));

            // Get optional status (draft/published)
            $status = $input['status'] ?? null;
            $valid_statuses = ['draft', 'published'];
            
            // Build dynamic UPDATE query
            $fields = ['content = ?', 'updated_at = NOW()'];
            $params = [$content_json];
            
            // Add title if provided
            if ($title !== null) {
                $fields[] = 'title = ?';
                $params[] = $title;
            }
            
            // Add status if provided and valid
            if ($status !== null && in_array($status, $valid_statuses)) {
                $fields[] = 'status = ?';
                $params[] = $status;
            }
            
            $params[] = $id; // WHERE id = ?
            $sql = "UPDATE tb4_pages SET " . implode(', ', $fields) . " WHERE id = ?";

            error_log('[TB4 API DEBUG] Executing SQL: ' . $sql);
            error_log('[TB4 API DEBUG] Params: ' . json_encode($params));

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            error_log('[TB4 API DEBUG] SUCCESS: Page content saved');

            $this->send_success([
                'id' => $id,
                'message' => 'Page content saved successfully'
            ]);

        } catch (\PDOException $e) {
            error_log('[TB4 API DEBUG] PDO Exception: ' . $e->getMessage());
            error_log('[TB4 API DEBUG] PDO Exception trace: ' . $e->getTraceAsString());
            $this->send_error('Database error while saving', 500);
        }
    }

    /**
     * POST: Save draft (auto-save without history)
     * NOTE: Uses tb4_pages.id as lookup (JS sends as 'page_id')
     */
    private function save_draft(): void
    {
        // Use cached JSON input (php://input already read in constructor)
        $input = $this->json_input;

        if (!$input) {
            $this->send_error('No JSON data received', 400);
        }

        // NOTE: JS sends row 'id' as 'page_id'
        $id = (int)($input['page_id'] ?? $this->get_int_param('page_id'));
        $content = $input['content'] ?? null;

        if (!$id) {
            $this->send_error('page_id is required', 400);
        }

        // Content validation: must be array (even if empty sections)
        if ($content === null || !is_array($content)) {
            $this->send_error('content is required', 400);
        }

        $content_json = json_encode($content, JSON_UNESCAPED_UNICODE);

        try {
            // Check if page exists by id
            $stmt = $this->db->prepare("SELECT id FROM tb4_pages WHERE id = ?");
            $stmt->execute([$id]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$existing) {
                $this->send_error('Page not found', 404);
            }

            // Update existing page (use 'content' column)
            $stmt = $this->db->prepare("UPDATE tb4_pages SET content = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$content_json, $id]);

            $this->send_success([
                'saved' => true,
                'message' => 'Draft saved'
            ]);

        } catch (\PDOException $e) {
            error_log('[TB4 API] save_draft error: ' . $e->getMessage());
            $this->send_error('Database error while saving draft', 500);
        }
    }

    /**
     * GET: Get list of all available modules
     */
    private function get_modules_list(): void
    {
        $registry = ModuleRegistry::getInstance();
        $modules = $registry->getModulesForJson();

        // Group by category
        $by_category = [];
        foreach ($modules as $slug => $module) {
            $category = $module['category'] ?? 'general';
            if (!isset($by_category[$category])) {
                $by_category[$category] = [];
            }
            $by_category[$category][$slug] = $module;
        }

        $this->send_success([
            'modules' => $modules,
            'by_category' => $by_category,
            'categories' => array_keys($by_category)
        ]);
    }

    /**
     * GET: Get fields for a specific module type
     */
    private function get_module_fields(): void
    {
        $module_type = $this->get_string_param('module_type');

        if (empty($module_type)) {
            $this->send_error('module_type is required', 400);
        }

        $registry = ModuleRegistry::getInstance();
        $module = $registry->getModule($module_type);

        if (!$module) {
            $this->send_error('Unknown module type: ' . htmlspecialchars($module_type, ENT_QUOTES, 'UTF-8'), 404);
        }

        $fields_by_tab = $module->get_fields_by_tab();

        $this->send_success([
            'module_type' => $module_type,
            'name' => $module->getName(),
            'icon' => $module->getIcon(),
            'category' => $module->getCategory(),
            'content_fields' => $fields_by_tab['content'] ?? [],
            'design_fields' => $fields_by_tab['design'] ?? [],
            'advanced_fields' => $fields_by_tab['advanced'] ?? [],
            'defaults' => $module->get_defaults()
        ]);
    }

    /**
     * POST: Render module preview HTML
     */
    private function render_module_preview(): void
    {
        $module_type = $this->get_string_param('module_type');
        $content = $_POST['content'] ?? '{}';
        $design = $_POST['design'] ?? '{}';
        $advanced = $_POST['advanced'] ?? '{}';

        if (empty($module_type)) {
            $this->send_error('module_type is required', 400);
        }

        // Parse JSON inputs
        $content_arr = json_decode($content, true) ?: [];
        $design_arr = json_decode($design, true) ?: [];
        $advanced_arr = json_decode($advanced, true) ?: [];

        $registry = ModuleRegistry::getInstance();
        $module = $registry->getModule($module_type);

        if (!$module) {
            $this->send_error('Unknown module type: ' . htmlspecialchars($module_type, ENT_QUOTES, 'UTF-8'), 404);
        }

        // Merge all settings
        $settings = array_merge($content_arr, $design_arr, $advanced_arr);
        $settings = $module->merge_with_defaults($settings);

        // Generate unique ID for preview
        $unique_id = 'tb4_preview_' . uniqid();

        // Render HTML
        $html = $module->render($settings);

        // Generate CSS
        $css = $module->generate_module_css($unique_id, $settings);

        // Wrap in preview container
        $preview_html = sprintf(
            '<div id="%s" class="tb4-module tb4-module--%s tb4-preview">%s</div>',
            htmlspecialchars($unique_id, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($module_type, ENT_QUOTES, 'UTF-8'),
            $html
        );

        $this->send_success([
            'html' => $preview_html,
            'css' => $css,
            'unique_id' => $unique_id
        ]);
    }

    /**
     * POST: Save content to library (tb4_layouts)
     */
    private function save_to_library(): void
    {
        $name = $this->get_string_param('name');
        $type = $this->get_string_param('type');
        $category = $this->get_string_param('category') ?: null;
        $content_json = $_POST['content_json'] ?? '';

        if (empty($name)) {
            $this->send_error('name is required', 400);
        }

        if (empty($type)) {
            $this->send_error('type is required', 400);
        }

        // Validate type
        $allowed_types = ['header', 'footer', 'section', 'row', 'full_page'];
        if (!in_array($type, $allowed_types, true)) {
            $this->send_error('Invalid type. Allowed: ' . implode(', ', $allowed_types), 400);
        }

        if (empty($content_json)) {
            $this->send_error('content_json is required', 400);
        }

        // Validate JSON
        $content = json_decode($content_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->send_error('Invalid JSON: ' . json_last_error_msg(), 400);
        }

        $content_json = json_encode($content, JSON_UNESCAPED_UNICODE);

        try {
            $stmt = $this->db->prepare("
                INSERT INTO tb4_layouts (name, type, category, content_json, created_by)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $type, $category, $content_json, $this->user_id]);

            $id = (int)$this->db->lastInsertId();

            $this->send_success([
                'id' => $id,
                'name' => $name,
                'type' => $type,
                'category' => $category,
                'message' => 'Saved to library successfully'
            ]);

        } catch (\PDOException $e) {
            error_log('[TB4 API] save_to_library error: ' . $e->getMessage());
            $this->send_error('Database error while saving to library', 500);
        }
    }

    /**
     * GET: Get library items from tb4_layouts
     */
    private function get_library_items(): void
    {
        $type = $this->get_string_param('type') ?: null;
        $category = $this->get_string_param('category') ?: null;

        $sql = "SELECT id, name, type, category, content_json, thumbnail, is_global, created_at
                FROM tb4_layouts WHERE 1=1";
        $params = [];

        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }

        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        $sql .= " ORDER BY name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Parse content_json for each item
        foreach ($items as &$item) {
            $item['id'] = (int)$item['id'];
            $item['is_global'] = (bool)$item['is_global'];
            $item['content'] = json_decode($item['content_json'], true) ?: [];
            unset($item['content_json']);
        }

        // Get available categories
        $stmt = $this->db->query("SELECT DISTINCT category FROM tb4_layouts WHERE category IS NOT NULL ORDER BY category");
        $categories = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $this->send_success([
            'items' => $items,
            'total' => count($items),
            'categories' => $categories
        ]);
    }

    /**
     * GET: Get a single library item by ID
     */
    private function get_library_item(): void
    {
        $id = $this->get_int_param('id');

        if (!$id) {
            $this->send_error('id is required', 400);
        }

        $stmt = $this->db->prepare("
            SELECT id, name, type, category, content_json, thumbnail, is_global, created_at
            FROM tb4_layouts
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $item = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$item) {
            $this->send_error('Layout not found', 404);
        }

        $item['id'] = (int)$item['id'];
        $item['is_global'] = (bool)$item['is_global'];

        $this->send_success([
            'item' => $item
        ]);
    }

    /**
     * GET: Get history entries for a page
     */
    private function get_history(): void
    {
        $page_id = $this->get_int_param('page_id');
        $limit = min($this->get_int_param('limit') ?: 50, 100);

        if (!$page_id) {
            $this->send_error('page_id is required', 400);
        }

        $stmt = $this->db->prepare("
            SELECT h.id, h.page_id, h.action_type, h.created_by, h.created_at,
                   u.username as created_by_name
            FROM tb4_history h
            LEFT JOIN admins u ON h.created_by = u.id
            WHERE h.page_id = ?
            ORDER BY h.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$page_id, $limit]);
        $history = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Cast IDs to integers
        foreach ($history as &$item) {
            $item['id'] = (int)$item['id'];
            $item['page_id'] = (int)$item['page_id'];
            $item['created_by'] = $item['created_by'] ? (int)$item['created_by'] : null;
        }

        $this->send_success([
            'history' => $history,
            'total' => count($history),
            'page_id' => $page_id
        ]);
    }

    /**
     * POST: Restore content from history
     */
    private function restore_history(): void
    {
        $history_id = $this->get_int_param('history_id');

        if (!$history_id) {
            $this->send_error('history_id is required', 400);
        }

        // Get history entry
        $stmt = $this->db->prepare("SELECT * FROM tb4_history WHERE id = ?");
        $stmt->execute([$history_id]);
        $history = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$history) {
            $this->send_error('History entry not found', 404);
        }

        $page_id = (int)$history['page_id'];
        $content_json = $history['content_json'];

        try {
            $this->db->beginTransaction();

            // Save current to history before restore
            $this->save_to_history($page_id, 'restore');

            // Check if page exists
            $stmt = $this->db->prepare("SELECT id, version FROM tb4_pages WHERE page_id = ?");
            $stmt->execute([$page_id]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($existing) {
                $new_version = (int)$existing['version'] + 1;
                $stmt = $this->db->prepare("
                    UPDATE tb4_pages
                    SET content_json = ?, css_cache = NULL, version = ?, updated_by = ?
                    WHERE page_id = ?
                ");
                $stmt->execute([$content_json, $new_version, $this->user_id, $page_id]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO tb4_pages (page_id, content_json, version, updated_by)
                    VALUES (?, ?, 1, ?)
                ");
                $stmt->execute([$page_id, $content_json, $this->user_id]);
                $new_version = 1;
            }

            $this->db->commit();

            $this->send_success([
                'page_id' => $page_id,
                'restored_from' => $history_id,
                'new_version' => $new_version,
                'message' => 'Content restored successfully'
            ]);

        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log('[TB4 API] restore_history error: ' . $e->getMessage());
            $this->send_error('Database error while restoring', 500);
        }
    }

    /**
     * Save current page content to history
     */
    private function save_to_history(int $page_id, string $action_type): void
    {
        // Get current content
        $stmt = $this->db->prepare("SELECT content_json FROM tb4_pages WHERE page_id = ?");
        $stmt->execute([$page_id]);
        $current = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($current) {
            $stmt = $this->db->prepare("
                INSERT INTO tb4_history (page_id, content_json, action_type, created_by)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $page_id,
                $current['content_json'],
                $action_type,
                $this->user_id
            ]);

            // Cleanup old history (keep last 100)
            $stmt = $this->db->prepare("
                DELETE FROM tb4_history
                WHERE page_id = ?
                AND id NOT IN (
                    SELECT id FROM (
                        SELECT id FROM tb4_history
                        WHERE page_id = ?
                        ORDER BY created_at DESC
                        LIMIT 100
                    ) as keep
                )
            ");
            $stmt->execute([$page_id, $page_id]);
        }
    }

    /**
     * Validate CSRF token for POST requests
     */
    private function validate_csrf(): void
    {
        // Check header first, then POST, then cached JSON body
        $sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';

        // If not in POST or header, try cached JSON body
        if (empty($sent) && $this->json_input) {
            $sent = $this->json_input['csrf_token'] ?? '';
        }

        $good = $_SESSION['csrf_token'] ?? '';

        if (!$good || !is_string($sent) || $sent === '' || !hash_equals($good, $sent)) {
            $this->send_error('CSRF verification failed', 403);
        }
    }

    /**
     * Get integer parameter from GET or POST
     */
    private function get_int_param(string $name): int
    {
        $value = $_GET[$name] ?? $_POST[$name] ?? 0;
        return (int)$value;
    }

    /**
     * Get string parameter from GET or POST (sanitized)
     */
    private function get_string_param(string $name): string
    {
        $value = $_GET[$name] ?? $_POST[$name] ?? '';
        return trim((string)$value);
    }

    /**
     * Send JSON success response
     */
    private function send_success(array $data): void
    {
        $this->send_json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * GET: Get posts/articles for Blog module
     * Returns published articles with featured images, categories, authors
     */
    private function get_posts(): void
    {
        $limit = $this->get_int_param('limit') ?: 10;
        $offset = $this->get_int_param('offset') ?: 0;
        $category_id = $this->get_int_param('category_id') ?: null;
        $order_by = $_GET['order_by'] ?? 'published_at';
        $order = strtoupper($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        // Validate order_by to prevent SQL injection
        $allowed_order = ['published_at', 'created_at', 'title', 'views'];
        if (!in_array($order_by, $allowed_order)) {
            $order_by = 'published_at';
        }

        // Build query
        $sql = "SELECT 
                    a.id,
                    a.slug,
                    a.title,
                    a.excerpt,
                    a.featured_image,
                    a.featured_image_alt,
                    a.status,
                    a.category_id,
                    a.author_id,
                    a.published_at,
                    a.created_at,
                    a.views,
                    c.name AS category_name,
                    c.slug AS category_slug,
                    u.name AS author_name
                FROM articles a
                LEFT JOIN article_categories c ON a.category_id = c.id
                LEFT JOIN users u ON a.author_id = u.id
                WHERE a.status = 'published'";
        
        $params = [];
        
        if ($category_id) {
            $sql .= " AND a.category_id = ?";
            $params[] = $category_id;
        }
        
        $sql .= " ORDER BY a.{$order_by} {$order}";
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get total count
            $count_sql = "SELECT COUNT(*) FROM articles WHERE status = 'published'";
            if ($category_id) {
                $count_sql .= " AND category_id = " . (int)$category_id;
            }
            $total = $this->db->query($count_sql)->fetchColumn();

            // Format dates and images
            foreach ($posts as &$post) {
                $post['published_date'] = $post['published_at'] 
                    ? date('M j, Y', strtotime($post['published_at']))
                    : date('M j, Y', strtotime($post['created_at']));
                
                // Ensure featured_image is full URL or null
                if (!empty($post['featured_image']) && !str_starts_with($post['featured_image'], 'http')) {
                    $post['featured_image'] = '/uploads/' . ltrim($post['featured_image'], '/');
                }
            }

            $this->send_success([
                'posts' => $posts,
                'total' => (int)$total,
                'limit' => $limit,
                'offset' => $offset
            ]);
        } catch (\PDOException $e) {
            error_log('[TB4 API] get_posts error: ' . $e->getMessage());
            $this->send_error('Failed to fetch posts', 500);
        }
    }

    /**
     * GET: Get article categories
     */
    private function get_categories(): void
    {
        try {
            $sql = "SELECT 
                        c.id,
                        c.name,
                        c.slug,
                        c.description,
                        COUNT(a.id) AS post_count
                    FROM article_categories c
                    LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published'
                    GROUP BY c.id
                    ORDER BY c.name ASC";
            
            $categories = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

            $this->send_success([
                'categories' => $categories
            ]);
        } catch (\PDOException $e) {
            error_log('[TB4 API] get_categories error: ' . $e->getMessage());
            $this->send_error('Failed to fetch categories', 500);
        }
    }

    /**
     * Send JSON error response
     */
    private function send_error(string $message, int $status_code = 400): void
    {
        http_response_code($status_code);
        $this->send_json([
            'success' => false,
            'error' => $message
        ]);
        exit;
    }

    /**
     * Send JSON response with proper headers
     */
    private function send_json(array $data): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=UTF-8');
            header('Cache-Control: no-cache, no-store, must-revalidate');
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
