<?php
require_once __DIR__ . '/../../core/csrf.php';
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * PluginsController - Handles plugin management in admin
 */
class PluginsController
{
    private $pluginService;
    private $viewPath;
    private $tenantId;

    public function __construct()
    {
        TenantSecurityMiddleware::enforceTenantHeader();
        $this->tenantId = $_SERVER['HTTP_X_TENANT_ID'];
        $this->pluginService = PluginService::getInstance();
        $this->viewPath = __DIR__ . '/../views/plugins/';
    }

    /**
     * List installed plugins
     */
    public function index(): void
    {
        $plugins = $this->pluginService->getInstalledPlugins($this->tenantId);
        $this->render('index.php', ['plugins' => $plugins]);
    }

    /**
     * Show plugin details
     */
    public function show(string $pluginId): void
    {
        $plugin = $this->pluginService->getPluginDetails(
            $pluginId,
            $this->tenantId
        );
        $this->render('show.php', ['plugin' => $plugin]);
    }

    /**
     * Install new plugin
     */
    public function install(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_validate_or_403();
            $cache = new \Core\Cache\SessionCacheAdapter(
                \Core\Cache\CacheFactory::make(),
                session_id()
            );
            $storedToken = $cache->get(session_id(), 'csrf_token') ?? '';
            if (empty($_POST['csrf_token']) || !hash_equals($storedToken, $_POST['csrf_token'])) {
                $this->render('install.php', ['error' => 'Invalid CSRF token']);
                return;
            }
            $result = $this->pluginService->installPlugin(
                $_POST['plugin_id'],
                $_POST['license_key'] ?? null,
                $this->tenantId
            );

            if ($result['success']) {
                $this->redirect('/admin/plugins');
            } else {
                $this->render('install.php', ['error' => $result['message']]);
            }
        } else {
            $availablePlugins = $this->pluginService->getAvailablePlugins();
            $this->render('install.php', ['plugins' => $availablePlugins]);
        }
    }

    /**
     * Update plugin settings
     */
    public function updateSettings(string $pluginId): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_validate_or_403();
            $cache = new \Core\Cache\SessionCacheAdapter(
                \Core\Cache\CacheFactory::make(),
                session_id()
            );
            $storedToken = $cache->get(session_id(), 'csrf_token') ?? '';
            if (empty($_POST['csrf_token']) || !hash_equals($storedToken, $_POST['csrf_token'])) {
                $this->redirect("/admin/plugins/{$pluginId}?error=invalid_csrf");
                return;
            }
            $settings = $_POST['settings'] ?? [];
            $this->pluginService->updatePluginSettings($pluginId, $settings);
            $this->redirect("/admin/plugins/{$pluginId}");
        }
    }

    /**
     * Uninstall plugin
     */
    public function uninstall(string $pluginId): void
    {
        $this->pluginService->uninstallPlugin($pluginId);
        $this->redirect('/admin/plugins');
    }

    /**
     * Render view template
     */
    private function render(string $view, array $data = []): void
    {
        $targetPath = $this->viewPath . $view;
        $base = realpath(__DIR__ . '/../views/plugins/');
        $resolved = realpath($targetPath);
        if ($base !== false && $resolved !== false && str_starts_with($resolved, $base . DIRECTORY_SEPARATOR) && is_file($resolved)) {
            extract($data);
            require_once $resolved;
        } else {
            http_response_code(400);
            error_log('Blocked invalid view path: ' . $view);
            exit;
        }
    }

    /**
     * Redirect to URL
     */
    private function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }
}
