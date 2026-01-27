<?php
/**
 * PluginMarketplaceService - Handles marketplace integration
 */
class PluginMarketplaceService {
    private $apiEndpoint;
    private $cache;
    private $authToken;

    public function __construct() {
        $this->apiEndpoint = 'https://marketplace.cms.example.com/api/v1';
        $this->cache = new CacheService();
        $this->authToken = Config::get('marketplace.token');
    }

    /**
     * Get available plugins from marketplace
     */
    public function getAvailablePlugins(): array {
        $cached = $this->cache->get('marketplace_plugins');
        if ($cached) {
            return $cached;
        }

        $response = $this->makeRequest('/plugins');
        $plugins = json_decode($response, true) ?? [];

        // Cache for 1 hour
        $this->cache->set('marketplace_plugins', $plugins, 3600);
        
        return $plugins;
    }

    /**
     * Get plugin details
     */
    public function getPluginDetails(string $pluginId): array {
        $response = $this->makeRequest("/plugins/$pluginId");
        return json_decode($response, true) ?? [];
    }

    /**
     * Search plugins
     */
    public function searchPlugins(string $query): array {
        $response = $this->makeRequest("/plugins/search?q=" . urlencode($query));
        return json_decode($response, true) ?? [];
    }

    /**
     * Make authenticated API request
     */
    private function makeRequest(string $path): string {
        $url = $this->apiEndpoint . $path;
        $headers = [
            'Authorization: Bearer ' . $this->authToken,
            'Accept: application/json'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('Marketplace API error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        return $response;
    }
}
