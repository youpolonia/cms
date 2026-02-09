<?php
/**
 * ContentRenderer — Plugin-Agnostic Content Rendering
 * 
 * Allows plugins (JTB, etc.) to register content renderers.
 * SEO/AI tools use this to get clean HTML/text from any content type.
 * 
 * Usage:
 *   $html = ContentRenderer::render($rawContent);    // Clean HTML
 *   $text = ContentRenderer::toText($rawContent);     // Plain text
 *   $html = ContentRenderer::getRendered('page', 5);  // From DB entity
 */
class ContentRenderer {
    /** @var array Registered renderers: [name => [detector, renderer, priority]] */
    private static array $renderers = [];
    
    /**
     * Register a content renderer
     *
     * @param string   $name     Unique renderer name (e.g., 'jtb', 'legacy-tb')
     * @param callable $detector fn(string $content): bool — returns true if this renderer handles it
     * @param callable $renderer fn(string $content): string — returns clean HTML
     * @param int      $priority Lower = higher priority (default 10)
     */
    public static function register(string $name, callable $detector, callable $renderer, int $priority = 10): void {
        self::$renderers[$name] = [
            'detector' => $detector,
            'renderer' => $renderer,
            'priority' => $priority,
        ];
        // Sort by priority (lower first)
        uasort(self::$renderers, fn($a, $b) => $a['priority'] <=> $b['priority']);
    }
    
    /**
     * Render raw content to clean HTML
     * Tries registered renderers in priority order. Falls back to as-is.
     *
     * @param string $rawContent Raw content (may contain builder markup)
     * @return string Clean semantic HTML
     */
    public static function render(string $rawContent): string {
        if (empty(trim($rawContent))) {
            return '';
        }
        
        foreach (self::$renderers as $name => $renderer) {
            try {
                if (($renderer['detector'])($rawContent)) {
                    $result = ($renderer['renderer'])($rawContent);
                    if (!empty($result)) {
                        return $result;
                    }
                }
            } catch (\Throwable $e) {
                error_log("ContentRenderer [{$name}] error: " . $e->getMessage());
                // Continue to next renderer
            }
        }
        
        // Fallback: return content as-is (plain HTML from WYSIWYG editor)
        return $rawContent;
    }
    
    /**
     * Convert raw content to plain text (for SEO analysis, word count, etc.)
     *
     * @param string $rawContent Raw content
     * @return string Clean plain text
     */
    public static function toText(string $rawContent): string {
        $html = self::render($rawContent);
        if (empty($html)) {
            return '';
        }
        
        // Preserve paragraph breaks
        $text = preg_replace('/<\/(p|h[1-6]|li|div|section|br\s*\/?)>/i', "$0\n", $html);
        // Strip HTML tags
        $text = strip_tags($text);
        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Normalize whitespace
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        
        return trim($text);
    }
    
    /**
     * Get rendered content for a DB entity (page or article)
     *
     * @param string $type Entity type ('page', 'article')
     * @param int    $id   Entity ID
     * @return string Clean HTML (empty string if not found)
     */
    public static function getRendered(string $type, int $id): string {
        try {
            $db = \core\Database::connection();
            
            switch ($type) {
                case 'page':
                    $stmt = $db->prepare("SELECT content FROM pages WHERE id = ?");
                    break;
                case 'article':
                    $stmt = $db->prepare("SELECT content FROM articles WHERE id = ?");
                    break;
                case 'jtb_page':
                    $stmt = $db->prepare("SELECT content FROM jtb_pages WHERE id = ?");
                    break;
                default:
                    return '';
            }
            
            $stmt->execute([$id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$row || empty($row['content'])) {
                return '';
            }
            
            return self::render($row['content']);
        } catch (\Throwable $e) {
            error_log("ContentRenderer::getRendered({$type}, {$id}) error: " . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Check if any renderer is registered
     */
    public static function hasRenderers(): bool {
        return !empty(self::$renderers);
    }
    
    /**
     * Get list of registered renderer names
     */
    public static function getRegisteredNames(): array {
        return array_keys(self::$renderers);
    }
}

// Register built-in renderer for legacy TB content (pages with tb-section markup)
ContentRenderer::register('legacy-tb',
    function(string $content): bool {
        return str_contains($content, 'tb-section') || str_contains($content, 'tb-row') || str_contains($content, 'tb-module');
    },
    function(string $content): string {
        // Strip TB structural wrappers but keep inner content
        // Remove section/row/column wrappers
        $content = preg_replace('/<section[^>]*class="[^"]*tb-section[^"]*"[^>]*>/i', '', $content);
        $content = preg_replace('/<div[^>]*class="[^"]*tb-(section-inner|row|column|col-\d+)[^"]*"[^>]*>/i', '', $content);
        // Remove module wrappers but keep content inside
        $content = preg_replace('/<div[^>]*class="[^"]*tb-module\b[^"]*"[^>]*>/i', '', $content);
        $content = preg_replace('/<div[^>]*class="[^"]*tb-(text-content|form-container|icon-wrapper|image-wrapper|video-wrapper)[^"]*"[^>]*>/i', '', $content);
        // Clean up leftover closing divs/sections (imperfect but good enough for text extraction)
        // Count opens vs closes to avoid breaking structure too much
        $content = preg_replace('/<\/section>/i', '', $content);
        // Remove style attributes
        $content = preg_replace('/\s+style="[^"]*"/i', '', $content);
        // Remove data-* attributes
        $content = preg_replace('/\s+data-[a-z-]+="[^"]*"/i', '', $content);
        // Remove id attributes with builder prefixes
        $content = preg_replace('/\s+id="(section|row|col|mod)_[^"]*"/i', '', $content);
        // Remove class attributes with builder prefixes
        $content = preg_replace('/\s+class="[^"]*tb-[^"]*"/i', '', $content);
        // Clean up empty divs
        $content = preg_replace('/<div\s*>\s*<\/div>/i', '', $content);
        // Collapse extra whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        
        return trim($content);
    },
    20 // Lower priority — JTB renderer should match first if JTB content
);
