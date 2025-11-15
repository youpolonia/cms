<?php
/**
 * CMS Theme System
 * 
 * @package CMS
 * @subpackage Theme
 */

namespace Includes\Theme;

// Use the new central MultiSite service
use App\Includes\MultiSite;
use Includes\Config\ConfigLoader; // Assuming this ConfigLoader is compatible or also to be reviewed

class Theme
{
    /**
     * @var string Active theme
     */
    protected string $activeTheme;
    
    /**
     * @var array Theme configuration
     */
    protected array $config;
    
    /**
     * @var string|null Current site ID (handle)
     */
    protected ?string $currentSiteHandle = null;
    
    // SiteManager is no longer needed as a property, will use static MultiSite service
    // protected ?SiteManager $siteManager = null;
    
    /**
     * @var array Parent theme cache
     */
    protected array $parentThemeCache = [];
    
    /**
     * @var array Template path cache
     */
    protected array $templatePathCache = [];
    
    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config ?: ConfigLoader::load('theme'); // Assuming ConfigLoader::load('theme') is correct
        
        // Ensure MultiSite is initialized (should be done in bootstrap.php)
        // App\Includes\MultiSite::initialize(); // Not strictly needed here if bootstrap guarantees it

        if (MultiSite::isEnabled()) {
            $this->currentSiteHandle = MultiSite::getCurrentSiteId();
            $this->activeTheme = MultiSite::getCurrentTheme();
            // If theme config has a global default, MultiSite::getCurrentTheme() should already consider it.
            // If not, and a theme isn't set for the site, this might need a fallback.
            // The current MultiSite::getCurrentTheme() has a fallback to 'default'.
        } else {
            $this->activeTheme = $this->config['default_theme'] ?? 'default';
            $this->currentSiteHandle = null; // Explicitly null if not enabled
        }
    }
    
    /**
     * Get active theme
     *
     * @return string
     */
    public function getActiveTheme(): string
    {
        return $this->activeTheme;
    }
    
    /**
     * Set active theme
     *
     * @param string $theme
     */
    public function setActiveTheme(string $theme): void
    {
        $this->activeTheme = $theme;
        $this->templatePathCache = []; // Clear cache when theme changes
    }
    
    /**
     * Get theme path
     *
     * @param string|null $theme
     * @return string
     */
    public function getThemePath(string $theme = null): string
    {
        $theme = $theme ?? $this->activeTheme;
        
        // If multisite is enabled, check for site-specific theme path
        if (MultiSite::isEnabled() && $this->currentSiteHandle) {
            // Use the new MultiSite service for paths
            $sitePath = MultiSite::getSiteStoragePath('themes' . DIRECTORY_SEPARATOR . $theme);
            if (is_dir($sitePath)) {
                return $sitePath;
            }
        }
        
        // Fallback to global themes directory
        $baseThemesPath = defined('CMS_ROOT') ? CMS_ROOT . '/themes' : __DIR__ . '/../../themes';
        return $baseThemesPath . DIRECTORY_SEPARATOR . $theme;
    }
    
    /**
     * Get parent theme
     *
     * @param string|null $theme
     * @return string|null
     */
    public function getParentTheme(string $theme = null): ?string
    {
        $theme = $theme ?? $this->activeTheme;
        
        // Check cache first
        if (isset($this->parentThemeCache[$theme])) {
            return $this->parentThemeCache[$theme];
        }
        
        // Check theme.json for parent theme
        $themeConfigPath = $this->getThemePath($theme) . '/theme.json';
        if (file_exists($themeConfigPath)) {
            $themeConfig = json_decode(file_get_contents($themeConfigPath), true);
            if ($themeConfig && isset($themeConfig['parent'])) {
                $this->parentThemeCache[$theme] = $themeConfig['parent'];
                return $themeConfig['parent'];
            }
        }
        
        // No parent theme found
        $this->parentThemeCache[$theme] = null;
        return null;
    }
    
    /**
     * Resolve template path using inheritance
     *
     * @param string $template
     * @return string|null
     */
    public function resolveTemplatePath(string $template): ?string
    {
        // Check cache first
        $cacheKey = $this->activeTheme . '|' . $template;
        if (isset($this->templatePathCache[$cacheKey])) {
            return $this->templatePathCache[$cacheKey];
        }
        
        // Get inheritance order from config
        $multisiteConfig = ConfigLoader::load('multisite');
        $fallbackOrder = $multisiteConfig['template_inheritance']['fallback_order'] ?? ['site', 'parent', 'core'];
        
        // Build theme hierarchy
        $themeHierarchy = [$this->activeTheme];
        $currentTheme = $this->activeTheme;
        
        while ($parentTheme = $this->getParentTheme($currentTheme)) {
            $themeHierarchy[] = $parentTheme;
            $currentTheme = $parentTheme;
        }
        
        // Add core theme as last fallback
        if (!in_array('core', $themeHierarchy)) {
            $themeHierarchy[] = 'core';
        }
        
        // Check for template in each theme according to fallback order
        foreach ($fallbackOrder as $type) {
            switch ($type) {
                case 'site':
                    // Check site-specific template using MultiSite service
                    if (MultiSite::isEnabled() && $this->currentSiteHandle) {
                        $siteTemplatePath = MultiSite::getSiteStoragePath('templates' . DIRECTORY_SEPARATOR . $template);
                        if (file_exists($siteTemplatePath)) {
                            $this->templatePathCache[$cacheKey] = $siteTemplatePath;
                            return $siteTemplatePath;
                        }
                    }
                    break;
                    
                case 'parent':
                case 'core':
                    // Check theme hierarchy
                    foreach ($themeHierarchy as $theme) {
                        $templatePath = $this->getThemePath($theme) . '/templates/' . $template;
                        if (file_exists($templatePath)) {
                            $this->templatePathCache[$cacheKey] = $templatePath;
                            return $templatePath;
                        }
                    }
                    break;
            }
        }
        
        // Template not found
        $this->templatePathCache[$cacheKey] = null;
        return null;
    }
    
    /**
     * Check if a theme exists
     *
     * @param string $theme
     * @return bool
     */
    public function themeExists(string $theme): bool
    {
        return is_dir($this->getThemePath($theme));
    }
    
    /**
     * Get available themes
     *
     * @return array
     */
    public function getAvailableThemes(): array
    {
        $themes = [];
        $themesDir = __DIR__ . '/../../themes';
        
        if (is_dir($themesDir)) {
            $dirs = scandir($themesDir);
            foreach ($dirs as $dir) {
                if ($dir !== '.' && $dir !== '..' && is_dir($themesDir . '/' . $dir)) {
                    $themes[] = $dir;
                }
            }
        }
        
        // Add site-specific themes if multisite is enabled
        if (MultiSite::isEnabled() && $this->currentSiteHandle) {
            // This logic might need refinement if all site themes are desired,
            // or just for the current site. Assuming for current site for now.
            $siteThemesDir = MultiSite::getSiteStoragePath('themes');
            if (is_dir($siteThemesDir)) {
                $siteThemeDirs = scandir($siteThemesDir);
                foreach ($siteThemeDirs as $dir) {
                    if ($dir !== '.' && $dir !== '..' && is_dir($siteThemesDir . DIRECTORY_SEPARATOR . $dir) && !in_array($dir, $themes)) {
                        $themes[] = $dir;
                    }
                }
            }
        }
        
        return array_unique($themes); // Ensure uniqueness
    }
    
    /**
     * Get theme assets URL
     *
     * @param string $path
     * @param string|null $theme
     * @return string
     */
    public function getThemeAssetUrl(string $path, string $theme = null): string
    {
        $theme = $theme ?? $this->activeTheme;
        
        // If multisite is enabled, check for site-specific assets
        if (MultiSite::isEnabled() && $this->currentSiteHandle) {
            // Construct URL relative to site's base, assuming a web-accessible storage path for sites
            // This might need adjustment based on how site storage is mapped to URLs
            // For now, let's assume a '/storage/sites/{site_handle}/themes...' structure under web root
            // Or, more robustly, a config for site asset base URL.
            
            $siteSpecificAssetPhysicalPath = MultiSite::getSiteStoragePath('themes' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $path);
            
            if (file_exists($siteSpecificAssetPhysicalPath)) {
                // This URL construction is a placeholder and depends heavily on web server config
                // and how `storage/sites/{site_handle}` is made web-accessible.
                // A common pattern is to have a symlink from `public/sites/{site_handle}` to `storage/sites/{site_handle}`
                // or a dedicated route/controller to serve these assets.
                // For simplicity, assuming a direct mapping for now.
                $baseSiteAssetUrl = MultiSite::getCurrentSite()->asset_base_url ?? ('/storage/sites/' . $this->currentSiteHandle);
                return rtrim($baseSiteAssetUrl, '/') . THEMES_DIR . $theme . '/assets/' . ltrim($path, '/');
            }
        }
        
        // Fallback to global theme assets URL
        // Ensure CMS_BASE_URL or similar is defined for constructing full URLs if needed,
        // or keep it relative to web root.
        $globalThemeBaseUrl = defined('CMS_THEMES_URL') ? CMS_THEMES_URL : '/themes';
        return rtrim($globalThemeBaseUrl, '/') . '/' . $theme . '/assets/' . ltrim($path, '/');
    }
}
