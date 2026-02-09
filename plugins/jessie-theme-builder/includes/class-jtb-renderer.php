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
    private static string $context = 'preview'; // 'canvas' or 'preview'

    /**
     * Render complete JTB content
     *
     * @param array $content JTB content structure
     * @param array $options Render options:
     *                       - context: 'canvas' (editor) or 'preview' (AI preview, frontend)
     * @return string Rendered HTML
     */
    public static function render(array $content, array $options = []): string
    {
        // Reset state
        self::$css = '';
        self::$moduleIndex = 0;
        self::$usedFonts = [];
        self::$context = $options['context'] ?? 'preview';

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

        // CSS jest teraz zarządzany przez JTB_CSS_Output
        // Enqueue zebrany CSS do renderowania w <head>
        if (!empty(self::$css)) {
            JTB_CSS_Output::enqueue(self::$css, 'jtb-modules-css');
        }
        // NIE dodajemy <style> tutaj - zostanie dodany w <head> przez theme integration
        // Dla preview/canvas kontekstu dodajemy CSS inline (bo nie ma <head>)
        if (self::$context === 'preview' && !empty(self::$css)) {
            $output .= '<style id="jtb-preview-css">' . self::$css . '</style>';
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

        // Merge with default styles from JTB_Default_Styles
        $attrs = JTB_Default_Styles::mergeWithDefaults('section', $attrs);

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

        // AI Compatibility: Visual context classes
        $visualContext = $section['_visual_context'] ?? ($attrs['visual_context'] ?? null);
        if ($visualContext) {
            $classes[] = 'jtb-context-' . strtolower($visualContext);
        }

        // Stage 12: Visual Intent classes
        $visualIntent = $section['_visual_intent'] ?? ($attrs['visual_intent'] ?? null);
        if ($visualIntent) {
            $classes[] = 'jtb-vi-' . strtolower($visualIntent);
        }

        // Stage 13: Visual Density classes
        $visualDensity = $section['_visual_density'] ?? ($attrs['visual_density'] ?? null);
        if ($visualDensity) {
            $classes[] = 'jtb-vd-' . strtolower($visualDensity);
        }

        // Stage 14: Visual Scale classes
        $visualScale = $section['_visual_scale'] ?? ($attrs['visual_scale'] ?? null);
        if ($visualScale) {
            $classes[] = 'jtb-scale-' . strtolower($visualScale);
        }

        // Stage 15: Typography Scale classes
        $typographyScale = $section['_typography_scale'] ?? ($attrs['typography_scale'] ?? null);
        if ($typographyScale) {
            $classes[] = 'jtb-ts-' . strtolower($typographyScale);
        }

        // Stage 15: Text Emphasis classes
        $textEmphasis = $section['_text_emphasis'] ?? ($attrs['text_emphasis'] ?? null);
        if ($textEmphasis) {
            $classes[] = 'jtb-te-' . strtolower($textEmphasis);
        }

        // Stage 16: Emotional Tone classes
        $emotionalTone = $section['_emotional_tone'] ?? ($attrs['emotional_tone'] ?? null);
        if ($emotionalTone) {
            $classes[] = 'jtb-et-' . strtolower($emotionalTone);
        }

        // Stage 16: Attention Level classes
        $attentionLevel = $section['_attention_level'] ?? ($attrs['attention_level'] ?? null);
        if ($attentionLevel) {
            $classes[] = 'jtb-att-' . strtolower($attentionLevel);
        }

        // Stage 17: Narrative Role classes
        $narrativeRole = $section['_narrative_role'] ?? ($attrs['narrative_role'] ?? null);
        if ($narrativeRole) {
            $classes[] = 'jtb-nr-' . strtolower($narrativeRole);
        }

        $classStr = implode(' ', $classes);

        // Stage 13: Rhythm spacing inline styles
        $spacingMap = ['sm' => 24, 'md' => 48, 'lg' => 72, 'xl' => 96, '2xl' => 140];
        $inlineStyles = [];

        $beforeSpacing = $attrs['before_spacing'] ?? null;
        if ($beforeSpacing && isset($spacingMap[$beforeSpacing])) {
            $inlineStyles[] = 'margin-top:' . $spacingMap[$beforeSpacing] . 'px';
        }

        $afterSpacing = $attrs['after_spacing'] ?? null;
        if ($afterSpacing && isset($spacingMap[$afterSpacing])) {
            $inlineStyles[] = 'margin-bottom:' . $spacingMap[$afterSpacing] . 'px';
        }

        // Stage 14: Visual Scale CSS variables
        $scaleMap = ['xs' => 0.85, 'sm' => 0.92, 'md' => 1.0, 'lg' => 1.12, 'xl' => 1.25];
        if ($visualScale && isset($scaleMap[strtolower($visualScale)])) {
            $scaleValue = $scaleMap[strtolower($visualScale)];
            $inlineStyles[] = '--jtb-scale:' . $scaleValue;
            $inlineStyles[] = '--jtb-scale-heading:' . $scaleValue;
            $inlineStyles[] = '--jtb-scale-padding:' . $scaleValue;
        }

        $styleAttr = !empty($inlineStyles) ? ' style="' . implode(';', $inlineStyles) . '"' : '';

        // Build HTML
        $html = '<section id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '" class="' . $classStr . '"' . $styleAttr . '>';

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

        // Merge with default styles from JTB_Default_Styles
        $attrs = JTB_Default_Styles::mergeWithDefaults('row', $attrs);

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

        // Parse column widths for inline styles
        $columnWidths = self::parseColumnWidths($columns);

        // Build HTML
        $html = '<div id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '" class="' . $classStr . '">';

        // Render columns with width info
        $columnChildren = $row['children'] ?? [];
        foreach ($columnChildren as $index => $column) {
            $width = $columnWidths[$index] ?? null;
            $html .= self::renderColumn($column, $width);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Parse column widths string into flex values
     * ADDED 2026-02-03: Support for all column structures
     */
    private static function parseColumnWidths(string $columnsStr): array
    {
        if (empty($columnsStr) || $columnsStr === '1') {
            return [1];
        }

        $widthMap = [
            '1' => 1,
            '1_2' => 1,
            '1_3' => 1,
            '2_3' => 2,
            '1_4' => 1,
            '3_4' => 3,
            '1_5' => 1,
            '2_5' => 2,
            '3_5' => 3,
            '4_5' => 4,
            '1_6' => 1,
            '5_6' => 5,
        ];

        $parts = explode(',', $columnsStr);
        $widths = [];

        foreach ($parts as $part) {
            $part = trim($part);
            $widths[] = $widthMap[$part] ?? 1;
        }

        return $widths;
    }

    /**
     * Render a column
     * @param array $column Column data
     * @param int|null $flexValue Flex value for column width (ADDED 2026-02-03)
     */
    public static function renderColumn(array $column, ?int $flexValue = null): string
    {
        $module = JTB_Registry::get('column');

        if (!$module) {
            return '';
        }

        $attrs = $column['attrs'] ?? [];

        // Merge with default styles from JTB_Default_Styles
        $attrs = JTB_Default_Styles::mergeWithDefaults('column', $attrs);

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

        // Build inline style for flex (ADDED 2026-02-03)
        $style = '';
        if ($flexValue !== null && $flexValue > 0) {
            $style = ' style="flex: ' . $flexValue . ' 1 0; min-width: 0;"';
        }

        // Build HTML
        $html = '<div id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '" class="' . $classStr . '"' . $style . '>';

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

        // Merge with default styles from JTB_Default_Styles
        $attrs = JTB_Default_Styles::mergeWithDefaults($type, $attrs);

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

        // Canvas wrapper: Only apply in editor context, not for preview/frontend
        if (self::$context === 'canvas') {
            $html = self::wrapForCanvasPreview($html, $type, $id);
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

    /**
     * Pobierz zebrany CSS (dla backward compatibility)
     * @deprecated Use JTB_CSS_Output::getCss() instead
     */
    public static function getCollectedCss(): string
    {
        return self::$css;
    }

    /**
     * Reset CSS (dla testów i nowych renderów)
     */
    public static function resetCss(): void
    {
        self::$css = '';
        JTB_CSS_Output::reset();
    }

    /**
     * Global Preview Adapter
     * Wraps module HTML in canvas-compatible structure for preview rendering
     *
     * @param string $html Original module HTML
     * @param string $type Module type slug
     * @param string $id Module unique ID
     * @return string Wrapped HTML
     */
    private static function wrapForCanvasPreview(string $html, string $type, string $id): string
    {
        return '<div class="jtb-module-editor" data-id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '" data-type="' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '">'
             . '<div class="jtb-module-preview">' . $html . '</div>'
             . '</div>';
    }
}
