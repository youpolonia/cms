<?php
/**
 * Plugin Compatibility Checker
 * Verifies system requirements for plugins
 */
class PluginCompatibilityChecker {
    private $requiredPhpVersion;
    private $requiredExtensions = [];

    public function __construct() {
        $this->requiredPhpVersion = PHP_VERSION;
    }

    public function check(array $plugin): array {
        $errors = [];
        
        // Check PHP version
        if (isset($plugin['requires']['php'])) {
            if (version_compare($this->requiredPhpVersion, $plugin['requires']['php'], '<')) {
                $errors[] = "Requires PHP {$plugin['requires']['php']}+ (current: {$this->requiredPhpVersion})";
            }
        }

        // Check extensions
        if (isset($plugin['requires']['extensions'])) {
            foreach ($plugin['requires']['extensions'] as $ext) {
                if (!extension_loaded($ext)) {
                    $errors[] = "Missing required extension: {$ext}";
                }
            }
        }

        return $errors;
    }
}
