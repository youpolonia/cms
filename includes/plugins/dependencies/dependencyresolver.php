<?php

namespace Includes\Plugins\Dependencies;

require_once __DIR__ . '/../../errorhandler.php';
require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../database/tenantcontext.php';
require_once __DIR__ . '/../../FileCache.php';
require_once __DIR__ . '/../pluginmanager.php';
use Includes\ErrorHandler;
use Includes\Config;
use Includes\Database\TenantContext;

class DependencyResolver {
    private $dependencies = [];
    private $tenantId;
    private $cache;

    public function __construct(?string $tenantId = null) {
        $this->tenantId = $tenantId ?? TenantContext::getCurrentTenantId();
        $this->cache = new \Includes\FileCache('plugin_dependencies');
    }

    public function resolve(array $pluginManifest): bool {
        if (empty($pluginManifest['dependencies'])) {
            return true;
        }

        $deps = $this->normalizeDependencies($pluginManifest['dependencies']);
        $this->dependencies = $deps;

        // Check if already resolved in cache
        $cacheKey = $this->getCacheKey($pluginManifest['name']);
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached['status'];
        }

        $result = $this->checkDependencies($deps);
        $this->cache->set($cacheKey, [
            'status' => $result,
            'dependencies' => $deps,
            'timestamp' => time()
        ]);

        return $result;
    }

    private function normalizeDependencies($deps): array {
        if (is_string($deps)) {
            return [$deps => '*'];
        }

        $normalized = [];
        foreach ($deps as $name => $constraint) {
            if (is_numeric($name)) {
                $normalized[$constraint] = '*';
            } else {
                $normalized[$name] = $constraint;
            }
        }
        return $normalized;
    }

    private function checkDependencies(array $deps): bool {
        $pluginManager = \Includes\Plugins\PluginManager::getInstance();
        $allPlugins = $pluginManager->getAllPlugins();

        foreach ($deps as $name => $constraint) {
            if (!isset($allPlugins[$name])) {
                ErrorHandler::log("Missing required plugin: $name");
                return false;
            }

            if (!$this->versionMatches($allPlugins[$name]['version'], $constraint)) {
                ErrorHandler::log("Version mismatch for plugin $name: requires $constraint, found {$allPlugins[$name]['version']}");
                return false;
            }
        }

        return true;
    }

    private function versionMatches(string $version, string $constraint): bool {
        if ($constraint === '*') {
            return true;
        }

        // Simple version comparison (TODO: implement proper semver)
        return version_compare($version, $constraint, '>=');
    }

    private function getCacheKey(string $pluginName): string {
        return "{$this->tenantId}_{$pluginName}_deps";
    }

    public function getDependencies(): array {
        return $this->dependencies;
    }

    public function clearCache(string $pluginName): void {
        $this->cache->delete($this->getCacheKey($pluginName));
    }
}
