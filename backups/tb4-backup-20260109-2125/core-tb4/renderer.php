<?php
/**
 * TB 4.0 Renderer
 *
 * Renders HTML/CSS from TB4 JSON content structure.
 * Parses sections → rows → columns → modules and generates
 * combined HTML output with responsive CSS.
 *
 * @package Core\TB4
 * @version 4.0
 */

namespace Core\TB4;

require_once __DIR__ . '/init.php';

class Renderer
{
    /**
     * Parsed content structure
     */
    private array $content = [];

    /**
     * Collected CSS from all rendered modules
     */
    private array $css_collection = [];

    /**
     * Module registry instance
     */
    private ModuleRegistry $registry;

    /**
     * Responsive breakpoints
     */
    private array $breakpoints = [
        'desktop' => null,
        'tablet' => 980,
        'phone' => 767
    ];

    /**
     * Unique ID counter for CSS selectors
     */
    private int $id_counter = 0;

    /**
     * Constructor
     *
     * @param array $content_json Parsed JSON content from tb4_pages.content_json
     */
    public function __construct(array $content_json)
    {
        $this->content = $content_json;
        $this->registry = ModuleRegistry::getInstance();
    }

    /**
     * Render complete page HTML
     *
     * @return string Complete HTML output
     */
    public function render(): string
    {
        $html = '';
        $this->css_collection = [];
        $this->id_counter = 0;

        $sections = $this->content['sections'] ?? [];

        foreach ($sections as $section) {
            $html .= $this->render_section($section);
        }

        return $html;
    }

    /**
     * Render a section with its rows
     *
     * @param array $section Section data
     * @return string Section HTML
     */
    public function render_section(array $section): string
    {
        $settings = $section['settings'] ?? [];
        $rows = $section['rows'] ?? [];
        $unique_id = $this->generate_unique_id('section');

        // Get section module for rendering settings
        $section_module = $this->load_module_class('section');

        // Build section styles
        $styles = [];
        $inner_styles = [];

        // Background
        $bg_color = $settings['background_color'] ?? '';
        $bg_image = $settings['background_image'] ?? '';
        $bg_size = $settings['background_size'] ?? 'cover';
        $bg_position = $settings['background_position'] ?? 'center center';

        if ($bg_color) {
            $styles[] = 'background-color:' . $this->esc_attr($bg_color);
        }
        if ($bg_image) {
            $styles[] = 'background-image:url(' . $this->esc_attr($bg_image) . ')';
            $styles[] = 'background-size:' . $this->esc_attr($bg_size);
            $styles[] = 'background-position:' . $this->esc_attr($bg_position);
            $styles[] = 'background-repeat:no-repeat';
        }

        // Min height
        $min_height = $settings['min_height'] ?? '';
        if ($min_height) {
            $styles[] = 'min-height:' . $this->esc_attr($min_height);
        }

        // Padding
        $padding = $this->get_spacing_value($settings['padding'] ?? [], 'desktop');
        if ($padding) {
            $styles[] = 'padding:' . $padding;
        }

        // Animation
        $animation = $settings['animation'] ?? '';
        $animation_attr = '';
        if ($animation) {
            $animation_attr = ' data-animation="' . $this->esc_attr($animation) . '"';
        }

        // Fullwidth
        $fullwidth = $settings['fullwidth'] ?? false;
        if ($fullwidth) {
            $inner_styles[] = 'width:100%';
        } else {
            $inner_styles[] = 'max-width:1200px';
            $inner_styles[] = 'margin:0 auto';
        }

        // CSS ID and classes
        $css_id = $settings['css_id'] ?? '';
        $css_class = $settings['css_class'] ?? '';
        $id_attr = $css_id ? ' id="' . $this->esc_attr($css_id) . '"' : '';
        $class_attr = 'tb4-section';
        if ($css_class) {
            $class_attr .= ' ' . $this->esc_attr($css_class);
        }

        // Generate advanced CSS if module available
        if ($section_module) {
            $css = $section_module->generate_module_css($unique_id, $settings);
            if ($css) {
                $this->css_collection[] = $css;
            }
        }

        // Generate responsive padding CSS
        $this->generate_responsive_spacing_css($unique_id, $settings);

        // Render rows
        $rows_html = '';
        foreach ($rows as $row) {
            $rows_html .= $this->render_row($row);
        }

        $style_attr = !empty($styles) ? ' style="' . implode(';', $styles) . '"' : '';
        $inner_style_attr = !empty($inner_styles) ? ' style="' . implode(';', $inner_styles) . '"' : '';

        return sprintf(
            '<section%s class="%s" data-tb4-id="%s"%s%s><div class="tb4-section__inner"%s>%s</div></section>',
            $id_attr,
            $class_attr,
            $unique_id,
            $style_attr,
            $animation_attr,
            $inner_style_attr,
            $rows_html
        );
    }

