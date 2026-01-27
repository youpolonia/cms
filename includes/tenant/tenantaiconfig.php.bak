<?php
/**
 * Tenant AI Configuration - Handles tenant-specific AI provider settings
 * 
 * @package CMS
 * @subpackage Tenant
 */

class TenantAIConfig {
    /**
     * Get AI configuration for current tenant
     * @return array AI provider settings
     */
    public static function getConfig(): array {
        $tenant = TenantManager::getCurrentTenant();
        $config = TenantManager::getTenantConfig($tenant['tenant_id']);
        
        return $config['ai'] ?? [
            'default_provider' => 'openai',
            'providers' => [
                'openai' => [
                    'api_key' => '',
                    'model' => 'gpt-4',
                    'rate_limit' => 30,
                    'fallback_order' => 1
                ],
                'anthropic' => [
                    'api_key' => '',
                    'model' => 'claude-2',
                    'rate_limit' => 20,
                    'fallback_order' => 2  
                ]
            ]
        ];
    }

    /**
     * Get active provider configuration
     * @param string|null $provider Force specific provider
     * @return array Provider config
     */
    public static function getProviderConfig(?string $provider = null): array {
        $config = self::getConfig();
        $provider = $provider ?? $config['default_provider'];
        
        return $config['providers'][$provider] ?? [];
    }

    /**
     * Get fallback provider order
     * @return array Sorted provider names
     */
    public static function getFallbackOrder(): array {
        $config = self::getConfig();
        $providers = $config['providers'] ?? [];
        
        uasort($providers, function($a, $b) {
            return ($a['fallback_order'] ?? 999) <=> ($b['fallback_order'] ?? 999);
        });
        
        return array_keys($providers);
    }
}
