<?php
require_once __DIR__.'/aicoremanager.php';

/**
 * Content generation handler for AI models
 */
class AIContentGenerator extends AICoreManager {
    const CONTENT_TYPES = [
        'blog_post',
        'landing_page', 
        'product_description',
        'seo_article'
    ];

    /**
     * Generate content using specified model
     */
    public static function generate(
        string $modelId,
        string $contentType,
        array $parameters,
        ?string $prompt = null
    ): array {
        if (!in_array($contentType, self::CONTENT_TYPES)) {
            throw new Exception("Invalid content type: $contentType");
        }

        $payload = [
            'content_type' => $contentType,
            'parameters' => $parameters,
            'prompt' => $prompt ?? self::getDefaultPrompt($contentType)
        ];

        return self::makeRequest($modelId, $payload);
    }

    /**
     * Get default prompt for content type
     */
    protected static function getDefaultPrompt(string $contentType): string {
        $prompts = [
            'blog_post' => 'Write a comprehensive blog post about: {topic}',
            'landing_page' => 'Create compelling landing page copy for: {product}',
            // Additional default prompts...
        ];
        return $prompts[$contentType] ?? '';
    }

    /**
     * Enhance existing content
     */
    public static function enhance(
        string $modelId,
        string $content,
        string $enhancementType
    ): array {
        $payload = [
            'action' => 'enhance',
            'content' => $content,
            'enhancement_type' => $enhancementType
        ];
        return self::makeRequest($modelId, $payload);
    }
}