    /**
     * Render a row with its columns
     *
     * @param array $row Row data
     * @return string Row HTML
     */
    public function render_row(array $row): string
    {
        $settings = $row['settings'] ?? [];
        $columns = $row['columns'] ?? [];
        $unique_id = $this->generate_unique_id('row');

        // Row module for advanced CSS
        $row_module = $this->load_module_class('row');

        // Build row styles
        $styles = [
            'display:flex',
            'flex-wrap:' . (($settings['wrap'] ?? true) ? 'wrap' : 'nowrap')
        ];

        // Gap
        $gap = $settings['gap'] ?? '24px';
        if ($gap) {
            $styles[] = 'gap:' . $this->esc_attr($gap);
        }

        // Alignment
        $align_items = $settings['align_items'] ?? 'stretch';
        $justify_content = $settings['justify_content'] ?? 'flex-start';
        $styles[] = 'align-items:' . $this->esc_attr($align_items);
        $styles[] = 'justify-content:' . $this->esc_attr($justify_content);

        // CSS ID and classes
        $css_id = $settings['css_id'] ?? '';
        $css_class = $settings['css_class'] ?? '';
        $id_attr = $css_id ? ' id="' . $this->esc_attr($css_id) . '"' : '';
        $class_attr = 'tb4-row';
        if ($css_class) {
            $class_attr .= ' ' . $this->esc_attr($css_class);
        }

        // Generate advanced CSS
        if ($row_module) {
            $css = $row_module->generate_module_css($unique_id, $settings);
            if ($css) {
                $this->css_collection[] = $css;
            }
        }

        // Calculate column widths based on layout
        $layout = $settings['columns'] ?? '2';
        $column_widths = $this->parse_column_layout($layout, count($columns));

        // Render columns
        $columns_html = '';
        foreach ($columns as $index => $column) {
            $width = $column_widths[$index] ?? 'auto';
            $columns_html .= $this->render_column($column, $width);
        }

        $style_attr = ' style="' . implode(';', $styles) . '"';

        return sprintf(
            '<div%s class="%s" data-tb4-id="%s"%s>%s</div>',
            $id_attr,
            $class_attr,
            $unique_id,
            $style_attr,
            $columns_html
        );
    }

    /**
     * Render a column with its modules
     *
     * @param array $column Column data
     * @param string $width Column width from row layout
     * @return string Column HTML
     */
    public function render_column(array $column, string $width = 'auto'): string
    {
        $settings = $column['settings'] ?? [];
        $modules = $column['modules'] ?? [];
        $unique_id = $this->generate_unique_id('column');

        // Column module for advanced CSS
        $column_module = $this->load_module_class('column');

        // Build column styles
        $styles = [
            'display:flex',
            'flex-direction:column'
        ];

        // Width (from row layout or explicit setting)
        $explicit_width = $settings['width'] ?? '';
        $final_width = $explicit_width ?: $width;

        if ($final_width && $final_width !== 'auto') {
            // Handle flex basis with gap consideration
            $styles[] = 'flex:0 0 calc(' . $this->esc_attr($final_width) . ' - 12px)';
        } else {
            $styles[] = 'flex:1';
        }

        // Background
        $bg_color = $settings['background_color'] ?? '';
        if ($bg_color) {
            $styles[] = 'background-color:' . $this->esc_attr($bg_color);
        }

        // Vertical alignment
        $vertical_align = $settings['vertical_align'] ?? 'flex-start';
        $styles[] = 'justify-content:' . $this->esc_attr($vertical_align);

        // Text alignment
        $text_align = $settings['text_align'] ?? 'left';
        $styles[] = 'text-align:' . $this->esc_attr($text_align);

        // CSS ID and classes
        $css_id = $settings['css_id'] ?? '';
        $css_class = $settings['css_class'] ?? '';
        $id_attr = $css_id ? ' id="' . $this->esc_attr($css_id) . '"' : '';
        $class_attr = 'tb4-column';
        if ($css_class) {
            $class_attr .= ' ' . $this->esc_attr($css_class);
        }

        // Generate advanced CSS
        if ($column_module) {
            $css = $column_module->generate_module_css($unique_id, $settings);
            if ($css) {
                $this->css_collection[] = $css;
            }
        }

        // Render modules
        $modules_html = '';
        foreach ($modules as $module) {
            $modules_html .= $this->render_module($module);
        }

        $style_attr = ' style="' . implode(';', $styles) . '"';

        return sprintf(
            '<div%s class="%s" data-tb4-id="%s"%s>%s</div>',
            $id_attr,
            $class_attr,
            $unique_id,
            $style_attr,
            $modules_html
        );
    }

