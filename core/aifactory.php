<?php
require_once __DIR__ . '/aiservice.php';
require_once __DIR__ . '/openaiclient.php';
require_once __DIR__ . '/geminiclient.php';

class AIFactory {
    public static function createService(int $tenantId, array $providers): AIService {
        $service = new AIService($tenantId);
        
        foreach ($providers as $provider => $config) {
            if (!empty($config['api_key'])) {
                $service->addClient($provider, $config);
            }
        }

        return $service;
    }

    public static function getDefaultService(int $tenantId): AIService {
        $providers = require __DIR__ . '/../config/ai_providers.php';
        return self::createService($tenantId, $providers);
    }
}
