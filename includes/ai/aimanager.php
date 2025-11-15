<?php
require_once __DIR__ . '/AIProviderInterface.php';
require_once __DIR__ . '/openaiprovider.php';
require_once __DIR__ . '/huggingfaceprovider.php';

class AIManager {
    private array $providers = [];
    private ?string $defaultProvider = null;
    private array $config;

    public function __construct(array $config) {
        $this->config = $config;
        $this->initializeProviders();
    }

    private function initializeProviders(): void {
        foreach ($this->config['providers'] as $providerName => $providerConfig) {
            $providerClass = "{$providerName}Provider";
            if (class_exists($providerClass)) {
                $this->providers[$providerName] = new $providerClass($providerConfig);
                if ($providerConfig['is_default'] ?? false) {
                    $this->defaultProvider = $providerName;
                }
            }
        }
    }

    public function getProvider(?string $providerName = null): AIProviderInterface {
        $providerName = $providerName ?? $this->defaultProvider;
        if (!isset($this->providers[$providerName])) {
            throw new InvalidArgumentException("Provider {$providerName} not found");
        }
        return $this->providers[$providerName];
    }

    public function generateContent(
        string $template,
        array $variables = [],
        array $options = [],
        ?string $providerName = null
    ): string {
        $provider = $this->getProvider($providerName);
        return $provider->generateContent($template, $variables, $options);
    }

    public function getAvailableProviders(): array {
        return array_keys($this->providers);
    }

    public function getProviderConfig(string $providerName): array {
        return $this->getProvider($providerName)->getConfig();
    }

    public function getProviderUsageStats(string $providerName): array {
        return $this->getProvider($providerName)->getUsageStats();
    }

    public function isProviderAvailable(string $providerName): bool {
        return $this->getProvider($providerName)->isAvailable();
    }
}
