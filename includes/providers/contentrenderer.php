<?php
namespace Includes\Providers;

class ContentRenderer {
    /**
     * Render content using appropriate template
     * @param string $type Content type
     * @param array $content Content data
     * @param array $fields Field definitions
     * @param array $context Additional context
     * @return string Rendered content
     */
    public static function render(string $type, array $content, array $fields, array $context = []): string {
        $activeTheme = $context['active_theme'] ?? 'default';
        
        // Primary template path
        $primaryTemplate = "themes/{$activeTheme}/content_types/{$type}.php";
        
        // Fallback template path
        $fallbackTemplate = "views/content_types/default.php";
        
        // Check if primary template exists
        if (file_exists($primaryTemplate)) {
            return self::renderTemplate($primaryTemplate, $content, $fields, $context);
        }
        
        // Use fallback template
        return self::renderTemplate($fallbackTemplate, $content, $fields, $context);
    }

    /**
     * Render template with variables
     * @param string $templatePath Path to template file
     * @param array $content Content data
     * @param array $fields Field definitions
     * @param array $context Additional context
     * @return string Rendered content
     */
    private static function renderTemplate(string $templatePath, array $content, array $fields, array $context): string {
        // Extract variables for template
        $content = $content;
        $fields = $fields;
        $context = $context;
        
        // Start output buffering
        ob_start();
        require_once $templatePath;
        return ob_get_clean();
    }
}
