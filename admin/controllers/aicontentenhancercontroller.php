<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__.'/../../includes/providers/aiintegrationprovider.php';
require_once __DIR__.'/../../includes/providers/openaiprovider.php';
require_once __DIR__.'/../../includes/providers/geminiprovider.php';

class AIContentEnhancerController {
    private $provider;
    private $config;

    public function __construct(string $provider, array $config) {
        $this->provider = $provider;
        $this->config = $config;
    }

    public function generateMeta(string $title, string $content, string $language = 'en'): array {
        $prompt = $this->buildSEOPrompt($title, $content, $language);
        $response = $this->getAIResponse($prompt);
        
        return [
            'meta_title' => $response['meta_title'] ?? '',
            'meta_description' => $response['meta_description'] ?? '',
            'keywords' => $response['keywords'] ?? [],
            'language' => $language
        ];
    }

    private function buildSEOPrompt(string $title, string $content, string $language): string {
        return "Generate SEO metadata for the following content:\n\n" .
               "Title: $title\n\n" .
               "Content: $content\n\n" .
               "Language: $language\n\n" .
               "Return JSON with: meta_title, meta_description, keywords";
    }

    private function getAIResponse(string $prompt): array {
        switch ($this->provider) {
            case 'openai':
                $client = new OpenAIProvider($this->config);
                break;
            case 'gemini':
                $client = new GeminiProvider($this->config);
                break;
            default:
                throw new Exception('Unsupported AI provider');
        }

        $response = $client->process([
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);
        return $response;
    }
}
