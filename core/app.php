<?php
declare(strict_types=1);

namespace Core;

class App
{
    private static ?App $instance = null;

    public function __construct()
    {
        self::$instance = $this;
        $this->loadHelpers();
    }

    public static function getInstance(): ?App
    {
        return self::$instance;
    }

    private function loadHelpers(): void
    {
        require_once CMS_APP . '/helpers/functions.php';
    }

    public function run(): void
    {
        try {
            require_once CMS_CORE . '/session.php';
            Session::start();

            require_once CMS_CORE . '/router.php';
            require_once CMS_CORE . '/request.php';
            require_once CMS_CORE . '/response.php';
            require_once CMS_CORE . '/controllerregistry.php';

            // Simple autoloader for Admin controllers
            spl_autoload_register(function($class) {
                // Skip if class is null or not a string
                if (!is_string($class) || $class === '') {
                    return;
                }

                // Admin\SomeController -> app/controllers/admin/somecontroller.php
                if (str_starts_with($class, 'Admin\\')) {
                    $controllerName = substr($class, 6); // Remove "Admin\"
                    $file = CMS_APP . '/controllers/admin/' . strtolower($controllerName) . '.php';
                    if (file_exists($file)) {
                        require_once $file;
                        // Class uses different namespace - alias it
                        if (class_exists('App\\Controllers\\Admin\\' . $controllerName)) {
                            class_alias('App\\Controllers\\Admin\\' . $controllerName, $class);
                        }
                    }
                }
                
                // Front\SomeController -> app/controllers/front/somecontroller.php
                if (str_starts_with($class, 'Front\\')) {
                    $controllerName = substr($class, 6); // Remove "Front\"
                    $file = CMS_APP . '/controllers/front/' . strtolower($controllerName) . '.php';
                    if (file_exists($file)) {
                        require_once $file;
                        // Class uses different namespace - alias it
                        if (class_exists('App\\Controllers\\Front\\' . $controllerName)) {
                            class_alias('App\\Controllers\\Front\\' . $controllerName, $class);
                        }
                    }
                }
            });

            // Load routes from config file
            $routes = require CMS_CONFIG . '/routes.php';
            
            // Register routes with Router
            if (is_array($routes)) {
                foreach ($routes as $key => $handler) {
                    // Parse "METHOD /path" format
                    $parts = explode(' ', $key, 2);
                    if (count($parts) === 2) {
                        $method = $parts[0];
                        $path = $parts[1];
                        
                        // Extract middleware options if present
                        $options = [];
                        if (is_array($handler) && count($handler) === 3) {
                            $options = $handler[2];
                            $handler = [$handler[0], $handler[1]];
                        }
                        
                        Router::addRoute($method, $path, $handler);
                    }
                }
            }

            // Dispatch request
            Router::dispatch();

        } catch (\Throwable $e) {
            $this->handleError($e);
        }
    }

    private function handleError(\Throwable $e): void
    {
        if (defined('DEV_MODE') && DEV_MODE === true) {
            echo '<h1>Error</h1>';
            echo '<p><strong>' . htmlspecialchars($e->getMessage()) . '</strong></p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        } else {
            http_response_code(500);
            echo '<h1>500 Internal Server Error</h1>';
            error_log('[CMS] ' . $e->getMessage());
        }
    }
}
