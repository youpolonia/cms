<?php
/**
 * Namespace Adapter
 * 
 * This file provides compatibility between the Core and Includes\Core namespaces
 * to resolve the namespace mismatch issue.
 */

// First, check if the Core classes exist in the includes/Core directory
if (file_exists(__DIR__ . '/Request.php')) {
    require_once __DIR__ . '/request.php';
    require_once __DIR__ . '/response.php';
    require_once __DIR__ . '/securityservice.php';
}

// Create class aliases to bridge the namespace gap
if (!class_exists('Includes\\Core\\Request') && class_exists('Core\\Request')) {
    class_alias('Core\\Request', 'Includes\\Core\\Request');
    class_alias('Core\\Response', 'Includes\\Core\\Response');
    class_alias('Core\\SecurityService', 'Includes\\Core\\SecurityService');
}

// If the classes don't exist in the Core namespace but do in Includes\Core, create aliases
if (!class_exists('Core\\Request') && class_exists('Includes\\Core\\Request')) {
    class_alias('Includes\\Core\\Request', 'Core\\Request');
    class_alias('Includes\\Core\\Response', 'Core\\Response');
    class_alias('Includes\\Core\\SecurityService', 'Core\\SecurityService');
}
