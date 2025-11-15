<?php
/**
 * Theme variable helper functions
 */

require_once __DIR__ . '/themevariablemanager.php';

function get_theme_variable(string $name, $default = null, ?string $theme = null) {
    return ThemeVariableManager::getInstance()->getVariable($name, $default, $theme);
}

function set_theme_variable(string $name, $value, ?string $type = null, ?string $theme = null) {
    return ThemeVariableManager::getInstance()->setVariable($name, $value, $type, $theme);
}

function render_theme_view(string $viewPath, array $context = []) {
    $themeVars = ThemeVariableManager::getInstance()->getAllVariables();
    $context = array_merge($themeVars, $context);
    
    extract($context);
    ob_start();
    require_once $viewPath;
    return ob_get_clean();
}
