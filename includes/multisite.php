<?php

declare(strict_types=1);

namespace App\Includes;

use App\Includes\Database\Database; // Assuming this is your main DB access point
use App\Includes\Config\ConfigLoader;

/**
 * Class MultiSite
 *
 * Handles multi-site functionality, domain mapping, and site-specific configurations.
 */
class MultiSite
{
    private static ?array $config = null;
    private static ?object $currentSite = null; // Using a generic object or a dedicated Site class
    // private static ?Database $db = null; // Database interaction might be handled by specific components

    /**
     * Initializes the MultiSite service.
     * Loads configuration and determines the current site.
     * This should be called early in the application bootstrap process.
     */
    public static function initialize(): void
    {
        if (self::$config === null) {
            global $app; // Access the global application container
            if ($app) {
                try {
                    $allConfigs = $app->get('config'); // Attempt to get the config service
                    self::$config = $allConfigs['multisite'] ?? []; // Get the 'multisite' key specifically
                } catch (\RuntimeException $e) {
                    // 'config' service not found, or 'multisite' key missing, or other issue with $app->get()
                    self::$config = [];
                    trigger_error("MultiSite: Error retrieving 'config' service or 'multisite' data: " . $e->getMessage(), E_USER_WARNING);
                }
            } else {
                // Fallback if $app is not available
                self::$config = [];
                trigger_error("MultiSite: Application container (\$app) not available when initializing configuration.", E_USER_WARNING);
            }
        }

        if (!self::isEnabled()) {
            self::$currentSite = self::getDefaultSiteObject();
            return;
        }

        // self::$db = \core\Database::connection(); // If direct DB access is needed by MultiSite itself
        self::determineCurrentSite();
    }

    /**
     * Checks if MultiSite functionality is enabled in the configuration.
     *
     * @return bool True if enabled, false otherwise.
     */
    public static function isEnabled(): bool
    {
        // initialize() should have been called during bootstrap.
        // If self::$config is still null here, it means initialization failed or
        // the 'multisite' config key was not found.
        return self::$config['enabled'] ?? false; // Default to false if not set
    }

    /**
     * Gets the current site object.
     * Initializes the service if it hasn't been already.
     *
     * @return object|null The current site object or null if not determined.
     */
    public static function getCurrentSite(): ?object
    {
        // initialize() should have been called during bootstrap.
        // If self::$currentSite is still null here, it means initialization failed
        // or no site could be determined. It will return null or the default site object
        // if MultiSite::initialize() set a default due to missing config.
        if (self::$currentSite === null && self::$config === null) {
            // This case implies initialize() was never successfully run or $app was not available.
            // Trigger a warning and return a very basic default to prevent immediate fatal errors downstream.
            trigger_error("MultiSite::getCurrentSite() called when MultiSite not properly initialized.", E_USER_WARNING);
            return self::getDefaultSiteObject(); // getDefaultSiteObject has its own null config check
        }
        // If $currentSite is null but $config is NOT null, it means initialize() ran
        // but couldn't determine a site, and should have set a default.
        return self::$currentSite ?? self::getDefaultSiteObject();
    }

    /**
     * Gets the ID (handle) of the current site.
     *
     * @return string|null The current site handle (e.g., 'primary', 'site2') or null.
     */
    public static function getCurrentSiteId(): ?string
    {
        $site = self::getCurrentSite();
        return $site->handle ?? null; // 'handle' is more descriptive than 'id' for the string key
    }

    /**
     * Gets the full configuration array for a specific site by its handle.
     *
     * @param string $siteHandle The handle of the site (e.g., 'primary').
     * @return array|null The site configuration array or null if not found.
     */
    public static function getSiteConfig(string $siteHandle): ?array
    {
        // initialize() should have been called during bootstrap.
        // If self::$config is still null here, it means initialization failed.
        if (self::$config === null) {
            trigger_error("MultiSite::getSiteConfig() called when MultiSite config not loaded.", E_USER_WARNING);
            return null;
        }
        return self::$config['sites'][$siteHandle] ?? null;
    }

