<?php

namespace Includes\Plugins;

require_once CMS_ROOT . '/includes/plugins/pluginmanager.php';
require_once CMS_ROOT . '/includes/errorhandler.php'; // Assuming ErrorHandler is in includes/
class HookManager {
    private static $instance;
    private $hooks = [];
    private $pluginManager;

    private function __construct() {
        $this->pluginManager = PluginManager::getInstance();
    }

    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function addHook(string $hookName, callable $callback, string $type = 'after', int $priority = null): void {
        $priority = $priority ?? config('plugins.hooks.priority.' . $type);
        
        $this->hooks[$hookName][$type][$priority][] = [
            'callback' => $callback,
            'plugin' => $this->getCallingPlugin()
        ];
    }

    public function trigger(string $hookName, array $args = []): array {
        $results = [];
        
        if (!isset($this->hooks[$hookName])) {
            return $results;
        }

        // Process before hooks (higher priority first)
        if (isset($this->hooks[$hookName]['before'])) {
            krsort($this->hooks[$hookName]['before']);
            foreach ($this->hooks[$hookName]['before'] as $priority => $hooks) {
                foreach ($hooks as $hook) {
                    if ($this->pluginManager->isActive($hook['plugin'])) {
                        $results[] = $this->executeHook($hook['callback'], $args);
                    }
                }
            }
        }

        // Process after hooks (lower priority first)
        if (isset($this->hooks[$hookName]['after'])) {
            ksort($this->hooks[$hookName]['after']);
            foreach ($this->hooks[$hookName]['after'] as $priority => $hooks) {
                foreach ($hooks as $hook) {
                    if ($this->pluginManager->isActive($hook['plugin'])) {
                        $results[] = $this->executeHook($hook['callback'], $args);
                    }
                }
            }
        }

        return $results;
    }

    private function executeHook(callable $callback, array $args) {
        try {
            return call_user_func_array($callback, $args);
        } catch (\Throwable $e) {
            ErrorHandler::log("Hook execution failed: " . $e->getMessage());
            return null;
        }
    }

    private function getCallingPlugin(): ?string {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        if (isset($backtrace[2]['class'])) {
            $class = $backtrace[2]['class'];
            if (strpos($class, 'Plugins\\') === 0) {
                return explode('\\', $class)[1] ?? null;
            }
        }
        return null;
    }
}
