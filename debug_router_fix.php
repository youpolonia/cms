<?php
declare(strict_types=1);

/**
 * Debug Router Fix
 * 
 * This file implements a fix for the router implementation conflict
 * where Router.php expects an array but routes.php returns a Router instance.
 * 
 * Usage:
 * 1. Include this file in your project
 * 2. Call RouterDebugger::checkRouterCompatibility() before using the router
 */

class RouterDebugger {
    /**
     * Check router compatibility and log issues
     * 
     * @param mixed $routerOrRoutes The router instance or routes array
     * @return array The normalized routes array
     */
    public static function checkRouterCompatibility($routerOrRoutes): array {
        // Log the check
        self::logDebug("Checking router compatibility");
        
        // Check if we received a Router instance instead of array
        if (is_object($routerOrRoutes)) {
            $className = get_class($routerOrRoutes);
            self::logWarning("Router.php expected array but received $className");
            
            // Handle Core\Router vs CMS\API\Router conflict
            if ($className === 'Core\Router' || $className === 'CMS\API\Router') {
                self::logDebug("Converting $className to compatible format");
                return self::convertRouterToArray($routerOrRoutes);
            }
            
            // If it's a RoutingV2\Router, extract routes
            if ($routerOrRoutes instanceof \RoutingV2\Router) {
                self::logDebug("Converting RoutingV2\\Router to compatible format");
                return self::convertRouterToArray($routerOrRoutes);
            }
            
            // If it's another Router implementation
            if (method_exists($routerOrRoutes, 'getRoutes')) {
                self::logDebug("Extracting routes using getRoutes() method");
                return $routerOrRoutes->getRoutes();
            }
            
            // Last resort - try to cast to array
            self::logError("Unknown router type, attempting array conversion");
            return (array)$routerOrRoutes;
        }
        
        // If it's already an array, return as is
        if (is_array($routerOrRoutes)) {
            self::logDebug("Router received expected array format");
            return $routerOrRoutes;
        }
        
        // If we got here, something is wrong
        self::logError("Invalid router format: " . gettype($routerOrRoutes));
        return [];
    }
    
    /**
     * Convert a RoutingV2\Router instance to compatible array format
     * 
     * @param \RoutingV2\Router $router The router instance
     * @return array The extracted routes
     */
    private static function convertRouterToArray($router): array {
        $routes = [];
        $className = get_class($router);
        
        // Log which Router implementation we're converting
        self::logDebug("Converting $className to array format");
        
        // Use reflection to access protected/private properties if needed
        $reflection = new \ReflectionClass($router);
        
        // Try to get routes property
        try {
            $routesProperty = $reflection->getProperty('routes');
            $routesProperty->setAccessible(true);
            $routes = $routesProperty->getValue($router);
            self::logDebug("Successfully extracted routes via reflection");
        } catch (\ReflectionException $e) {
            self::logError("Failed to extract routes: " . $e->getMessage());
        }
        
        return $routes;
    }
    
    /**
     * Log debug message
     */
    private static function logDebug(string $message): void {
        error_log("[RouterDebug][DEBUG] " . $message);
    }
    
    /**
     * Log warning message
     */
    private static function logWarning(string $message): void {
        error_log("[RouterDebug][WARNING] " . $message);
    }
    
    /**
     * Log error message
     */
    private static function logError(string $message): void {
        error_log("[RouterDebug][ERROR] " . $message);
    }
}
