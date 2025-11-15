<?php

namespace Includes;

class DependencyContainer
{
    private static ?DependencyContainer $instance = null;
    private array $providers = [];

    private function __construct() {}

    public static function getInstance(): DependencyContainer
    {
        if (self::$instance === null) {
            self::$instance = new DependencyContainer();
            self::$instance->loadConfig();
        }
        return self::$instance;
    }

    public function register(string $key, callable $provider): void
    {
        $this->providers[$key] = $provider;
    }

    public function resolve(string $key)
    {
        if (!isset($this->providers[$key])) {
            throw new \InvalidArgumentException("No provider registered for $key");
        }
        return ($this->providers[$key])();
    }

    protected function loadConfig(): void
    {
        $configFiles = [
            __DIR__ . '/../config/services.php',
            __DIR__ . '/../config/providers.php'
        ];

        foreach ($configFiles as $file) {
            if (file_exists($file)) {
                $config = require_once $file;
                $config($this);
            }
        }
    }
}