    /**
     * Determines the current site based on the HTTP_HOST.
     * This is a simplified version. A more robust solution might involve a Request class
     * and more complex domain/subdomain mapping logic.
     */
    private static function determineCurrentSite(): void
    {
        // Ensure config is loaded
        if (self::$config === null || !isset(self::$config['sites'])) {
            self::$currentSite = self::getDefaultSiteObject(); // Fallback if config is improper
            return;
        }

        $currentHost = $_SERVER['HTTP_HOST'] ?? php_uname('n'); // Fallback for CLI

        foreach (self::$config['sites'] as $handle => $siteConfig) {
            $domains = (array) ($siteConfig['domain'] ?? []);
            if (in_array($currentHost, $domains, true)) {
                // Add 'id' and 'handle' to the site object for consistency
                self::$currentSite = (object) array_merge(['id' => $handle, 'handle' => $handle], $siteConfig);
                return;
            }
        }
        // Fallback to default site if no specific domain match is found
        self::$currentSite = self::getDefaultSiteObject();
    }

    /**
     * Creates and returns the default site object based on configuration.
     *
     * @return object
     */
    private static function getDefaultSiteObject(): object
    {
        if (self::$config === null) {
             // This should ideally not happen if initialize is called first,
             // but as a safeguard:
            trigger_error("MultiSite config not loaded when trying to get default site object.", E_USER_WARNING);
            // Return a very basic default to prevent fatal errors downstream
            return (object) ['id' => 'default', 'handle' => 'default', 'domain' => 'localhost', 'theme' => 'default_theme'];
        }

        $defaultSiteHandle = self::$config['default_site'] ?? 'primary';
        $defaultSiteConfig = self::$config['sites'][$defaultSiteHandle] ?? [];
        
        // Ensure essential keys exist even if config is sparse for the default site
        $defaultSiteConfig['domain'] = $defaultSiteConfig['domain'] ?? 'localhost';
        $defaultSiteConfig['theme'] = $defaultSiteConfig['theme'] ?? 'default_theme';


        return (object) array_merge(['id' => $defaultSiteHandle, 'handle' => $defaultSiteHandle], $defaultSiteConfig);
    }

    /**
     * Gets the base storage path for the current site.
     *
     * @param string $relativePath Optional relative path to append to the site's storage directory.
     * @return string The absolute storage path for the site.
     */
    public static function getSiteStoragePath(string $relativePath = ''): string
    {
        $site = self::getCurrentSite();
        // Use handle as the directory name, ensure it's filesystem-safe if necessary
        $siteDirectoryName = $site->handle ?? (self::$config['default_site'] ?? 'primary');
        
        // Basic sanitization for directory name (can be expanded)
        $siteDirectoryName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $siteDirectoryName);

        $baseStoragePath = rtrim(self::$config['storage_path'] ?? 'storage/sites', '/\\');
        
        $siteSpecificPath = $baseStoragePath . DIRECTORY_SEPARATOR . $siteDirectoryName;
        
        if (!empty($relativePath)) {
            $siteSpecificPath .= DIRECTORY_SEPARATOR . ltrim($relativePath, '/\\');
        }
        
        // Ensure the directory exists
        if (!is_dir($siteSpecificPath)) {
            if (!mkdir($siteSpecificPath, 0755, true) && !is_dir($siteSpecificPath)) {
                // Handle error if directory creation fails
                error_log("MultiSite: Failed to create storage directory: {$siteSpecificPath}");
                // Fallback or throw exception as appropriate for your error handling strategy
                return $baseStoragePath . DIRECTORY_SEPARATOR . 'default_site_storage_error';
            }
        }
        
