<?php
/**
 * CMS Hook System
 */

require_once __DIR__ . '/moduleregistry.php';

function add_action(string $name, callable $callback): void {
    Core\ModuleRegistry::addHook($name, $callback);
}

function do_action(string $name, ...$args): void {
    $hooks = Core\ModuleRegistry::getHooks($name) ?? [];
    foreach ($hooks as $hook) {
        call_user_func_array($hook, $args);
    }
}
