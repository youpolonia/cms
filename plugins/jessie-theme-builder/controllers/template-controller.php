<?php
/**
 * Template Controller
 * Handles Theme Builder admin pages
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Global esc() fallback - define if CMS doesn't provide it
if (!function_exists('esc')) {
    function esc(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

class TemplateController
{
    private string $pluginPath;
    private string $pluginUrl;

    public function __construct()
    {
        $this->pluginPath = dirname(__DIR__);
        $this->pluginUrl = '/plugins/jessie-theme-builder';
    }

    /**
     * Load dependencies
     */
    private function loadDependencies(): void
    {
        require_once $this->pluginPath . '/includes/class-jtb-element.php';
        require_once $this->pluginPath . '/includes/class-jtb-registry.php';
        require_once $this->pluginPath . '/includes/class-jtb-fields.php';
        require_once $this->pluginPath . '/includes/class-jtb-fonts.php';
        require_once $this->pluginPath . '/includes/class-jtb-default-styles.php';
        require_once $this->pluginPath . '/includes/class-jtb-renderer.php';
        require_once $this->pluginPath . '/includes/class-jtb-css-output.php';
        require_once $this->pluginPath . '/includes/class-jtb-settings.php';
        require_once $this->pluginPath . '/includes/class-jtb-builder.php';
        require_once $this->pluginPath . '/includes/class-jtb-icons.php';
        require_once $this->pluginPath . '/includes/class-jtb-templates.php';
        require_once $this->pluginPath . '/includes/class-jtb-template-conditions.php';
        require_once $this->pluginPath . '/includes/class-jtb-global-modules.php';
        require_once $this->pluginPath . '/includes/class-jtb-dynamic-context.php';

        // Initialize registry
        JTB_Registry::init();
        JTB_Fields::init();

        // Load modules - include 'theme' category for theme builder modules
        $modulesPath = $this->pluginPath . '/modules';
        $moduleCategories = ['structure', 'content', 'interactive', 'media', 'forms', 'blog', 'fullwidth', 'theme'];

        foreach ($moduleCategories as $category) {
            $categoryPath = $modulesPath . '/' . $category;
            if (is_dir($categoryPath)) {
                foreach (glob($categoryPath . '/*.php') as $moduleFile) {
                    require_once $moduleFile;
                }
            }
        }
    }

    /**
     * Check authentication
     */
    private function checkAuth(): void
    {
        // Use CMS session boot (same as admin panel)
        require_once CMS_ROOT . '/core/session_boot.php';
        cms_session_start('admin');

        // Check for admin session (Jessie CMS uses admin_user_id or admin_id)
        if (!isset($_SESSION['admin_user_id']) && !isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
            header('Location: /admin/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }

        // Ensure CSRF token exists (same as CMS helper function)
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Get CSRF token
     */
    private function getCsrfToken(): string
    {
        return $_SESSION['csrf_token'] ?? '';
    }

    /**
     * Show template manager dashboard
     * Route: /admin/jtb/templates
     */
    public function index(): void
    {
        $this->checkAuth();
        $this->loadDependencies();

        $pluginUrl = $this->pluginUrl;
        $csrfToken = $this->getCsrfToken();
        $templates = JTB_Templates::getGroupedByType();
        $counts = JTB_Templates::getCountByType();

        require $this->pluginPath . '/views/template-manager.php';
    }

    /**
     * Show template editor
     * Route: /admin/jtb/template/edit/{id?}
     */
    public function edit(?int $templateId = null): void
    {
        $this->checkAuth();
        $this->loadDependencies();

        $pluginUrl = $this->pluginUrl;
        $csrfToken = $this->getCsrfToken();

        // Get template data
        $template = null;
        $content = JTB_Templates::getEmptyContent();
        $templateType = $_GET['type'] ?? 'header';

        if ($templateId) {
            $template = JTB_Templates::get($templateId);
            if (!$template) {
                http_response_code(404);
                echo 'Template not found';
                exit;
            }
            $content = $template['content'];
            $templateType = $template['type'];
        }

        // Get modules for sidebar
        $modules = [];
        foreach (JTB_Registry::getInstances() as $slug => $instance) {
            $modules[$slug] = [
                'slug' => $slug,
                'name' => $instance->getName(),
                'icon' => $instance->icon ?? 'box',
                'category' => $instance->category ?? 'content',
                'is_child' => $instance->is_child ?? false,
                'child_slug' => $instance->child_slug ?? null,
                'fields' => [
                    'content' => $instance->getContentFields(),
                    'design' => $instance->getDesignFields(),
                    'advanced' => $instance->getAdvancedFields()
                ]
            ];
        }

        // Get page types for conditions
        $pageTypes = JTB_Template_Conditions::getPageTypes();

        require $this->pluginPath . '/views/template-editor.php';
    }

    /**
     * Show global modules manager
     * Route: /admin/jtb/global-modules
     */
    public function globalModules(): void
    {
        $this->checkAuth();
        $this->loadDependencies();

        $pluginUrl = $this->pluginUrl;
        $csrfToken = $this->getCsrfToken();
        $modules = JTB_Global_Modules::getGroupedByType();
        $types = JTB_Global_Modules::getTypes();
        $count = JTB_Global_Modules::getCount();

        require $this->pluginPath . '/views/global-modules-manager.php';
    }

    /**
     * Show theme settings page
     * Route: /admin/jtb/theme-settings
     */
    public function themeSettings(): void
    {
        $this->checkAuth();
        $this->loadDependencies();

        // Load theme settings classes
        require_once $this->pluginPath . '/includes/class-jtb-theme-settings.php';
        require_once $this->pluginPath . '/includes/class-jtb-css-generator.php';
        require_once $this->pluginPath . '/includes/class-jtb-style-system.php';

        $pluginUrl = $this->pluginUrl;
        $csrfToken = $this->getCsrfToken();

        // Get all settings and defaults
        $settings = JTB_Theme_Settings::getAll();
        $defaults = JTB_Theme_Settings::getDefaults();

        // Group labels for navigation
        $groupLabels = [
            'colors' => 'Colors',
            'typography' => 'Typography',
            'layout' => 'Layout',
            'buttons' => 'Buttons',
            'forms' => 'Forms',
            'header' => 'Header',
            'menu' => 'Menu',
            'footer' => 'Footer',
            'blog' => 'Blog',
            'responsive' => 'Responsive'
        ];

        // Font options for select fields
        $fontOptions = JTB_Fonts::getFontOptions();

        require $this->pluginPath . '/views/theme-settings.php';
    }

    /**
     * Show AI Quality Dashboard (DEV_MODE only)
     * Route: /admin/jtb/quality-dashboard
     */
    public function qualityDashboard(): void
    {
        $this->checkAuth();

        // DEV_MODE gate
        if (!defined('DEV_MODE') || DEV_MODE !== true) {
            http_response_code(403);
            echo 'Access denied. This tool is only available in DEV_MODE.';
            exit;
        }

        // Include the dashboard file directly
        require $this->pluginPath . '/admin/tools/ai_quality_dashboard.php';
    }

    /**
     * Show template library browser
     * Route: /admin/jtb/library
     */
    public function library(): void
    {
        $this->checkAuth();
        $this->loadDependencies();

        // Load library class
        require_once $this->pluginPath . '/includes/class-jtb-library.php';

        // Ensure tables exist
        if (!JTB_Library::tablesExist()) {
            JTB_Library::createTables();
        }

        $pluginUrl = $this->pluginUrl;
        $csrfToken = $this->getCsrfToken();

        // Check if embed mode (opened from builder modal)
        $embedMode = isset($_GET['embed']) && $_GET['embed'] === '1';

        require $this->pluginPath . '/views/library-browser.php';
    }

    /**
     * Website Builder - Unified Theme Builder
     * Build entire website in one interface: header + footer + pages + templates
     * Route: /admin/website-builder
     */
    public function websiteBuilder(): void
    {
        $this->checkAuth();
        $this->loadDependencies();

        // Load additional required classes
        require_once $this->pluginPath . '/includes/class-jtb-theme-settings.php';

        $pluginUrl = $this->pluginUrl;
        $csrfToken = $this->getCsrfToken();

        // Get all headers
        $headers = JTB_Templates::getAll('header');

        // Get all footers
        $footers = JTB_Templates::getAll('footer');

        // Get all body templates
        $bodyTemplates = JTB_Templates::getAll('body');

        // Get theme settings
        $themeSettings = JTB_Theme_Settings::getAll();

        // Get pages from CMS
        $db = \core\Database::connection();
        $pages = $db->query("SELECT id, title, slug FROM pages WHERE status = 'published' ORDER BY title")->fetchAll(\PDO::FETCH_ASSOC);

        // Website meta
        $website = [
            'id' => 1,
            'name' => JTB_Dynamic_Context::getSiteTitle() ?: 'My Website'
        ];

        // Get all modules for the builder
        $modules = [];
        foreach (JTB_Registry::getInstances() as $slug => $instance) {
            $modules[$slug] = [
                'slug' => $slug,
                'name' => $instance->getName(),
                'icon' => $instance->icon ?? 'box',
                'category' => $instance->category ?? 'content',
                'is_child' => $instance->is_child ?? false,
                'child_slug' => $instance->child_slug ?? null,
                'fields' => [
                    'content' => $instance->getContentFields(),
                    'design' => $instance->getDesignFields(),
                    'advanced' => $instance->getAdvancedFields()
                ]
            ];
        }

        require $this->pluginPath . '/views/website-builder.php';
    }
}
