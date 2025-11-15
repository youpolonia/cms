<?php

namespace Core;

// Load core router from new location
require_once __DIR__ . '/../../core/router.php';

// ConfigLoader is in CMS\Config namespace and included by bootstrap.php
// ErrorHandler is in global namespace and included by bootstrap.php

use Core\Router;
use CMS\Config\ConfigLoader; // Corrected namespace
use ErrorHandler; // Corrected namespace (it's global)

class Application
{
    protected static $instance;
    protected $services = [];
    protected $router;
    protected $config;
    protected $errorHandler;

    private function __construct()
    {
        // Private constructor for singleton pattern
    }

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function bootstrap(): void
    {
        $this->loadConfig();
        $this->initErrorHandler();
        $this->initRouter();
    }

    protected function loadConfig(): void
    {
        // Use the registered 'config' service to ensure a single source of truth
        // This requires the 'config' service to be registered before $app->bootstrap() is called.
        // Which it is, in includes/bootstrap.php.
        $this->config = $this->get('config');
    }

    protected function initErrorHandler(): void
    {
        $this->errorHandler = new \ErrorHandler(); // ErrorHandler is global
        // ErrorHandler::register() is called in bootstrap.php, which sets debugMode.
        // Here, we might just want to ensure it's the same instance or re-configure if necessary.
        // For now, let's assume bootstrap.php's ErrorHandler setup is sufficient.
        // If Application needs its own ErrorHandler instance, it should be managed carefully.
        // The line below would re-register handlers if ErrorHandler was not a singleton.
        // ErrorHandler::register($this->config['debug'] ?? false);
        set_error_handler([$this->errorHandler, 'handleError']);
        set_exception_handler([$this->errorHandler, 'handleException']);
    }

    protected function initRouter(): void
    {
        $this->router = new Router($this->config['routes'] ?? []);
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function register(string $name, callable $factory): void
    {
        $this->services[$name] = $factory;
    }

    public function get(string $name)
    {
        if (!isset($this->services[$name])) {
            throw new \RuntimeException("Service {$name} not registered");
        }

        if (is_callable($this->services[$name])) {
            $this->services[$name] = call_user_func($this->services[$name], $this);
        }

        return $this->services[$name];
    }

    public function run(): void
    {
        $this->router->dispatch();
    }
}
