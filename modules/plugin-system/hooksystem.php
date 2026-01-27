<?php
/**
 * Hook System - Event and filter management
 */
class HookSystem {
    private array $hooks = [];
    private array $filters = [];
    private bool $sandboxMode = true;

    public function addAction(string $hook, callable $callback, int $priority = 10): void {
        $this->hooks[$hook][$priority][] = $callback;
    }

    public function doAction(string $hook, ...$args): void {
        if (empty($this->hooks[$hook])) {
            return;
        }

        ksort($this->hooks[$hook]);
        foreach ($this->hooks[$hook] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $this->executeCallback($callback, $args);
            }
        }
    }

    public function addFilter(string $filter, callable $callback, int $priority = 10): void {
        $this->filters[$filter][$priority][] = $callback;
    }

    public function applyFilters(string $filter, $value, ...$args) {
        if (empty($this->filters[$filter])) {
            return $value;
        }

        ksort($this->filters[$filter]);
        foreach ($this->filters[$filter] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $value = $this->executeCallback($callback, array_merge([$value], $args));
            }
        }
        return $value;
    }

    private function executeCallback(callable $callback, array $args) {
        if ($this->sandboxMode) {
            try {
                return call_user_func_array($callback, $args);
            } catch (Throwable $e) {
                error_log("Hook execution failed: " . $e->getMessage());
                return null;
            }
        }
        return call_user_func_array($callback, $args);
    }

    public function setSandboxMode(bool $enabled): void {
        $this->sandboxMode = $enabled;
    }
}
