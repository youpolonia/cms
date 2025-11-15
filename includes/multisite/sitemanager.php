<?php

namespace Includes\Multisite;

use Includes\Config\ConfigLoader;
use Includes\Database\DatabaseConnection;
use Includes\ErrorHandler;

/**
 * SiteManager - Handles multi-site management operations
 */
class SiteManager
{
    /**
     * @var array The site configuration
     */
    private array $config;
    
    /**
     * @var string The current site identifier
     */
    private string $currentSite;
    
    /**
     * @var array Cache of site data
     */
    private array $siteCache = [];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = ConfigLoader::load('multisite');
        $this->currentSite = $this->config['default_site'];
        
        // Auto-detect site based on domain if enabled
        if ($this->config['enabled']) {
            $this->detectCurrentSite();
        }
    }
    
    /**
     * Detect the current site based on the domain
     */
    private function detectCurrentSite(): void
    {
        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        
        foreach ($this->config['sites'] as $siteId => $siteConfig) {
            if ($siteConfig['domain'] === $serverName) {
                $this->currentSite = $siteId;
                break;
            }
        }
    }
    
    /**
     * Get the current site ID
     *
     * @return string
     */
    public function getCurrentSite(): string
    {
        return $this->currentSite;
    }
    
    /**
     * Set the current site
     *
     * @param string $siteId
     * @return bool
     */
    public function setCurrentSite(string $siteId): bool
    {
        if (!$this->siteExists($siteId)) {
            return false;
        }
        
        $this->currentSite = $siteId;
        return true;
    }
    
    /**
     * Check if a site exists
     *
     * @param string $siteId
     * @return bool
     */
    public function siteExists(string $siteId): bool
    {
        return isset($this->config['sites'][$siteId]);
    }
    
    /**
     * Get site configuration
     *
     * @param string|null $siteId
     * @return array|null
     */
    public function getSiteConfig(?string $siteId = null): ?array
    {
        $siteId = $siteId ?? $this->currentSite;
        
        if (!$this->siteExists($siteId)) {
            return null;
        }
        
        return $this->config['sites'][$siteId];
    }
    
    /**
     * Get all sites
     *
     * @return array
     */
    public function getAllSites(): array
    {
        return $this->config['sites'];
    }
    
    /**
     * Register a new site
     *
     * @param string $siteId
     * @param array $config
     * @return bool
     */
    public function registerSite(string $siteId, array $config): bool
    {
        if ($this->siteExists($siteId)) {
            return false;
        }
        
        // Validate required configuration
        if (!isset($config['domain']) || !isset($config['theme'])) {
            return false;
        }
        
        // Create site directory structure
        $sitePath = $this->config['storage_path'] . '/' . $siteId;
        if (!is_dir($sitePath) && !mkdir($sitePath, 0755, true)) {
            ErrorHandler::logError("Failed to create site directory: $sitePath");
            return false;
        }
        
        // Update configuration
        $this->config['sites'][$siteId] = $config;
        
        // Save configuration (in a real implementation, this would update the config file)
        // For now, we'll just update the in-memory config
        
        return true;
    }
    
    /**
     * Remove a site
     *
     * @param string $siteId
     * @return bool
     */
    public function removeSite(string $siteId): bool
    {
        if (!$this->siteExists($siteId) || $siteId === $this->config['default_site']) {
            return false;
        }
        
        // Remove from configuration
        unset($this->config['sites'][$siteId]);
        
        // In a real implementation, we would also:
        // 1. Archive site data
        // 2. Update configuration file
        // 3. Clean up database tables
        
        return true;
    }
    
    /**
     * Get the database prefix for a site
     *
     * @param string|null $siteId
     * @return string
     */
    public function getDatabasePrefix(?string $siteId = null): string
    {
        $siteId = $siteId ?? $this->currentSite;
        
        if (!$this->config['database']['prefix_enabled']) {
            return '';
        }
        
        return $siteId . '_';
    }
    
    /**
     * Get the storage path for a site
     *
     * @param string|null $siteId
     * @return string
     */
    public function getSiteStoragePath(?string $siteId = null): string
    {
        $siteId = $siteId ?? $this->currentSite;
        return $this->config['storage_path'] . '/' . $siteId;
    }
    
    /**
     * Check if a table is shared across sites
     *
     * @param string $tableName
     * @return bool
     */
    public function isSharedTable(string $tableName): bool
    {
        return in_array($tableName, $this->config['database']['shared_tables']);
    }
    
    /**
     * Get the site's theme
     *
     * @param string|null $siteId
     * @return string
     */
    public function getSiteTheme(?string $siteId = null): string
    {
        $siteId = $siteId ?? $this->currentSite;
        $config = $this->getSiteConfig($siteId);
        
        return $config['theme'] ?? 'default';
    }
    
    /**
     * Get the site's domain
     *
     * @param string|null $siteId
     * @return string|null
     */
    public function getSiteDomain(?string $siteId = null): ?string
    {
        $siteId = $siteId ?? $this->currentSite;
        $config = $this->getSiteConfig($siteId);
        
        return $config['domain'] ?? null;
    }
    
    /**
     * Check if multisite is enabled
     *
     * @return bool
     */
    public function isMultisiteEnabled(): bool
    {
        return (bool) $this->config['enabled'];
    }
    
    /**
     * Get default roles for new sites
     *
     * @return array
     */
    public function getDefaultRoles(): array
    {
        return $this->config['default_roles'] ?? [];
    }
    
    /**
     * Get site storage limit
     *
     * @param string|null $siteId
     * @return string
     */
    public function getStorageLimit(?string $siteId = null): string
    {
        $siteId = $siteId ?? $this->currentSite;
        $config = $this->getSiteConfig($siteId);
        
        return $config['storage_limit'] ?? '1GB';
    }
    
    /**
     * Check if a site is exceeding its storage limit
     *
     * @param string|null $siteId
     * @return bool
     */
    public function isStorageLimitExceeded(?string $siteId = null): bool
    {
        $siteId = $siteId ?? $this->currentSite;
        $limit = $this->getStorageLimit($siteId);
        $path = $this->getSiteStoragePath($siteId);
        
        // Convert limit to bytes
        $limitBytes = $this->convertToBytes($limit);
        
        // Get current usage
        $usage = $this->getDirectorySize($path);
        
        return $usage > $limitBytes;
    }
    
    /**
     * Convert human-readable size to bytes
     *
     * @param string $size
     * @return int
     */
    private function convertToBytes(string $size): int
    {
        $unit = strtoupper(substr($size, -2));
        $value = (int) substr($size, 0, -2);
        
        switch ($unit) {
            case 'KB':
                return $value * 1024;
            case 'MB':
                return $value * 1024 * 1024;
            case 'GB':
                return $value * 1024 * 1024 * 1024;
            case 'TB':
                return $value * 1024 * 1024 * 1024 * 1024;
            default:
                return (int) $size;
        }
    }
    
    /**
     * Get directory size in bytes
     *
     * @param string $path
     * @return int
     */
    private function getDirectorySize(string $path): int
    {
        $size = 0;
        
        if (!is_dir($path)) {
            return 0;
        }
        
        $files = scandir($path);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $filePath = $path . '/' . $file;
            
            if (is_dir($filePath)) {
                $size += $this->getDirectorySize($filePath);
            } else {
                $size += filesize($filePath);
            }
        }
        
        return $size;
    }
    
    /**
     * Update site configuration
     *
     * @param string $siteId
     * @param array $config
     * @return bool
     */
    public function updateSiteConfig(string $siteId, array $config): bool
    {
        if (!$this->siteExists($siteId)) {
            return false;
        }
        
        // Validate required configuration
        if (!isset($config['domain']) || !isset($config['theme'])) {
            return false;
        }
        
        // Update configuration
        $this->config['sites'][$siteId] = array_merge(
            $this->config['sites'][$siteId],
            $config
        );
        
        // In a real implementation, this would update the config file
        // For now, we'll just update the in-memory config
        
        return true;
    }
    
    /**
     * Get default site ID
     *
     * @return string
     */
    public function getDefaultSite(): string
    {
        return $this->config['default_site'];
    }
}
