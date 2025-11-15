<?php

namespace Includes\Theme;

use Includes\Multisite\SiteManager;

/**
 * TemplateInheritance - Handles template rendering with inheritance support
 */
class TemplateInheritance
{
    /**
     * @var Theme
     */
    private Theme $theme;
    
    /**
     * @var SiteManager|null
     */
    private ?SiteManager $siteManager = null;
    
    /**
     * @var array Template variables
     */
    private array $variables = [];
    
    /**
     * @var array Sections
     */
    private array $sections = [];
    
    /**
     * @var string|null Current section being captured
     */
    private ?string $currentSection = null;
    
    /**
     * @var array Output buffer for sections
     */
    private array $sectionBuffer = [];
    
    /**
     * Constructor
     *
     * @param Theme $theme
     */
    public function __construct(Theme $theme)
    {
        $this->theme = $theme;
        
        // Initialize site manager if available
        try {
            $this->siteManager = new SiteManager();
        } catch (\Exception $e) {
            // Multisite not enabled, continue without it
        }
    }
    
    /**
     * Render a template
     *
     * @param string $template
     * @param array $variables
     * @return string
     * @throws \Exception
     */
    public function render(string $template, array $variables = []): string
    {
        $this->variables = array_merge($this->variables, $variables);
        
        // First try the requested template
        $templatePath = $this->theme->resolveTemplatePath($template);
        
        // If not found, try default_page.php fallback
        if (!$templatePath) {
            $templatePath = $this->theme->resolveTemplatePath('default_page');
            if (!$templatePath) {
                throw new \Exception("Neither template '$template' nor fallback template 'default_page' could be found");
            }
        }
        
        // Start output buffering
        ob_start();
        
        // Extract variables
        extract($this->variables);
        
        // Include template
        require_once $templatePath;
        
        // Get content
        $content = ob_get_clean();
        
        return $content;
    }
    
    /**
     * Set a template variable
     *
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function with(string $name, $value): self
    {
        $this->variables[$name] = $value;
        return $this;
    }
    
    /**
     * Extend a parent template
     *
     * @param string $template
     * @return void
     */
    public function extend(string $template): void
    {
        // Capture current output
        $content = ob_get_clean();
        
        // Start new buffer
        ob_start();
        
        // Store content as section
        $this->sections['content'] = $content;
        
        // Render parent template
        echo $this->render($template);
    }
    
    /**
     * Start a section
     *
     * @param string $name
     * @return void
     */
    public function section(string $name): void
    {
        $this->currentSection = $name;
        ob_start();
    }
    
    /**
     * End a section
     *
     * @return void
     */
    public function endSection(): void
    {
        if ($this->currentSection) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }
    
    /**
     * Yield a section
     *
     * @param string $name
     * @param string $default
     * @return void
     */
    public function yield(string $name, string $default = ''): void
    {
        echo $this->sections[$name] ?? $default;
    }
    
    /**
     * Include a partial template
     *
     * @param string $template
     * @param array $variables
     * @return void
     */
    public function require_once(string $template, array $variables = []): void
    {
        echo $this->render($template, $variables);
    }
    
    /**
     * Get site-specific data
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function siteData(string $key, $default = null)
    {
        if (!$this->siteManager) {
            return $default;
        }
        
        $siteConfig = $this->siteManager->getSiteConfig();
        return $siteConfig[$key] ?? $default;
    }
    
    /**
     * Get current site ID
     *
     * @return string|null
     */
    public function getCurrentSiteId(): ?string
    {
        return $this->siteManager ? $this->siteManager->getCurrentSite() : null;
    }
    
    /**
     * Check if multisite is enabled
     *
     * @return bool
     */
    public function isMultisiteEnabled(): bool
    {
        return $this->siteManager ? $this->siteManager->isMultisiteEnabled() : false;
    }
    
    /**
     * Get theme asset URL
     *
     * @param string $path
     * @return string
     */
    public function asset(string $path): string
    {
        return $this->theme->getThemeAssetUrl($path);
    }
}
