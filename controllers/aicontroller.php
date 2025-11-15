<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

class AIController {
    private const MAX_INPUT_LENGTH = 2000;
    private const VALID_TONES = ['professional', 'casual', 'friendly', 'academic'];
    
    /**
     * Generate content from title
     * @param array $input Request data
     * @return array Response
     */
    public function generate(array $input): array {
        // Validate input
        if (empty($input['title']) || !is_string($input['title'])) {
            return ['error' => 'Invalid title parameter'];
        }

        // Sanitize input
        $title = $this->sanitizeText($input['title']);
        $options = $this->sanitizeOptions($input['options'] ?? []);

        // Generate content using AI service
        try {
            $content = $this->callAIService('generate', [
                'title' => $title,
                'options' => $options
            ]);
            
            return [
                'status' => 'success',
                'content' => $content
            ];
        } catch (Exception $e) {
            return ['error' => 'AI service error: ' . $e->getMessage()];
        }
    }

    /**
     * Expand existing paragraph
     * @param array $input Request data
     * @return array Response
     */
    public function expand(array $input): array {
        if (empty($input['text']) || !is_string($input['text'])) {
            return ['error' => 'Invalid text parameter'];
        }

        $text = $this->sanitizeText($input['text']);
        $length = min((int)($input['length'] ?? 200), 1000);

        try {
            $expanded = $this->callAIService('expand', [
                'text' => $text,
                'length' => $length
            ]);
            
            return [
                'status' => 'success',
                'content' => $expanded
            ];
        } catch (Exception $e) {
            return ['error' => 'AI service error: ' . $e->getMessage()];
        }
    }

    /**
     * Rewrite with new tone
     * @param array $input Request data
     * @return array Response
     */
    public function rewrite(array $input): array {
        if (empty($input['text']) || !is_string($input['text'])) {
            return ['error' => 'Invalid text parameter'];
        }
        if (empty($input['tone']) || !in_array($input['tone'], self::VALID_TONES)) {
            return ['error' => 'Invalid tone parameter'];
        }

        $text = $this->sanitizeText($input['text']);
        $tone = $this->sanitizeText($input['tone']);

        try {
            $rewritten = $this->callAIService('rewrite', [
                'text' => $text,
                'tone' => $tone
            ]);
            
            return [
                'status' => 'success',
                'content' => $rewritten
            ];
        } catch (Exception $e) {
            return ['error' => 'AI service error: ' . $e->getMessage()];
        }
    }

    /**
     * Call AI service with proper authentication
     */
    private function callAIService(string $operation, array $data): string {
        // TODO: Implement actual AI service integration
        // This is a placeholder that would be replaced with real API calls
        return "Generated content based on: " . json_encode($data);
    }

    /**
     * Sanitize text input
     */
    private function sanitizeText(string $text): string {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        return substr($text, 0, self::MAX_INPUT_LENGTH);
    }

    /**
     * Sanitize options array
     */
    private function sanitizeOptions(array $options): array {
        $clean = [];
        foreach ($options as $key => $value) {
            if (is_string($value)) {
                $clean[$key] = $this->sanitizeText($value);
            } elseif (is_array($value)) {
                $clean[$key] = $this->sanitizeOptions($value);
            } else {
                $clean[$key] = $value;
            }
        }
        return $clean;
    }
}
