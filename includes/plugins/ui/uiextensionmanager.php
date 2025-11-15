<?php

namespace Includes\Plugins\UI;

use Includes\ErrorHandler;
use Includes\Config;
use Includes\Database\TenantContext;

require_once __DIR__ . '/../../FileCache.php'; // Added for \Includes\FileCache

class UIExtensionManager {
    private $extensions = [];
    private $tenantId;
    private $cache;

    public function __construct(?string $tenantId = null) {
        $this->tenantId = $tenantId ?? TenantContext::getCurrentTenantId();
        $this->cache = new \Includes\FileCache('ui_extensions');
        $this->loadExtensionPoints();
    }

    public function registerExtension(string $point, callable $callback, ?string $pluginName = null): void {
        if (!in_array($point, Config::get('plugins.ui.extension_points'))) {
            throw new \InvalidArgumentException("Invalid extension point: $point");
        }

        $this->extensions[$point][] = [
            'callback' => $callback,
            'plugin' => $pluginName
        ];
    }

    public function renderExtensions(string $point, array $context = []): string {
        if (!isset($this->extensions[$point])) {
            return '';
        }

        $output = '';
        foreach ($this->extensions[$point] as $extension) {
            try {
                $result = call_user_func($extension['callback'], $context);
                if (is_string($result)) {
                    $output .= $result;
                }
            } catch (\Throwable $e) {
                ErrorHandler::log("UI extension failed for {$extension['plugin']}: " . $e->getMessage());
            }
        }
        return $output;
    }

    public function getExtensionsForPoint(string $point): array {
        return $this->extensions[$point] ?? [];
    }

    private function loadExtensionPoints(): void {
        $cacheKey = $this->getCacheKey();
        if ($cached = $this->cache->get($cacheKey)) {
            $this->extensions = $cached;
            return;
        }

        // Load from plugins
        $pluginManager = \Includes\Plugins\PluginManager::getInstance();
        $plugins = $pluginManager->getActivePlugins();

        foreach ($plugins as $plugin) {
            $extensionFile = $plugin['path'] . '/ui_extensions.php';
            if (file_exists($extensionFile)) {
                $extensions = require_once $extensionFile;
                if (is_array($extensions)) {
                    foreach ($extensions as $point => $callbacks) {
                        foreach ((array)$callbacks as $callback) {
                            $this->registerExtension($point, $callback, $plugin['name']);
                        }
                    }
                }
            }
        }

        $this->cache->set($cacheKey, $this->extensions);
    }

    private function getCacheKey(): string {
        return "{$this->tenantId}_ui_extensions";
    }

    public function clearCache(): void {
        $this->cache->delete($this->getCacheKey());
    }
}
