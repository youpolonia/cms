<?php
/**
 * Handles AI content generation requests and responses
 */

// Load Hugging Face client library for status checks
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 2));
}
require_once CMS_ROOT . '/core/ai_hf.php';

class RequestHandler {
    private $generator;
    private $allowedTypes = ['article', 'summary', 'meta_description'];

    public function __construct(AIContentGenerator $generator) {
        $this->generator = $generator;
    }

    public function handleRequest(array $request): array {
        try {
            $this->validateRequest($request);
            $content = $this->generateContent($request);
            return $this->formatResponse($content, $request);
        } catch (InvalidRequestException $e) {
            return $this->formatError($e->getMessage(), 400);
        } catch (Exception $e) {
            return $this->formatError('Content generation failed', 500);
        }
    }

    private function validateRequest(array $request): void {
        if (empty($request['prompt'])) {
            throw new InvalidRequestException('Prompt is required');
        }

        if (!empty($request['type']) && !in_array($request['type'], $this->allowedTypes)) {
            throw new InvalidRequestException('Invalid content type');
        }
    }

    private function generateContent(array $request): string {
        $options = [
            'temperature' => $request['temperature'] ?? 0.7,
            'max_tokens' => $request['max_tokens'] ?? 1000,
            'type' => $request['type'] ?? 'article'
        ];
        
        return $this->generator->generate($request['prompt'], $options);
    }

    private function formatResponse(string $content, array $request): array {
        // Check if Hugging Face is currently configured
        $hfConfig = ai_hf_config_load();
        $hfEnabled = ai_hf_is_configured($hfConfig);

        return [
            'success' => true,
            'content' => $content,
            'type' => $request['type'] ?? 'article',
            'length' => strlen($content),
            'timestamp' => time(),
            'hf_enabled' => $hfEnabled
        ];
    }

    private function formatError(string $message, int $code): array {
        return [
            'success' => false,
            'error' => $message,
            'code' => $code,
            'timestamp' => time()
        ];
    }
}

class InvalidRequestException extends Exception {}
