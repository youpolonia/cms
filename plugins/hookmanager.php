<?php
namespace CMS\Plugins;

/**
 * CMS Hook Manager
 * Centralized system for managing plugin hooks
 */
class HookManager {
    private $hooks = [];
    private $filters = [];
    private $actions = [];

    /**
     * Register a new action hook
     * @param string $name Hook name
     * @param callable $callback Callback function
     * @param int $priority Execution priority (lower numbers execute earlier)
     */
    public function addAction(string $name, callable $callback, int $priority = 10): void {
        $this->actions[$name][$priority][] = $callback;
    }

    /**
     * Register a new filter hook
     * @param string $name Hook name
     * @param callable $callback Callback function
     * @param int $priority Execution priority (lower numbers execute earlier)
     */
    public function addFilter(string $name, callable $callback, int $priority = 10): void {
        $this->filters[$name][$priority][] = $callback;
    }

    /**
     * Execute all callbacks for an action hook
     * @param string $name Hook name
     * @param array $args Arguments to pass to callbacks
     */
    public function doAction(string $name, array $args = []): void {
        if (!isset($this->actions[$name])) {
            return;
        }

        ksort($this->actions[$name]);
        
        foreach ($this->actions[$name] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                call_user_func_array($callback, $args);
            }
        }
    }

    /**
     * Apply all callbacks for a filter hook
     * @param string $name Hook name
     * @param mixed $value The value to filter
     * @param array $args Additional arguments
     * @return mixed The filtered value
     */
    public function applyFilters(string $name, $value, array $args = []) {
        if (!isset($this->filters[$name])) {
            return $value;
        }

        ksort($this->filters[$name]);
        
        $args = array_merge([$value], $args);
        foreach ($this->filters[$name] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $value = call_user_func_array($callback, $args);
                $args[0] = $value;
            }
        }

        return $value;
    }

    /**
     * Check if a hook exists
     * @param string $name Hook name
     * @param string $type 'action' or 'filter'
     * @return bool
     */
    public function hasHook(string $name, string $type): bool {
        return $type === 'action' 
            ? isset($this->actions[$name])
            : isset($this->filters[$name]);
    }
}
