<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Session;
use Core\Response;

class JtbController
{
    public function index(): void
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('/admin/login');
        }

        $pluginPath = CMS_ROOT . '/plugins/jessie-theme-builder';

        if (!file_exists($pluginPath . '/admin.php')) {
            echo '<h1>Error: Jessie Theme Builder plugin not found</h1>';
            return;
        }

        require_once $pluginPath . '/admin.php';
    }

    public function edit(int $id): void
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('/admin/login');
        }

        $pluginPath = CMS_ROOT . '/plugins/jessie-theme-builder';

        if (!file_exists($pluginPath . '/views/builder.php')) {
            echo '<h1>Error: Jessie Theme Builder plugin not found</h1>';
            return;
        }

        $_GET['post_id'] = $id;

        // Load JTB classes
        require_once $pluginPath . '/includes/class-jtb-element.php';
        require_once $pluginPath . '/includes/class-jtb-registry.php';
        require_once $pluginPath . '/includes/class-jtb-fields.php';
        require_once $pluginPath . '/includes/class-jtb-renderer.php';
        require_once $pluginPath . '/includes/class-jtb-settings.php';
        require_once $pluginPath . '/includes/class-jtb-builder.php';

        \JessieThemeBuilder\JTB_Registry::init();
        \JessieThemeBuilder\JTB_Fields::init();

        // Load modules
        $modulesPath = $pluginPath . '/modules';
        if (is_dir($modulesPath . '/structure')) {
            foreach (glob($modulesPath . '/structure/*.php') as $moduleFile) {
                require_once $moduleFile;
            }
        }
        if (is_dir($modulesPath . '/content')) {
            foreach (glob($modulesPath . '/content/*.php') as $moduleFile) {
                require_once $moduleFile;
            }
        }

        // Get page data
        $db = \core\Database::connection();
        $stmt = $db->prepare('SELECT id, title, slug FROM pages WHERE id = ?');
        $stmt->execute([$id]);
        $post = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$post) {
            Response::redirect('/admin/pages');
            return;
        }

        // Set variables for builder view
        $postId = $id;
        $postTitle = $post['title'];
        $postSlug = $post['slug'] ?? '';
        $csrfToken = csrf_token();

        // Define pluginUrl for builder.php (normally comes from plugin.php)
        $pluginUrl = '/plugins/jessie-theme-builder';

        require_once $pluginPath . '/views/builder.php';
    }
}
