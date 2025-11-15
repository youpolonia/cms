<?php
class SEOToolkit {
    protected $pluginRegistry;
    protected $enabledPlugins = [];

    public function __construct() {
        $this->pluginRegistry = new EnhancedPluginRegistry();
        $this->loadEnabledPlugins();
    }

    protected function loadEnabledPlugins() {
        // Load plugins that extend SEO functionality
        foreach ($this->pluginRegistry->getPlugins() as $plugin) {
            if ($plugin['category'] === 'seo' && $plugin['enabled']) {
                $this->enabledPlugins[$plugin['id']] = $plugin;
            }
        }
    }

    // Core SEO Methods
    public function generateMetaTags($pageData) {
        $metaTags = $this->generateCoreMetaTags($pageData);
        
        // Allow plugins to modify meta tags
        foreach ($this->enabledPlugins as $pluginId => $plugin) {
            if (method_exists($plugin['instance'], 'modifyMetaTags')) {
                $metaTags = $plugin['instance']->modifyMetaTags($metaTags, $pageData);
            }
        }

        return $metaTags;
    }

    protected function generateCoreMetaTags($pageData) {
        return [
            'title' => $pageData['title'] ?? '',
            'description' => $pageData['description'] ?? '',
            'keywords' => $pageData['keywords'] ?? '',
            'canonical' => $pageData['canonical'] ?? ''
        ];
    }

    // Plugin Hook: Register new SEO features
    public function registerFeature($featureName, callable $handler) {
        foreach ($this->enabledPlugins as $pluginId => $plugin) {
            if (method_exists($plugin['instance'], 'handleFeatureRegistration')) {
                $plugin['instance']->handleFeatureRegistration($featureName, $handler);
            }
        }
    }

    // Plugin Hook: Schema markup generation
    public function generateSchemaMarkup($pageData) {
        $schema = [];
        
        foreach ($this->enabledPlugins as $pluginId => $plugin) {
            if (method_exists($plugin['instance'], 'generateSchema')) {
                $schema = array_merge($schema, $plugin['instance']->generateSchema($pageData));
            }
        }

        return $schema;
    }

    // Plugin Hook: Sitemap generation
    public function generateSitemap($urls) {
        foreach ($this->enabledPlugins as $pluginId => $plugin) {
            if (method_exists($plugin['instance'], 'modifySitemap')) {
                $urls = $plugin['instance']->modifySitemap($urls);
            }
        }
        return $urls;
    }
}
