<?php
namespace Core;

final class ControllerRegistry
{
    /** @var array<string, callable|array|string> */
    private static array $controllers = [];

    public static function register(string $name, callable|array|string $handler): void
    {
        self::$controllers[$name] = $handler;
    }

    /** @return callable|array|string|null */
    public static function get(string $name)
    {
        return self::$controllers[$name] ?? null;
    }

    /**
     * Validate that a controller class and method exist
     * @param string $className Full class name
     * @param string $methodName Method name to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateController(string $className, string $methodName): bool
    {
        if (!class_exists($className)) {
            // error_log("ControllerRegistry: class not found: {$className}");
            return false;
        }

        if (!method_exists($className, $methodName)) {
            // error_log("ControllerRegistry: method not found: {$className}::{$methodName}");
            return false;
        }

        return true;
    }

    /**
     * Log controller usage for diagnostics
     * @param string $className Full class name
     * @param string $methodName Method name
     */
    public static function logUsage(string $className, string $methodName): void
    {
        // error_log("ControllerRegistry: registered {$className}::{$methodName}");
    }
}
