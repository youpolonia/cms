<?php
require_once __DIR__ . '/../../core/aicontentgenerator.php';

class MetadataGenerator extends AIContentGenerator {
    const METADATA_TYPES = [
        'seo_metadata',
        'schema_org',
        'open_graph',
        'twitter_card'
    ];

    /**
     * Generate metadata for content
     */
    public static function generateMetadata(
        string $modelId,
        string $metadataType,
        array $content,
        ?string $prompt = null
    ): array {
        if (!in_array($metadataType, self::METADATA_TYPES)) {
            throw new Exception("Invalid metadata type: $metadataType");
        }

        $payload = [
            'action' => 'generate_metadata',
            'metadata_type' => $metadataType,
            'content' => $content,
            'prompt' => $prompt ?? self::getDefaultPrompt($metadataType)
        ];

        return parent::makeRequest($modelId, $payload);
    }

    /**
     * Get default prompt for metadata type
     */
    protected static function getDefaultPrompt(string $metadataType): string {
        $prompts = [
            'seo_metadata' => 'Generate SEO metadata for: {content}',
            'schema_org' => 'Create Schema.org markup for: {content}',
            'open_graph' => 'Generate Open Graph tags for: {content}',
            'twitter_card' => 'Create Twitter Card metadata for: {content}'
        ];
        return $prompts[$metadataType] ?? '';
    }
}
