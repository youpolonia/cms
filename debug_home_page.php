<?php
/**
 * Debug script to diagnose home page issues
 */

// Define CMS_ROOT
define('CMS_ROOT', dirname(__FILE__));

// Set up error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log function
function debug_log($message, $data = null) {
    echo "<div style='margin: 5px; padding: 5px; border: 1px solid #ccc;'>";
    echo "<strong>$message</strong>";
    if ($data !== null) {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
    echo "</div>";
}

// Check file existence
debug_log("Checking critical files:");
$files = [
    'includes/routingv2/CoreRouterAdapter.php',
    'includes/core/Request.php',
    'core/Request.php',
    'includes/core/Response.php',
    'core/Response.php',
    'includes/core/SecurityService.php',
    'core/SecurityService.php',
    'bootstrap.php',
    'core/bootstrap.php',
    'config/security.php',
    'templates/home.php',
    'templates/layout.php'
];

foreach ($files as $file) {
    $path = CMS_ROOT . '/' . $file;
    debug_log("File: $file", "Exists: " . (file_exists($path) ? 'Yes' : 'No'));
}

// Check namespace resolution
debug_log("Checking namespace resolution:");
try {
    // Check if we can resolve the CoreRouterAdapter class
    if (class_exists('\Includes\RoutingV2\CoreRouterAdapter')) {
        debug_log("CoreRouterAdapter class exists");
    } else {
        debug_log("CoreRouterAdapter class does not exist");
    }
    
    // Check if we can resolve the Request class in Core namespace
    if (class_exists('\Core\Request')) {
        debug_log("Core\Request class exists");
    } else {
        debug_log("Core\Request class does not exist");
    }
    
    // Check if we can resolve the Request class in Includes\Core namespace
    if (class_exists('\Includes\Core\Request')) {
        debug_log("Includes\Core\Request class exists");
    } else {
        debug_log("Includes\Core\Request class does not exist");
    }
    
    // Check if we can resolve the Response class in Core namespace
    if (class_exists('\Core\Response')) {
        debug_log("Core\Response class exists");
    } else {
        debug_log("Core\Response class does not exist");
    }
    
    // Check if we can resolve the Response class in Includes\Core namespace
    if (class_exists('\Includes\Core\Response')) {
        debug_log("Includes\Core\Response class exists");
    } else {
        debug_log("Includes\Core\Response class does not exist");
    }
    
    // Check if we can resolve the SecurityService class
    if (class_exists('\Core\SecurityService')) {
        debug_log("SecurityService class exists");
    } else {
        debug_log("SecurityService class does not exist");
    }
} catch (Exception $e) {
    debug_log("Exception during namespace resolution check", $e->getMessage());
}

// Check TenantIsolationMiddleware
debug_log("Checking TenantIsolationMiddleware:");
try {
    // Include the middleware file
    require_once __DIR__ . '/includes/middleware/tenantisolationmiddleware.php';
    
    // Create a mock request
    $mockRequest = [
        'headers' => [
            'host' => 'example.com',
            'x-tenant-id' => null
        ]
    ];
    
    debug_log("Creating TenantIsolationMiddleware instance");
    $middleware = new \Includes\Middleware\TenantIsolationMiddleware();
    
    debug_log("Testing extractTenantId method (this will likely fail without a tenant ID)");
    try {
        // Use reflection to access the private method
        $reflectionMethod = new ReflectionMethod('\Includes\Middleware\TenantIsolationMiddleware', 'extractTenantId');
        $reflectionMethod->setAccessible(true);
        $tenantId = $reflectionMethod->invoke($middleware, $mockRequest);
        debug_log("Extracted tenant ID", $tenantId);
    } catch (Exception $e) {
        debug_log("Exception during tenant ID extraction", $e->getMessage());
    }
} catch (Exception $e) {
    debug_log("Exception during TenantIsolationMiddleware check", $e->getMessage());
}

// Check bootstrap file
debug_log("Checking bootstrap file:");
try {
    // Include the bootstrap file
    debug_log("Including bootstrap.php");
    ob_start();
    require_once __DIR__ . '/bootstrap.php';
    $output = ob_get_clean();
    debug_log("Bootstrap output", $output);
} catch (Exception $e) {
    debug_log("Exception during bootstrap inclusion", $e->getMessage());
}

debug_log("Debug script completed");