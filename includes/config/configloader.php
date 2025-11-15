<?php

declare(strict_types=1);

namespace Includes\Config;

require_once \CMS_ROOT . '/includes/filecache.php';

use ErrorHandler;
use FileCache;

class ConfigLoader implements ConfigInterface
{
    protected ConfigCache $cache;
    protected string $configDir;
    protected string $env;
    protected array $loaded = [];

    public function __construct(
        FileCache $fileCache,
        string $configDir = 'config',
        string $env = 'production'
    ) {
        $this->cache = new ConfigCache($fileCache, $env);
        $this->configDir = rtrim($configDir, '/');
        $this->env = $env;
    }

    public function get(string $key, $default = null)
    {
        $parts = explode('.', $key, 2);
        if (count($parts) < 2) {
            ErrorHandler::handleError(
                E_WARNING,
                "Invalid config key format: {$key}",
                __FILE__,
                __LINE__
            );
            return $default;
        }

        [$file, $key] = $parts;
        $config = $this->loadFile($file);

        return $config[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        $parts = explode('.', $key, 2);
        if (count($parts) < 2) {
            return false;
        }

        [$file, $key] = $parts;
        $config = $this->loadFile($file);
        
        return isset($config[$key]);
    }

    public function all(): array
    {
        return $this->loaded;
    }

    public function set(string $key, $value, ?int $ttl = null): void
    {
        $parts = explode('.', $key, 2);
        if (count($parts) < 2) {
            ErrorHandler::handleError(
                E_WARNING,
                "Invalid config key format: {$key}",
                __FILE__,
                __LINE__
            );
            return;
        }

        [$file, $key] = $parts;
        $config = $this->loadFile($file);
        $config[$key] = $value;
        $this->cache->set($file, $config, $ttl);
        $this->loaded[$file] = $config;
    }

    public function clear(string $key): void
    {
        $parts = explode('.', $key, 2);
        if (count($parts) < 2) {
            ErrorHandler::handleError(
                E_WARNING,
                "Invalid config key format: {$key}",
                __FILE__,
                __LINE__
            );
            return;
        }

        [$file, $key] = $parts;
        $config = $this->loadFile($file);
        unset($config[$key]);
        $this->cache->set($file, $config);
        $this->loaded[$file] = $config;
    }

    protected function loadFile(string $file): array
    {
        if (isset($this->loaded[$file])) {
            return $this->loaded[$file];
        }

        $config = $this->cache->get($file);
        if ($config === null) {
            $config = $this->loadFromDisk($file);
            $this->cache->set($file, $config);
        }

        $this->loaded[$file] = $config;
        return $config;
    }

    protected function loadFromDisk(string $file): array
    {
        $path = "{$this->configDir}/{$file}.php";
        if (!file_exists($path)) {
            return [];
        }

        return require_once $path;
    }
}
