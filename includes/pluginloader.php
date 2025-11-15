<?php
namespace Includes;

class PluginLoader {
    private $pluginManager;
    private $auditLogger;
    private $tenantManager;

    public function __construct(PluginManager $pluginManager, AuditLogger $auditLogger, TenantManager $tenantManager = null) {
        $this->pluginManager = $pluginManager;
        $this->auditLogger = $auditLogger;
        $this->tenantManager = $tenantManager;
    }

    public function loadPlugins() {
        $pluginDir = __DIR__ . '/../plugins';
        if (!is_dir($pluginDir)) {
            return;
        }

        foreach (glob($pluginDir . '/*/plugin.json') as $pluginConfig) {
            $pluginPath = dirname($pluginConfig);
            $pluginData = json_decode(file_get_contents($pluginConfig), true);
            
            if (!$this->validatePlugin($pluginData, $pluginPath)) {
                continue;
            }

            $this->registerHooks($pluginData, $pluginPath);
            $this->registerActivation($pluginData, $pluginPath);
        }
    }

    private function validatePlugin(array $pluginData, string $pluginPath): bool {
        if (!isset($pluginData['name'], $pluginData['version'])) {
            $this->auditLogger->log_action(0, 'PLUGIN_LOAD_FAILED', 
                "Missing required fields in $pluginPath/plugin.json");
            return false;
        }
        return true;
    }

    private function registerHooks(array $pluginData, string $pluginPath) {
        if (!isset($pluginData['hooks'])) {
            return;
        }

        $bootstrapFile = "$pluginPath/bootstrap.php";
        if (file_exists($bootstrapFile)) {
            $base = realpath($pluginPath);
            $target = realpath($bootstrapFile);
            if ($base === false || $target === false || substr_compare($target, $base . DIRECTORY_SEPARATOR, 0, strlen($base) + 1) !== 0 || !is_file($target)) {
                error_log("SECURITY: blocked dynamic include: plugin bootstrap");
                return;
            }
            $loader = require_once $target;
            $loader($this->pluginManager);
        } else {
            foreach ($pluginData['hooks']['action'] ?? [] as $action) {
                $this->pluginManager->addHook($action, function() use ($pluginData, $action) {
                    $this->auditLogger->log_action(0, "PLUGIN_ACTION_$action", 
                        "Plugin {$pluginData['name']} triggered action $action");
                });
            }

            foreach ($pluginData['hooks']['filter'] ?? [] as $filter) {
                $this->pluginManager->addFilter($filter, function($content) use ($pluginData, $filter) {
                    $this->auditLogger->log_action(0, "PLUGIN_FILTER_$filter", 
                        "Plugin {$pluginData['name']} applied filter $filter");
                    return $content;
                });
            }
        }
    }

    private function registerActivation(array $pluginData, string $pluginPath) {
        $this->pluginManager->addHook("activate_{$pluginData['name']}", function() use ($pluginData) {
            $this->auditLogger->log_action(0, 'PLUGIN_ACTIVATED', 
                "Plugin {$pluginData['name']} activated");
            
            if ($this->tenantManager) {
                $this->tenantManager->registerPlugin($pluginData['name']);
            }
        });
    }
}
