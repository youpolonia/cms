<?php
/**
 * Builder v2.2 Block Renderer
 * Handles dual rendering (legacy + block-based)
 */
class BlockRenderer {
    /**
     * Render content using appropriate engine
     * @param string $content Input content
     * @return string Rendered output
     */
    public static function render(string $content): string {
        // Check if content is block-based JSON
        if (self::isBlockContent($content)) {
            return self::renderBlocks($content);
        }
        // Fallback to legacy HTML rendering
        return $content;
    }

    /**
     * Check if content is block-based JSON
     * @param string $content Content to check
     * @return bool True if block content
     */
    private static function isBlockContent(string $content): bool {
        $data = json_decode($content, true);
        return json_last_error() === JSON_ERROR_NONE 
            && isset($data['version']) 
            && isset($data['blocks']);
    }

    /**
     * Render block-based content
     * @param string $jsonContent JSON block content
     * @return string Rendered HTML
     */
    private static function renderBlocks(string $jsonContent): string {
        $data = json_decode($jsonContent, true);
        if (!isset($data['blocks'])) {
            return '';
        }

        $output = '';
        foreach ($data['blocks'] as $block) {
            if (!isset($block['type']) || !isset($block['data'])) {
                continue;
            }
            $output .= BlockManager::render($block['type'], $block['data']);
        }
        return $output;
    }

    /**
     * Convert legacy HTML to block format
     * @param string $html Legacy HTML content
     * @return string JSON block content
     */
    public static function convertLegacy(string $html): string {
        // Basic conversion - single text block
        return json_encode([
            'version' => '2.2',
            'blocks' => [[
                'type' => 'text',
                'data' => ['content' => $html]
            ]]
        ]);
    }
}
