<?php
/**
 * MVC Controllers Test
 * Verifies all admin controllers can be loaded and instantiated
 */

require_once __DIR__ . '/TestRunner.php';

if (!defined('CMS_ROOT')) define('CMS_ROOT', realpath(__DIR__ . '/..'));
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/core/request.php';
require_once CMS_ROOT . '/core/response.php';
require_once CMS_ROOT . '/core/session.php';

// Register autoloader for App\Controllers\Admin namespace
spl_autoload_register(function ($class) {
    $prefix = 'App\\Controllers\\Admin\\';
    if (strpos($class, $prefix) === 0) {
        $relative = strtolower(substr($class, strlen($prefix)));
        $file = CMS_ROOT . '/app/controllers/admin/' . $relative . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

$runner = new TestRunner();

// List of all expected controllers
$controllers = [
    'AnalyticsController', 'ArticlesController', 'AuthController',
    'AutomationRulesController', 'AutomationsController', 'BackupController',
    'CategoriesController', 'CommentsController', 'ContentController',
    'DashboardController', 'EmailCampaignsController',
    'ExtensionsController', 'GalleriesController',
    'JtbController', 'LogsController',
    'MaintenanceController', 'MediaController', 'MenusController',
    'MigrationsController', 'ModulesController',
    'NotificationsController', 'PagesController', 'PluginsController',
    'ProfileController', 'SchedulerController', 'SearchController',
    'SecurityDashboardController',
    'ThemeStudioController', 'ThemesController', 'UrlsController', 'UsersController',
    'VersionControlController', 'WidgetsController',
];

$runner->addTest('All admin controller files exist', function () use ($controllers) {
    $missing = [];
    foreach ($controllers as $name) {
        $file = CMS_ROOT . '/app/controllers/admin/' . strtolower($name) . '.php';
        if (!file_exists($file)) {
            $missing[] = $name;
        }
    }
    TestRunner::assert(empty($missing), 'Missing controllers: ' . implode(', ', $missing));
});

$runner->addTest('Controllers can be loaded', function () use ($controllers) {
    $failed = [];
    foreach ($controllers as $name) {
        $file = CMS_ROOT . '/app/controllers/admin/' . strtolower($name) . '.php';
        if (file_exists($file)) {
            try {
                require_once $file;
            } catch (\Throwable $e) {
                $failed[] = "$name: " . $e->getMessage();
            }
        }
    }
    TestRunner::assert(empty($failed), 'Failed to load: ' . implode('; ', $failed));
});

$runner->addTest('DashboardController has index method', function () {
    $class = 'App\\Controllers\\Admin\\DashboardController';
    TestRunner::assert(class_exists($class), "Class $class should exist");
    TestRunner::assert(method_exists($class, 'index'), 'Should have index method');
});

$runner->addTest('ArticlesController has CRUD methods', function () {
    $class = 'App\\Controllers\\Admin\\ArticlesController';
    TestRunner::assert(class_exists($class), "Class $class should exist");
    foreach (['index', 'create', 'store', 'edit', 'update', 'destroy'] as $method) {
        TestRunner::assert(method_exists($class, $method), "Should have $method method");
    }
});

$runner->run();
