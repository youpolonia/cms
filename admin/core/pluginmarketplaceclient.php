<?php
/**
 * Plugin Marketplace Client
 * Handles communication with remote plugin registry
 */
class PluginMarketplaceClient
{
    private const API_BASE_URL = 'https://plugins.cms.example.com/api/v1/';
    private $httpClient;
    private $cache;

    public function __construct()
    {
        $this->httpClient = new HttpClient();
        $this->cache = new FileCache(__DIR__ . '/../../cache/plugins/');
    }

    /**
     * Get available plugins from marketplace
     */
    public function getAvailablePlugins(): array
    {
        $cacheKey = 'available_plugins';
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $response = $this->httpClient->get(
            self::API_BASE_URL . 'plugins',
            ['Accept' => 'application/json']
        );

        if ($response->getStatusCode() !== 200) {
            throw new Exception("Failed to fetch plugins from marketplace");
        }

        $plugins = json_decode($response->getBody(), true);
        $this->cache->set($cacheKey, $plugins, 3600); // Cache for 1 hour

        return $plugins;
    }

    /**
     * Get plugin details
     */
    public function getPluginDetails(string $pluginId): array
    {
        $cacheKey = "plugin_{$pluginId}";
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $response = $this->httpClient->get(
            self::API_BASE_URL . "plugins/{$pluginId}",
            ['Accept' => 'application/json']
        );

        if ($response->getStatusCode() !== 200) {
            throw new Exception("Plugin not found in marketplace");
        }

        $plugin = json_decode($response->getBody(), true);
        $this->cache->set($cacheKey, $plugin, 3600);

        return $plugin;
    }

    /**
     * Verify plugin license
     */
    public function verifyLicense(string $pluginId, string $licenseKey): bool
    {
        $response = $this->httpClient->post(
            self::API_BASE_URL . "licenses/verify",
            [
                'plugin_id' => $pluginId,
                'license_key' => $licenseKey
            ]
        );

        return $response->getStatusCode() === 200;
    }
}