        return $siteSpecificPath;
    }

    /**
     * Gets the cache prefix for the current site.
     * This is used to segregate cache entries per site.
     *
     * @return string The cache prefix (e.g., "site_primary_"). Returns empty if not applicable.
     */
    public static function getCachePrefix(): string
    {
        if (self::isEnabled() && (self::$config['cache']['separate_cache'] ?? false)) {
            $site = self::getCurrentSite();
            $siteHandle = $site->handle ?? (self::$config['default_site'] ?? 'primary');
            // Sanitize site handle for use as prefix
            $safeHandle = preg_replace('/[^a-zA-Z0-9_]/', '_', $siteHandle);
            return (self::$config['cache']['prefix'] ?? 'site_') . $safeHandle . '_';
        }
        return ''; // No prefix if multisite is disabled or cache is not separated
    }

    /**
     * Checks if a given table name is configured as a shared table.
     * Shared tables do not get prefixed.
     *
     * @param string $tableName The name of the table.
     * @return bool True if the table is shared, false otherwise.
     */
    public static function isSharedTable(string $tableName): bool
    {
        if (!self::isEnabled() || !(self::$config['database']['prefix_enabled'] ?? true)) {
            // If multisite is disabled or DB prefixing is globally disabled, all tables are effectively "shared" (i.e., not prefixed).
            return true;
        }
        return in_array($tableName, self::$config['database']['shared_tables'] ?? [], true);
    }

    /**
     * Gets the prefixed table name for the current site if prefixing is enabled and the table is not shared.
     *
     * @param string $tableName The base name of the table.
     * @return string The (potentially) prefixed table name.
     */
    public static function getPrefixedTableName(string $tableName): string
    {
        if (self::isEnabled() && (self::$config['database']['prefix_enabled'] ?? true) && !self::isSharedTable($tableName)) {
            $site = self::getCurrentSite();
            $siteHandle = $site->handle ?? (self::$config['default_site'] ?? 'primary');
            // Sanitize site handle for use as prefix
            $prefix = preg_replace('/[^a-zA-Z0-9_]/', '_', $siteHandle);
            return $prefix . '_' . $tableName;
        }
        return $tableName; // Return base name if no prefixing applies
    }

    /**
     * Get the theme configured for the current site.
     *
     * @return string The theme name.
     */
    public static function getCurrentTheme(): string
    {
        $site = self::getCurrentSite();
        // Fallback chain: current site theme -> default site theme from config -> hardcoded default
        return $site->theme ?? (self::$config['sites'][self::$config['default_site'] ?? 'primary']['theme'] ?? 'default');
    }
    
    /**
     * Get all configured site handles from the multisite configuration.
     * @return array An array of site handles.
     */
    public static function getAllSiteHandles(): array
    {
        if (self::$config === null) {
            self::initialize();
        }
        return array_keys(self::$config['sites'] ?? []);
    }

    /**
     * Switches the current site context to the specified site handle.
     * USE WITH CAUTION. This is primarily intended for administrative tasks or specific backend processes
     * where operating under a different site's context is explicitly required.
     * It re-runs parts of the initialization logic for the new site.
     *
     * @param string $siteHandle The handle of the site to switch to.
     * @return bool True on successful switch, false if site not found or multisite is disabled.
     */
    public static function switchToSite(string $siteHandle): bool
    {
        if (!self::isEnabled()) {
            // Optionally log an attempt to switch when multisite is disabled
            // error_log("MultiSite: Attempted to switch site when multisite is disabled.");
            return false;
        }

        if (self::$config === null) {
            self::initialize(); // Should not be necessary if initialize was called, but as a safeguard
        }

        $siteConfig = self::getSiteConfig($siteHandle); // getSiteConfig already calls initialize if needed
        if ($siteConfig === null) {
            error_log("MultiSite: Attempted to switch to non-existent site '{$siteHandle}'.");
            return false;
        }

        // Update currentSite with the new site's configuration
        self::$currentSite = (object) array_merge(['id' => $siteHandle, 'handle' => $siteHandle], $siteConfig);
        
        // IMPORTANT: Re-initialization or notification for other services
        // Depending on your application architecture, other services (e.g., Database, Cache,
        // Theme engine, Session manager) might need to be notified or re-initialized
        // to reflect the new site context. This part is highly application-specific.
        // Example:
        // if (class_exists('\App\Includes\Database\Database')) {
        //     \App\Includes\Database\Database::updateSiteContext(self::$currentSite);
        // }
        // if (class_exists('\App\Includes\Cache\CacheManager')) {
        //     \App\Includes\Cache\CacheManager::updateSiteContext(self::$currentSite);
        // }

        // For now, we assume components will call MultiSite::getCurrentSite() or related methods
        // to get fresh, context-aware information.

        return true;
    }
}

// It's generally better to explicitly call `MultiSite::initialize()` early in your
// application's bootstrap sequence (e.g., in your main `index.php` or a core loader)
// rather than relying on auto-initialization within methods, to ensure predictable state.
// Example: require_once __DIR__ . '/includes/multisite.php'; MultiSite::initialize();
