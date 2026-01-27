<?php
/**
 * AI Content Generator Module
 * Handles content generation via AI APIs with fallback mechanisms
 */

// Load Hugging Face client library
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}
require_once CMS_ROOT . '/core/ai_hf.php';

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
        // Try Hugging Face first if configured
        $hfConfig = ai_hf_config_load();

        if (ai_hf_is_configured($hfConfig)) {
            // Map options to Hugging Face parameters
            $hfOptions = [];
            if (isset($options['max_tokens'])) {
                $hfOptions['max_new_tokens'] = (int)$options['max_tokens'];
            }
            if (isset($options['temperature'])) {
                $hfOptions['temperature'] = (float)$options['temperature'];
            }
            if (isset($options['top_p'])) {
                $hfOptions['top_p'] = (float)$options['top_p'];
            }

            // Set reasonable defaults if no options provided
            if (empty($hfOptions)) {
                $hfOptions['max_new_tokens'] = 512;
            }

            // Call Hugging Face API
            $result = ai_hf_infer($hfConfig, $prompt, $hfOptions);

            if ($result['ok']) {
                // Extract generated text from response
                $generatedText = '';

                // Try to extract from JSON response first
                if (is_array($result['json'])) {
                    // HF typically returns array with generated_text key
                    if (isset($result['json'][0]['generated_text'])) {
                        $generatedText = $result['json'][0]['generated_text'];
                    } elseif (isset($result['json']['generated_text'])) {
                        $generatedText = $result['json']['generated_text'];
                    }
                }

                // Fallback to raw body if JSON extraction failed
                if (empty($generatedText) && !empty($result['body'])) {
                    $generatedText = $result['body'];
                }

                // Return if we got valid content
                $generatedText = trim($generatedText);
                if (!empty($generatedText)) {
                    return $generatedText;
                }
            }
            // If HF failed or returned empty, fall through to existing provider
        }

        // Fallback to existing provider
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
