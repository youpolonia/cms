<?php
/**
 * Jessie Theme Builder
 * Visual page builder with Divi-style interface
 *
 * @package JessieThemeBuilder
 * @version 1.0.0
 */

namespace JessieThemeBuilder;

// Define CMS_ROOT if not already defined
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', realpath(__DIR__ . '/../..'));
}

// Global esc() fallback - define if CMS doesn't provide it
if (!function_exists('esc')) {
    function esc(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

class JessieThemeBuilderPlugin implements \EnhancedPluginInterface
{
    private $hookManager;
    private static ?self $instance = null;

    public const VERSION = '1.0.0';
    public const DB_VERSION = '1.0';

    public function __construct(\CMS\Plugins\HookManager $hookManager)
    {
        $this->hookManager = $hookManager;
        self::$instance = $this;
    }

    // ========================================
    // PluginInterface Methods
    // ========================================

    public function getMetadata(): array
    {
        return [
            'name' => 'Jessie Theme Builder',
            'version' => self::VERSION,
            'author' => 'Jessie CMS',
            'description' => 'Visual page builder with Divi-style interface'
        ];
    }

    public function init(): void
    {
        $this->loadDependencies();
        JTB_Registry::init();
        JTB_Fields::init();
        $this->loadModules();
    }

    public function registerHooks(): void
    {
        $this->hookManager->addAction('admin_menu', [$this, 'registerAdminMenu']);
        $this->hookManager->addAction('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        $this->hookManager->addFilter('the_content', [$this, 'filterContent']);

        // Theme Builder frontend hooks
        $this->hookManager->addAction('template_redirect', [$this, 'initThemeBuilder']);
        $this->hookManager->addAction('wp_head', [$this, 'outputThemeBuilderCss']);
        $this->hookManager->addAction('get_header', [$this, 'maybeRenderHeader']);
        $this->hookManager->addAction('get_footer', [$this, 'maybeRenderFooter']);

        // AJAX handlers
        $this->hookManager->addAction('wp_ajax_jtb_save', [$this, 'ajaxSave']);
        $this->hookManager->addAction('wp_ajax_jtb_load', [$this, 'ajaxLoad']);
        $this->hookManager->addAction('wp_ajax_jtb_render', [$this, 'ajaxRender']);
        $this->hookManager->addAction('wp_ajax_jtb_modules', [$this, 'ajaxModules']);
    }

    // ========================================
    // EnhancedPluginInterface Methods
    // ========================================

    public function getDependencies(): array
    {
        return [];
    }

    public function getVersionCompatibility(): array
    {
        return ['1.0.0', ''];
    }

    public function install(): void
    {
        $this->createTables();
    }

    public function activate(): void
    {
        // Nothing to do on activation
    }

    public function deactivate(): void
    {
        // Nothing to do on deactivation
    }

    public function uninstall(): void
    {
        $this->dropTables();
    }

    public function getHookPoints(): array
    {
        return [
            'jtb_before_render' => 'Before rendering JTB content',
            'jtb_after_render' => 'After rendering JTB content',
            'jtb_before_save' => 'Before saving JTB content',
            'jtb_after_save' => 'After saving JTB content',
            'jtb_register_modules' => 'When registering JTB modules',
            'jtb_before_header' => 'Before rendering JTB header template',
            'jtb_after_header' => 'After rendering JTB header template',
            'jtb_before_footer' => 'Before rendering JTB footer template',
            'jtb_after_footer' => 'After rendering JTB footer template',
            'jtb_before_body' => 'Before rendering JTB body template',
            'jtb_after_body' => 'After rendering JTB body template'
        ];
    }

    // ========================================
    // Private Methods
    // ========================================

    private function loadDependencies(): void
    {
        $includesPath = __DIR__ . '/includes';

        // Core classes
        require_once $includesPath . '/class-jtb-element.php';
        require_once $includesPath . '/class-jtb-registry.php';
        require_once $includesPath . '/class-jtb-fields.php';
        require_once $includesPath . '/class-jtb-fonts.php';
        require_once $includesPath . '/class-jtb-default-styles.php';
        require_once $includesPath . '/class-jtb-renderer.php';
        require_once $includesPath . '/class-jtb-css-output.php';
        require_once $includesPath . '/class-jtb-style-system.php';
        require_once $includesPath . '/class-jtb-settings.php';
        require_once $includesPath . '/class-jtb-builder.php';
        require_once $includesPath . '/class-jtb-icons.php';

        // Theme Builder classes
        require_once $includesPath . '/class-jtb-templates.php';
        require_once $includesPath . '/class-jtb-template-conditions.php';
        require_once $includesPath . '/class-jtb-global-modules.php';
        require_once $includesPath . '/class-jtb-template-matcher.php';
        require_once $includesPath . '/class-jtb-theme-integration.php';
    }

    private function loadModules(): void
    {
        $modulesPath = __DIR__ . '/modules';

        // Load structure modules
        $structurePath = $modulesPath . '/structure';
        if (is_dir($structurePath)) {
            foreach (glob($structurePath . '/*.php') as $moduleFile) {
                require_once $moduleFile;
            }
        }

        // Load content modules
        $contentPath = $modulesPath . '/content';
        if (is_dir($contentPath)) {
            foreach (glob($contentPath . '/*.php') as $moduleFile) {
                require_once $moduleFile;
            }
        }
    }

    private function createTables(): void
    {
        $db = \core\Database::connection();

        // jtb_pages table
        $db->exec("
            CREATE TABLE IF NOT EXISTS jtb_pages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                post_id INT NOT NULL UNIQUE,
                content JSON NOT NULL,
                css_cache TEXT,
                version VARCHAR(10) DEFAULT '1.0',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_post_id (post_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // jtb_templates table (Theme Builder)
        $db->exec("
            CREATE TABLE IF NOT EXISTS jtb_templates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                type ENUM('header', 'footer', 'body') NOT NULL,
                content JSON NOT NULL,
                css_cache TEXT,
                is_default TINYINT(1) DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                priority INT DEFAULT 10,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_type (type),
                INDEX idx_default (is_default),
                INDEX idx_active (is_active),
                INDEX idx_priority (priority)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // jtb_template_conditions table (Theme Builder)
        $db->exec("
            CREATE TABLE IF NOT EXISTS jtb_template_conditions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                template_id INT NOT NULL,
                condition_type ENUM('include', 'exclude') NOT NULL DEFAULT 'include',
                page_type VARCHAR(50) NOT NULL,
                object_id INT DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_template (template_id),
                INDEX idx_page_type (page_type),
                FOREIGN KEY (template_id) REFERENCES jtb_templates(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // jtb_global_modules table
        $db->exec("
            CREATE TABLE IF NOT EXISTS jtb_global_modules (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                type VARCHAR(100) NOT NULL,
                content JSON NOT NULL,
                description TEXT,
                thumbnail VARCHAR(500),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_type (type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private function dropTables(): void
    {
        $db = \core\Database::connection();

        // Drop in correct order due to foreign keys
        $db->exec("DROP TABLE IF EXISTS jtb_template_conditions");
        $db->exec("DROP TABLE IF EXISTS jtb_templates");
        $db->exec("DROP TABLE IF EXISTS jtb_global_modules");
        $db->exec("DROP TABLE IF EXISTS jtb_pages");
    }

    // ========================================
    // Static Helpers
    // ========================================

    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    public static function pluginPath(): string
    {
        return __DIR__;
    }

    public static function pluginUrl(): string
    {
        return '/plugins/jessie-theme-builder';
    }

    // ========================================
    // Hook Callbacks
    // ========================================

    public function registerAdminMenu(): void
    {
        // Register admin menu for Theme Builder
        // This hook adds menu items to the CMS admin sidebar

        // The CMS admin system expects menu items in this format:
        // Each item has: title, url, icon (optional), children (optional)

        $menuItems = [
            [
                'title' => 'Theme Builder',
                'url' => '/admin/jtb/templates',
                'icon' => 'layers',
                'permission' => 'manage_themes',
                'children' => [
                    [
                        'title' => 'Templates',
                        'url' => '/admin/jtb/templates',
                        'icon' => 'layout'
                    ],
                    [
                        'title' => 'Library',
                        'url' => '/admin/jtb/library',
                        'icon' => 'book-open'
                    ],
                    [
                        'title' => 'Global Modules',
                        'url' => '/admin/jtb/global-modules',
                        'icon' => 'package'
                    ],
                    [
                        'title' => 'Theme Settings',
                        'url' => '/admin/jtb/theme-settings',
                        'icon' => 'settings'
                    ]
                ]
            ]
        ];

        // Register with CMS admin menu system via hook
        $this->hookManager->doAction('admin_menu_register', $menuItems);
    }

    public function enqueueAdminAssets(): void
    {
        // Enqueue CSS and JS for admin pages
        // This will be called on admin pages
    }

    public function filterContent(string $content): string
    {
        global $post;

        if (!isset($post) || !isset($post->id)) {
            return $content;
        }

        $postId = (int) $post->id;

        if (!JTB_Builder::hasContent($postId)) {
            return $content;
        }

        $jtbContent = JTB_Builder::getContent($postId);

        if (!$jtbContent) {
            return $content;
        }

        $this->hookManager->doAction('jtb_before_render', $postId, $jtbContent);

        $rendered = JTB_Renderer::render($jtbContent);

        $this->hookManager->doAction('jtb_after_render', $postId, $rendered);

        return $rendered;
    }

    public function ajaxSave(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Invalid request method');
            return;
        }

        csrf_validate_or_403();

        if (!\Core\Session::isLoggedIn()) {
            $this->jsonError('Unauthorized');
            return;
        }

        $postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
        $content = isset($_POST['content']) ? $_POST['content'] : '';

        if ($postId <= 0) {
            $this->jsonError('Invalid post ID');
            return;
        }

        if (empty($content)) {
            $this->jsonError('Content is required');
            return;
        }

        $contentArray = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->jsonError('Invalid JSON content');
            return;
        }

        $this->hookManager->doAction('jtb_before_save', $postId, $contentArray);

        $result = JTB_Builder::saveContent($postId, $contentArray);

        if ($result) {
            $this->hookManager->doAction('jtb_after_save', $postId, $contentArray);
            $this->jsonSuccess([
                'message' => 'Content saved successfully',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } else {
            $this->jsonError('Failed to save content');
        }
    }

    public function ajaxLoad(): void
    {
        if (!\Core\Session::isLoggedIn()) {
            $this->jsonError('Unauthorized');
            return;
        }

        $postId = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;

        if ($postId <= 0) {
            $this->jsonError('Invalid post ID');
            return;
        }

        $content = JTB_Builder::getContent($postId);

        if ($content === null) {
            $content = JTB_Builder::getEmptyContent();
        }

        // Get post title
        $db = \core\Database::connection();
        $stmt = $db->prepare("SELECT title FROM posts WHERE id = ?");
        $stmt->execute([$postId]);
        $post = $stmt->fetch(\PDO::FETCH_ASSOC);

        $postTitle = $post ? $post['title'] : 'Untitled';

        // Get CSS cache
        $stmt = $db->prepare("SELECT css_cache FROM jtb_pages WHERE post_id = ?");
        $stmt->execute([$postId]);
        $jtbPage = $stmt->fetch(\PDO::FETCH_ASSOC);

        $cssCache = $jtbPage ? $jtbPage['css_cache'] : '';

        $this->jsonSuccess([
            'post_id' => $postId,
            'post_title' => $postTitle,
            'content' => $content,
            'css_cache' => $cssCache,
            'has_content' => JTB_Builder::hasContent($postId)
        ]);
    }

    public function ajaxRender(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Invalid request method');
            return;
        }

        if (!\Core\Session::isLoggedIn()) {
            $this->jsonError('Unauthorized');
            return;
        }

        $content = isset($_POST['content']) ? $_POST['content'] : '';

        if (empty($content)) {
            $this->jsonError('Content is required');
            return;
        }

        $contentArray = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->jsonError('Invalid JSON content');
            return;
        }

        if (!JTB_Builder::validateContent($contentArray)) {
            $this->jsonError('Invalid content structure');
            return;
        }

        $html = JTB_Renderer::render($contentArray);
        $css = JTB_Renderer::getCss();

        $this->jsonSuccess([
            'html' => $html,
            'css' => $css
        ]);
    }

    public function ajaxModules(): void
    {
        if (!\Core\Session::isLoggedIn()) {
            $this->jsonError('Unauthorized');
            return;
        }

        $modules = [];

        foreach (JTB_Registry::getInstances() as $slug => $module) {
            $modules[$slug] = [
                'slug' => $module->getSlug(),
                'name' => $module->getName(),
                'icon' => $module->icon,
                'category' => $module->category,
                'is_child' => $module->is_child,
                'child_slug' => $module->child_slug,
                'fields' => [
                    'content' => $module->getContentFields(),
                    'design' => $module->getDesignFields(),
                    'advanced' => $module->getAdvancedFields()
                ]
            ];
        }

        $this->jsonSuccess([
            'modules' => $modules,
            'categories' => JTB_Registry::getCategories(),
            'count' => JTB_Registry::count()
        ]);
    }

    // ========================================
    // Theme Builder Callbacks
    // ========================================

    /**
     * Initialize Theme Builder on frontend
     */
    public function initThemeBuilder(): void
    {
        // Initialize theme integration for the current request
        JTB_Theme_Integration::init();
    }

    /**
     * Output Theme Builder CSS in head
     */
    public function outputThemeBuilderCss(): void
    {
        JTB_Theme_Integration::outputCss();
    }

    /**
     * Maybe render JTB header instead of theme header
     */
    public function maybeRenderHeader(): bool
    {
        if (JTB_Theme_Integration::hasHeader()) {
            $this->hookManager->doAction('jtb_before_header');
            echo JTB_Theme_Integration::renderHeader();
            $this->hookManager->doAction('jtb_after_header');
            return true;
        }
        return false;
    }

    /**
     * Maybe render JTB footer instead of theme footer
     */
    public function maybeRenderFooter(): bool
    {
        if (JTB_Theme_Integration::hasFooter()) {
            $this->hookManager->doAction('jtb_before_footer');
            echo JTB_Theme_Integration::renderFooter();
            $this->hookManager->doAction('jtb_after_footer');
            return true;
        }
        return false;
    }

    /**
     * Render body with JTB template
     */
    public function renderBodyTemplate(string $content): string
    {
        if (JTB_Theme_Integration::hasBody()) {
            $this->hookManager->doAction('jtb_before_body', $content);
            $rendered = JTB_Theme_Integration::renderBody($content);
            $this->hookManager->doAction('jtb_after_body', $rendered);
            return $rendered;
        }
        return $content;
    }

    /**
     * Check if JTB should override the template
     */
    public function shouldOverrideTemplate(): bool
    {
        return JTB_Theme_Integration::shouldOverride();
    }

    /**
     * Get JTB full page (for complete template override)
     */
    public function getFullPage(string $content = '', string $title = ''): string
    {
        return JTB_Theme_Integration::getFullPage($content, $title);
    }

    // ========================================
    // JSON Helper Methods
    // ========================================

    private function jsonSuccess($data): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit;
    }

    private function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit;
    }
}

return new JessieThemeBuilderPlugin(\CMS\Plugins\PluginLoader::getHookManager());
