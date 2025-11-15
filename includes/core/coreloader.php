<?php
/**
 * Core System Loader
 * Implements framework-free PHP structure
 * PSR-4 compliant autoloader
 */
class CoreLoader {
    private static $instance;
    private $modules = [];
    private $baseDir; // Add a base directory property

    private function __construct() {
        $this->baseDir = realpath(__DIR__ . '/../');
        // Autoloader registration removed per project rules.
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function loadClass($className) {
        $className = ltrim($className, '\\');
        $originalClassPath = str_replace('\\', '/', $className);
        
        // Enhanced debug logging
        error_log("CoreLoader: Attempting to load class: {$className}");
        error_log("CoreLoader: Original class path: {$originalClassPath}");
        error_log("CoreLoader: Base directory: {$this->baseDir}");

        $potentialPaths = [];

        // Path 1: Specific handling for "Includes" namespace -> "includes" directory (case-sensitive)
        if (strpos($originalClassPath, 'Includes/') === 0) {
            $path = $this->baseDir . '/includes/' . substr($originalClassPath, strlen('Includes/')) . '.php';
            $potentialPaths[$path] = "Specific 'Includes' mapping (case-sensitive)";
            
            // Special handling for RoutingV2 -> routing_v2
            if (strpos($originalClassPath, 'Includes/RoutingV2/') === 0) {
                $routingPath = $this->baseDir . '/includes/routing_v2/' . substr($originalClassPath, strlen('Includes/RoutingV2/')) . '.php';
                $potentialPaths[$routingPath] = "Specific 'RoutingV2' to 'routing_v2' mapping";
            }

            // Special handling for Performance -> performance (lowercase)
            if (strpos($originalClassPath, 'Includes/Performance/') === 0) {
                $perfPath = $this->baseDir . '/includes/performance/' . substr($originalClassPath, strlen('Includes/Performance/')) . '.php';
                $potentialPaths[$perfPath] = "Specific 'Performance' mapping (lowercase)";
            }
        }
        
        // Path 2: Specific handling for "App" namespace -> "app" directory
        if (strpos($originalClassPath, 'App/') === 0) {
             $path = $this->baseDir . '/app/' . substr($originalClassPath, strlen('App/')) . '.php';
             $potentialPaths[$path] = "Specific 'App' mapping";
        }

        // Path 3: Original case path
        $path = $this->baseDir . '/' . $originalClassPath . '.php';
        $potentialPaths[$path] = "Original case";

        // Path 4: Full lowercase path (fallback)
        $path = $this->baseDir . '/' . strtolower($originalClassPath) . '.php';
        $potentialPaths[$path] = "Full lowercase fallback";

        // Path 5: Specific checks for global classes
        if ($className === 'Database') {
            $path = $this->baseDir . '/includes/database/connection.php';
            $potentialPaths = [$path => "Global 'Database' class hardcoded path"] + $potentialPaths;
        } elseif ($className === 'ViewRenderer') {
            $path = $this->baseDir . '/includes/ViewRenderer.php';
            $potentialPaths = [$path => "Global 'ViewRenderer' class hardcoded path"] + $potentialPaths;
        }

        // Debug log all potential paths
        error_log("CoreLoader: Resolving '{$className}' with " . count($potentialPaths) . " potential paths");
        
        error_log("CoreLoader: Attempting to load '{$className}'. BaseDir: '{$this->baseDir}'. Checking paths:");

        foreach ($potentialPaths as $filePath => $description) {
            error_log("CoreLoader: Checking ({$description}): {$filePath}");
            if (file_exists($filePath)) {
                try {
                    require_once $filePath;
                    error_log("CoreLoader: Successfully loaded '{$className}' from '{$filePath}' ({$description})");
                    return true;
                } catch (Throwable $e) {
                    error_log("CoreLoader: Exception while loading '{$className}' from '{$filePath}' ({$description}). Error: " . $e->getMessage());
                    // Potentially continue to try other paths if desired, but for now, fail on first load error.
                    return false;
                }
            }
        }

        error_log("CoreLoader: File NOT FOUND for '{$className}' after checking all potential paths.");
        return false;
    }

    public function registerModule($name, $path) {
        if (!is_dir($path)) {
            throw new InvalidArgumentException("Module path {$path} does not exist");
        }
        $this->modules[$name] = rtrim($path, '/');
    }

    public function initSystem() {
        // Load core configuration
        $config = $this->loadConfig();
        
        // Initialize registered modules
        foreach ($this->modules as $name => $path) {
            $initFile = $path . '/init.php';
            if (file_exists($initFile)) {
                try {
                    require_once $initFile;
                } catch (Throwable $e) {
                    error_log("Module {$name} initialization failed: " . $e->getMessage());
                }
            }
        }
    }

    public function loadConfig() {
        $configFile = __DIR__ . '/../config/core.json';
        if (file_exists($configFile)) {
            return json_decode(file_get_contents($configFile), true);
        }
        return [];
    }
}
