<?php
/**
 * AI Content Generator Module
 * Handles content generation via AI APIs with fallback mechanisms
 */
class AIContentGenerator {
    private $apiKey;
    private $provider;
    private $fallbackContent;
    private $validationRules = [];

    public function __construct(array $config) {
        $this->apiKey = $config['api_key'] ?? '';
        $this->provider = $config['provider'] ?? 'openai';
        $this->fallbackContent = $config['fallback_content'] ?? '';
        $this->validationRules = $config['validation_rules'] ?? [];
    }

    /**
     * Generate content from prompt
     */
    public function generate(string $prompt, array $options = []): string {
        try {
            $content = $this->callAIApi($prompt, $options);
            
            if ($this->validateContent($content)) {
                return $content;
            }
            
            return $this->handleInvalidContent($content);
        } catch (Exception $e) {
            error_log("AI Content Generation failed: " . $e->getMessage());
            return $this->fallbackContent;
        }
    }

    private function callAIApi(string $prompt, array $options): string {
        // Implementation will vary based on provider
        $providerClass = $this->getProviderClass();
        return $providerClass::generate($prompt, $options);
    }

    private function getProviderClass(): string {
        $providers = [
            'openai' => 'OpenAIProvider',
            'gemini' => 'GeminiProvider'
        ];
        return $providers[$this->provider] ?? $providers['openai'];
    }

    private function validateContent(string $content): bool {
        foreach ($this->validationRules as $rule) {
            if (!$rule($content)) {
                return false;
            }
        }
        return true;
    }

    private function handleInvalidContent(string $content): string {
        // Log invalid content attempts
        error_log("Invalid content generated: " . substr($content, 0, 100));
        return $this->fallbackContent;
    }
}