    /**
     * Render a content module
     *
     * @param array $module Module data
     * @return string Module HTML
     */
    public function render_module(array $module): string
    {
        $type = $module['type'] ?? '';
        if (!$type) {
            return '';
        }

        // Normalize module type (strip tb4_ prefix if present)
        $type = preg_replace('/^tb4_/', '', $type);

        $module_class = $this->load_module_class($type);
        if (!$module_class) {
            return '<!-- Unknown module type: ' . $this->esc_attr($type) . ' -->';
        }

        $unique_id = $this->generate_unique_id('module');

        // Merge content, design, and advanced settings
        $content = $module['content'] ?? [];
        $design = $module['design'] ?? [];
        $advanced = $module['advanced'] ?? [];
        $settings = array_merge($content, $design, $advanced);

        // Merge with module defaults
        $settings = $module_class->merge_with_defaults($settings);

        // Generate module CSS
        $css = $module_class->generate_module_css($unique_id, $settings);
        if ($css) {
            $this->css_collection[] = $css;
        }

        // Render module HTML
        $html = $module_class->render($settings);

        // Wrap with unique ID for CSS targeting
        $css_id = $settings['css_id'] ?? '';
        $css_class = $settings['css_class'] ?? '';
        $wrapper_id = $css_id ?: $unique_id;
        $wrapper_classes = ['tb4-module', 'tb4-module--' . $this->esc_attr($type)];
        if ($css_class) {
            $wrapper_classes[] = $this->esc_attr($css_class);
        }

        return sprintf(
            '<div id="%s" class="%s" data-tb4-type="%s">%s</div>',
            $this->esc_attr($wrapper_id),
            implode(' ', $wrapper_classes),
            $this->esc_attr($type),
            $html
        );
    }

    /**
     * Generate combined CSS for all rendered modules
     *
     * @return string Combined CSS with responsive breakpoints
     */
    public function generate_css(): string
    {
        if (empty($this->css_collection)) {
            return '';
        }

        $css = "/* TB4 Generated CSS */\n";

        // Base styles
        $css .= ".tb4-section { position: relative; }\n";
        $css .= ".tb4-row { width: 100%; }\n";
        $css .= ".tb4-column { box-sizing: border-box; }\n";
        $css .= ".tb4-module { position: relative; }\n\n";

        // Collected module CSS
        $css .= implode("\n", $this->css_collection);

        // Responsive wrapper for phone breakpoint
        $css .= "\n/* Phone Responsive Styles */\n";
        $css .= "@media (max-width: {$this->breakpoints['phone']}px) {\n";
        $css .= "  .tb4-row { flex-direction: column; }\n";
        $css .= "  .tb4-column { flex: 0 0 100% !important; }\n";
        $css .= "}\n";

        return $css;
    }

