<?php
/**
 * Content Renderer
 * Renders JTB content to HTML and CSS
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Renderer
{
    private static string $css = '';
    private static int $moduleIndex = 0;
    private static array $usedFonts = [];

    /**
     * Render complete JTB content
     */
    public static function render(array $content): string
    {
        // Reset state
        self::$css = '';
        self::$moduleIndex = 0;
        self::$usedFonts = [];

        $html = '';

        // Process content sections
        $sections = $content['content'] ?? [];

        foreach ($sections as $section) {
            $html .= self::renderSection($section);
        }

        // Extract Google Fonts from content
        $googleFonts = JTB_Fonts::extractFromContent($sections);

        // Wrap with container and prepend CSS
        $output = '<div class="jtb-content">';

        // Add Google Fonts link
        if (!empty($googleFonts)) {
            $output .= JTB_Fonts::getGoogleFontsLink($googleFonts);
        }

        if (!empty(self::$css)) {
            $output .= '<style>' . self::$css . '</style>';
        }

        $output .= $html;
        $output .= '</div>';

        return $output;
    }

    /**
     * Render a section
     */
    public static function renderSection(array $section): string
    {
        $module = JTB_Registry::get('section');

        if (!$module) {
            return '';
        }

        $attrs = $section['attrs'] ?? [];
        $id = $section['id'] ?? $module->generateId();
        $selector = '#' . $id;

        // Generate CSS
        self::$css .= $module->generateCss($attrs, $selector);

        // Build section classes
        $classes = ['jtb-section'];

        if (!empty($attrs['fullwidth'])) {
            $classes[] = 'jtb-section-fullwidth';
        }

        if (!empty($attrs['css_class'])) {
            $classes[] = htmlspecialchars($attrs['css_class'], ENT_QUOTES, 'UTF-8');
        }

        // Visibility classes
        if (!empty($attrs['disable_on_desktop'])) {
            $classes[] = 'jtb-hide-desktop';
        }
        if (!empty($attrs['disable_on_tablet'])) {
            $classes[] = 'jtb-hide-tablet';
        }
        if (!empty($attrs['disable_on_phone'])) {
            $classes[] = 'jtb-hide-phone';
        }

        // Animation classes
        if (!empty($attrs['animation_style']) && $attrs['animation_style'] !== 'none') {
            $classes[] = 'jtb-animated';
            $classes[] = 'jtb-animation-' . $attrs['animation_style'];
        }

        $classStr = implode(' ', $classes);

        // Build HTML
        $html = '<section id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '" class="' . $classStr . '">';

        // Inner wrapper
        $innerClass = 'jtb-section-inner';
        $html .= '<div class="' . $innerClass . '">';

        // Render rows
        $rows = $section['children'] ?? [];
        foreach ($rows as $row) {
            $html .= self::renderRow($row);
        }

        $html .= '</div>'; // .jtb-section-inner
        $html .= '</section>';

        return $html;
    }

    /**
     * Render a row
     */
    public static function renderRow(array $row): string
    {
        $module = JTB_Registry::get('row');

        if (!$module) {
            return '';
        }

        $attrs = $row['attrs'] ?? [];
        $id = $row['id'] ?? $module->generateId();
        $selector = '#' . $id;

        // Generate CSS
        self::$css .= $module->generateCss($attrs, $selector);

        // Build row classes
        $classes = ['jtb-row'];

        // Column structure class
        $columns = $attrs['columns'] ?? '1';
        $structureClass = 'jtb-row-cols-' . str_replace(',', '-', str_replace('_', '-', $columns));
        $classes[] = $structureClass;

        // Equal heights
        if (!empty($attrs['equal_heights'])) {
            $classes[] = 'jtb-row-equal-heights';
        }

        if (!empty($attrs['css_class'])) {
            $classes[] = htmlspecialchars($attrs['css_class'], ENT_QUOTES, 'UTF-8');
        }

        $classStr = implode(' ', $classes);

        // Build HTML
        $html = '<div id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '" class="' . $classStr . '">';

        // Render columns
        $columnChildren = $row['children'] ?? [];
        foreach ($columnChildren as $column) {
            $html .= self::renderColumn($column);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render a column
     */
    public static function renderColumn(array $column): string
    {
        $module = JTB_Registry::get('column');

        if (!$module) {
            return '';
        }

        $attrs = $column['attrs'] ?? [];
        $id = $column['id'] ?? $module->generateId();
        $selector = '#' . $id;

        // Generate CSS
        self::$css .= $module->generateCss($attrs, $selector);

        // Build column classes
        $classes = ['jtb-column'];

        if (!empty($attrs['css_class'])) {
            $classes[] = htmlspecialchars($attrs['css_class'], ENT_QUOTES, 'UTF-8');
        }

        $classStr = implode(' ', $classes);

        // Build HTML
        $html = '<div id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '" class="' . $classStr . '">';

        // Render modules
        $modules = $column['children'] ?? [];
        foreach ($modules as $moduleData) {
            $html .= self::renderModule($moduleData);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render a module
     */
    public static function renderModule(array $moduleData): string
    {
        $type = $moduleData['type'] ?? '';

        if (empty($type)) {
            return '';
        }

        $module = JTB_Registry::get($type);

        if (!$module) {
            return '<!-- Unknown module: ' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . ' -->';
        }

        $attrs = $moduleData['attrs'] ?? [];
        $id = $moduleData['id'] ?? $module->generateId();
        $selector = '#' . $id;

        // Set the ID in attrs for the wrapper
        $attrs['_id'] = $id;

        // Generate CSS
        self::$css .= $module->generateCss($attrs, $selector);

        // Handle child modules
        $childContent = '';
        if (!empty($moduleData['children']) && !empty($module->child_slug)) {
            foreach ($moduleData['children'] as $child) {
                $childContent .= self::renderModule($child);
            }
        }

        // Increment module index
        self::$moduleIndex++;

        // Render the module
        $html = $module->render($attrs, $childContent);

        // Wrap with ID if not already wrapped
        if (strpos($html, 'id="' . $id . '"') === false) {
            $html = '<div id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '">' . $html . '</div>';
        }

        return $html;
    }

    /**
     * Generate CSS only (without rendering HTML)
     */
    public static function generateCss(array $content): string
    {
        // Reset and render to generate CSS
        self::$css = '';
        self::$moduleIndex = 0;

        $sections = $content['content'] ?? [];

        foreach ($sections as $section) {
            self::renderSection($section);
        }

        return self::$css;
    }

    /**
     * Get current CSS
     */
    public static function getCss(): string
    {
        return self::$css;
    }
}