    /**
     * Get cached CSS for a page
     *
     * @param int $page_id Page ID
     * @return string|null Cached CSS or null
     */
    public function get_cached_css(int $page_id): ?string
    {
        $db = \core\Database::connection();

        $stmt = $db->prepare("SELECT css_cache FROM tb4_pages WHERE page_id = ?");
        $stmt->execute([$page_id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result && !empty($result['css_cache'])) {
            return $result['css_cache'];
        }

        return null;
    }

    /**
     * Save CSS cache for a page
     *
     * @param int $page_id Page ID
     * @param string $css Generated CSS
     * @return void
     */
    public function save_css_cache(int $page_id, string $css): void
    {
        $db = \core\Database::connection();

        $stmt = $db->prepare("UPDATE tb4_pages SET css_cache = ? WHERE page_id = ?");
        $stmt->execute([$css, $page_id]);
    }

    /**
     * Load and instantiate a module class by type
     *
     * @param string $type Module type/slug
     * @return \Core\TB4\Modules\Module|null Module instance or null
     */
    private function load_module_class(string $type): ?\Core\TB4\Modules\Module
    {
        return $this->registry->getModule($type);
    }

    /**
     * Generate a unique ID for CSS targeting
     *
     * @param string $prefix ID prefix
     * @return string Unique ID
     */
    private function generate_unique_id(string $prefix): string
    {
        $this->id_counter++;
        return 'tb4_' . $prefix . '_' . $this->id_counter;
    }

    /**
     * Parse column layout string to width percentages
     *
     * @param string $layout Layout string (e.g., '2', '1_2', '3')
     * @param int $count Number of columns
     * @return array Column widths
     */
    private function parse_column_layout(string $layout, int $count): array
    {
        $layouts = [
            '1' => ['100%'],
            '2' => ['50%', '50%'],
            '3' => ['33.333%', '33.333%', '33.333%'],
            '4' => ['25%', '25%', '25%', '25%'],
            '1_2' => ['33.333%', '66.666%'],
            '2_1' => ['66.666%', '33.333%'],
            '1_3' => ['25%', '75%'],
            '3_1' => ['75%', '25%'],
            '1_1_2' => ['25%', '25%', '50%'],
            '2_1_1' => ['50%', '25%', '25%'],
            '1_2_1' => ['25%', '50%', '25%']
        ];

        if (isset($layouts[$layout])) {
            return $layouts[$layout];
        }

        // Default: equal widths
        $width = (100 / max($count, 1)) . '%';
        return array_fill(0, $count, $width);
    }

    /**
     * Get spacing value string from spacing array
     *
     * @param array $spacing Spacing array with top/right/bottom/left
     * @param string $device Device type (desktop, tablet, phone)
     * @return string CSS spacing value
     */
    private function get_spacing_value(array $spacing, string $device = 'desktop'): string
    {
        $values = $spacing[$device] ?? $spacing;

        if (!is_array($values)) {
            return '';
        }

        $top = $values['top'] ?? '';
        $right = $values['right'] ?? '';
        $bottom = $values['bottom'] ?? '';
        $left = $values['left'] ?? '';

        if ($top === '' && $right === '' && $bottom === '' && $left === '') {
            return '';
        }

        $top = $top !== '' ? $top : '0';
        $right = $right !== '' ? $right : '0';
        $bottom = $bottom !== '' ? $bottom : '0';
        $left = $left !== '' ? $left : '0';

        return "{$top} {$right} {$bottom} {$left}";
    }

    /**
     * Generate responsive spacing CSS for an element
     *
     * @param string $unique_id Element unique ID
     * @param array $settings Element settings
     * @return void
     */
    private function generate_responsive_spacing_css(string $unique_id, array $settings): void
    {
        $padding = $settings['padding'] ?? [];
        $margin = $settings['margin'] ?? [];

        $tablet_css = [];
        $phone_css = [];

        // Tablet padding
        $tablet_padding = $this->get_spacing_value($padding, 'tablet');
        if ($tablet_padding) {
            $tablet_css[] = "padding: {$tablet_padding}";
        }

        // Phone padding
        $phone_padding = $this->get_spacing_value($padding, 'phone');
        if ($phone_padding) {
            $phone_css[] = "padding: {$phone_padding}";
        }

        // Tablet margin
        $tablet_margin = $this->get_spacing_value($margin, 'tablet');
        if ($tablet_margin) {
            $tablet_css[] = "margin: {$tablet_margin}";
        }

        // Phone margin
        $phone_margin = $this->get_spacing_value($margin, 'phone');
        if ($phone_margin) {
            $phone_css[] = "margin: {$phone_margin}";
        }

        $selector = '[data-tb4-id="' . $unique_id . '"]';

        if (!empty($tablet_css)) {
            $this->css_collection[] = "@media (max-width: {$this->breakpoints['tablet']}px) {\n" .
                "  {$selector} {\n    " . implode(";\n    ", $tablet_css) . ";\n  }\n}";
        }

        if (!empty($phone_css)) {
            $this->css_collection[] = "@media (max-width: {$this->breakpoints['phone']}px) {\n" .
                "  {$selector} {\n    " . implode(";\n    ", $phone_css) . ";\n  }\n}";
        }
    }

    /**
     * Escape attribute value
     *
     * @param string $text Text to escape
     * @return string Escaped text
     */
    private function esc_attr(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Render a page with cached or freshly generated CSS
     *
     * @param int $page_id Page ID for cache lookup
     * @return array ['html' => string, 'css' => string]
     */
    public function render_page(int $page_id): array
    {
        $html = $this->render();

        // Try to get cached CSS
        $css = $this->get_cached_css($page_id);

        if ($css === null) {
            // Generate and cache CSS
            $css = $this->generate_css();
            $this->save_css_cache($page_id, $css);
        }

        return [
            'html' => $html,
            'css' => $css
        ];
    }

    /**
     * Invalidate CSS cache for a page
     *
     * @param int $page_id Page ID
     * @return void
     */
    public function invalidate_cache(int $page_id): void
    {
        $db = \core\Database::connection();

        $stmt = $db->prepare("UPDATE tb4_pages SET css_cache = NULL WHERE page_id = ?");
        $stmt->execute([$page_id]);
    }

    /**
     * Get content structure
     *
     * @return array Content array
     */
    public function get_content(): array
    {
        return $this->content;
    }

    /**
     * Set content structure
     *
     * @param array $content Content array
     * @return void
     */
    public function set_content(array $content): void
    {
        $this->content = $content;
        $this->css_collection = [];
        $this->id_counter = 0;
    }
}
