<?php
/**
 * TB4 Box Shadow Advanced Field
 *
 * Handles box shadow settings including multiple shadows and hover states
 *
 * @package TB4\AdvancedFields
 * @since 1.0.0
 */

namespace TB4\AdvancedFields;

require_once __DIR__ . '/advancedfield.php';

class BoxShadow extends AdvancedField
{
    /**
     * Get default box shadow values
     *
     * @return array
     */
    public function get_defaults(): array
    {
        return [
            'shadows' => [
                [
                    'horizontal' => '0',
                    'vertical' => '4',
                    'blur' => '6',
                    'spread' => '0',
                    'color' => 'rgba(0,0,0,0.1)',
                    'inset' => false,
                ]
            ],
            'hover_shadows' => []
        ];
    }

    /**
     * Get default single shadow values
     *
     * @return array
     */
    protected function get_shadow_defaults(): array
    {
        return [
            'horizontal' => '0',
            'vertical' => '4',
            'blur' => '6',
            'spread' => '0',
            'color' => 'rgba(0,0,0,0.1)',
            'inset' => false,
        ];
    }

    /**
     * Render box shadow control panel
     *
     * @param string $prefix Input name prefix
     * @param array $values Current values
     * @return string HTML
     */
    public function render_controls(string $prefix, array $values): string
    {
        $values = $this->merge_defaults($values);

        $html = '<div class="tb4-boxshadow-panel tb4-advanced-panel">';
        $html .= '<div class="tb4-panel-header">Box Shadow</div>';
        $html .= '<div class="tb4-panel-content">';

        // Normal state shadows
        $html .= '<div class="tb4-boxshadow-section">';
        $html .= '<h4 class="tb4-section-title">Normal State</h4>';
        $html .= $this->render_shadow_list("{$prefix}[shadows]", $values['shadows'], 'shadows');
        $html .= '</div>';

        // Hover state shadows
        $html .= '<div class="tb4-boxshadow-section tb4-boxshadow-hover">';
        $html .= '<h4 class="tb4-section-title">Hover State</h4>';
        $html .= $this->render_shadow_list("{$prefix}[hover_shadows]", $values['hover_shadows'], 'hover_shadows');
        $html .= '</div>';

        $html .= '</div>'; // panel-content
        $html .= '</div>'; // panel

        return $html;
    }

    /**
     * Render shadow list with add/remove functionality
     *
     * @param string $name Base input name
     * @param array $shadows Array of shadow configurations
     * @param string $list_id Unique identifier for the list
     * @return string HTML
     */
    protected function render_shadow_list(string $name, array $shadows, string $list_id): string
    {
        $html = '<div class="tb4-shadow-list" data-list-id="' . $this->esc_attr($list_id) . '">';

        if (empty($shadows)) {
            $shadows = [];
        }

        foreach ($shadows as $index => $shadow) {
            $shadow = array_merge($this->get_shadow_defaults(), $shadow);
            $html .= $this->render_single_shadow($name, $index, $shadow);
        }

        $html .= '</div>';

        // Add shadow button
        $html .= sprintf(
            '<button type="button" class="tb4-btn tb4-btn-secondary tb4-add-shadow" data-target="%s" data-name="%s">
                <span class="tb4-btn-icon">+</span> Add Shadow
            </button>',
            $this->esc_attr($list_id),
            $this->esc_attr($name)
        );

        // Template for new shadow (hidden)
        $html .= '<template class="tb4-shadow-template" data-list-id="' . $this->esc_attr($list_id) . '">';
        $html .= $this->render_single_shadow($name, '__INDEX__', $this->get_shadow_defaults());
        $html .= '</template>';

        return $html;
    }

    /**
     * Render a single shadow configuration
     *
     * @param string $name Base input name
     * @param int|string $index Shadow index
     * @param array $shadow Shadow values
     * @return string HTML
     */
    protected function render_single_shadow(string $name, int|string $index, array $shadow): string
    {
        $base_name = "{$name}[{$index}]";

        $html = '<div class="tb4-shadow-item" data-index="' . $this->esc_attr((string)$index) . '">';

        // Header with remove button
        $html .= '<div class="tb4-shadow-header">';
        $html .= '<span class="tb4-shadow-label">Shadow ' . ($index === '__INDEX__' ? '' : ((int)$index + 1)) . '</span>';
        $html .= '<button type="button" class="tb4-btn tb4-btn-icon tb4-remove-shadow" title="Remove Shadow">&times;</button>';
        $html .= '</div>';

        $html .= '<div class="tb4-shadow-controls">';

        // Horizontal & Vertical row
        $html .= '<div class="tb4-control-row">';
        $html .= $this->render_number_input(
            "{$base_name}[horizontal]",
            $shadow['horizontal'],
            'X Offset',
            'px'
        );
        $html .= $this->render_number_input(
            "{$base_name}[vertical]",
            $shadow['vertical'],
            'Y Offset',
            'px'
        );
        $html .= '</div>';

        // Blur & Spread row
        $html .= '<div class="tb4-control-row">';
        $html .= $this->render_number_input(
            "{$base_name}[blur]",
            $shadow['blur'],
            'Blur',
            'px'
        );
        $html .= $this->render_number_input(
            "{$base_name}[spread]",
            $shadow['spread'],
            'Spread',
            'px'
        );
        $html .= '</div>';

        // Color & Inset row
        $html .= '<div class="tb4-control-row">';
        $html .= $this->render_color_picker(
            "{$base_name}[color]",
            $shadow['color'],
            'Color'
        );
        $html .= $this->render_checkbox(
            "{$base_name}[inset]",
            (bool)$shadow['inset'],
            'Inset'
        );
        $html .= '</div>';

        $html .= '</div>'; // shadow-controls
        $html .= '</div>'; // shadow-item

        return $html;
    }

    /**
     * Render a number input with unit suffix
     *
     * @param string $name Input name
     * @param string $value Current value
     * @param string $label Label text
     * @param string $unit Unit suffix (e.g., 'px')
     * @return string HTML
     */
    protected function render_number_input(
        string $name,
        string $value,
        string $label,
        string $unit = 'px'
    ): string {
        $id = 'tb4-' . str_replace(['[', ']', ' '], ['-', '', '-'], $name);

        return sprintf(
            '<div class="tb4-field-control tb4-number-control">
                <label for="%s">%s</label>
                <div class="tb4-input-with-unit">
                    <input type="number" id="%s" name="%s" value="%s" class="tb4-input tb4-input-small">
                    <span class="tb4-unit">%s</span>
                </div>
            </div>',
            $this->esc_attr($id),
            $this->esc_attr($label),
            $this->esc_attr($id),
            $this->esc_attr($name),
            $this->esc_attr($value),
            $this->esc_attr($unit)
        );
    }

    /**
     * Render a checkbox control
     *
     * @param string $name Input name
     * @param bool $checked Whether checkbox is checked
     * @param string $label Label text
     * @return string HTML
     */
    protected function render_checkbox(string $name, bool $checked, string $label): string
    {
        $id = 'tb4-' . str_replace(['[', ']', ' '], ['-', '', '-'], $name);
        $checked_attr = $checked ? ' checked' : '';

        return sprintf(
            '<div class="tb4-field-control tb4-checkbox-control">
                <label for="%s" class="tb4-checkbox-label">
                    <input type="hidden" name="%s" value="0">
                    <input type="checkbox" id="%s" name="%s" value="1"%s class="tb4-checkbox">
                    <span class="tb4-checkbox-text">%s</span>
                </label>
            </div>',
            $this->esc_attr($id),
            $this->esc_attr($name),
            $this->esc_attr($id),
            $this->esc_attr($name),
            $checked_attr,
            $this->esc_attr($label)
        );
    }

    /**
     * Generate CSS from box shadow values
     *
     * @param string $selector CSS selector
     * @param array $values Field values
     * @return string CSS
     */
    public function generate_css(string $selector, array $values): string
    {
        $values = $this->merge_defaults($values);
        $css = '';

        // Normal state shadows
        $normal_shadow = $this->build_shadow_value($values['shadows']);
        if (!empty($normal_shadow)) {
            $css .= "{$selector} {\n    box-shadow: {$normal_shadow};\n}\n";
        }

        // Hover state shadows
        $hover_shadow = $this->build_shadow_value($values['hover_shadows']);
        if (!empty($hover_shadow)) {
            $css .= "{$selector}:hover {\n    box-shadow: {$hover_shadow};\n}\n";
        }

        return $css;
    }

    /**
     * Build the box-shadow CSS value from shadow array
     *
     * @param array $shadows Array of shadow configurations
     * @return string CSS box-shadow value
     */
    protected function build_shadow_value(array $shadows): string
    {
        if (empty($shadows)) {
            return '';
        }

        $shadow_parts = [];

        foreach ($shadows as $shadow) {
            $shadow = array_merge($this->get_shadow_defaults(), $shadow);

            $horizontal = $this->sanitize_css_value($shadow['horizontal']);
            $vertical = $this->sanitize_css_value($shadow['vertical']);
            $blur = $this->sanitize_css_value($shadow['blur']);
            $spread = $this->sanitize_css_value($shadow['spread']);
            $color = $this->sanitize_css_value($shadow['color']);
            $inset = !empty($shadow['inset']);

            // Skip if essential values are missing
            if ($horizontal === '' && $vertical === '') {
                continue;
            }

            // Build shadow string
            $parts = [];

            if ($inset) {
                $parts[] = 'inset';
            }

            $parts[] = $this->css_unit($horizontal, 'px');
            $parts[] = $this->css_unit($vertical, 'px');
            $parts[] = $this->css_unit($blur, 'px');
            $parts[] = $this->css_unit($spread, 'px');

            if (!empty($color)) {
                $parts[] = $color;
            }

            $shadow_parts[] = implode(' ', $parts);
        }

        return implode(', ', $shadow_parts);
    }

    /**
     * Get CSS for no shadow (useful for removing shadows)
     *
     * @param string $selector CSS selector
     * @return string CSS
     */
    public function generate_none_css(string $selector): string
    {
        return "{$selector} {\n    box-shadow: none;\n}\n";
    }

    /**
     * Preset: Subtle shadow
     *
     * @return array Shadow configuration
     */
    public static function preset_subtle(): array
    {
        return [
            'shadows' => [
                [
                    'horizontal' => '0',
                    'vertical' => '1',
                    'blur' => '3',
                    'spread' => '0',
                    'color' => 'rgba(0,0,0,0.1)',
                    'inset' => false,
                ]
            ],
            'hover_shadows' => []
        ];
    }

    /**
     * Preset: Medium shadow
     *
     * @return array Shadow configuration
     */
    public static function preset_medium(): array
    {
        return [
            'shadows' => [
                [
                    'horizontal' => '0',
                    'vertical' => '4',
                    'blur' => '6',
                    'spread' => '-1',
                    'color' => 'rgba(0,0,0,0.1)',
                    'inset' => false,
                ],
                [
                    'horizontal' => '0',
                    'vertical' => '2',
                    'blur' => '4',
                    'spread' => '-1',
                    'color' => 'rgba(0,0,0,0.06)',
                    'inset' => false,
                ]
            ],
            'hover_shadows' => []
        ];
    }

    /**
     * Preset: Large shadow
     *
     * @return array Shadow configuration
     */
    public static function preset_large(): array
    {
        return [
            'shadows' => [
                [
                    'horizontal' => '0',
                    'vertical' => '10',
                    'blur' => '15',
                    'spread' => '-3',
                    'color' => 'rgba(0,0,0,0.1)',
                    'inset' => false,
                ],
                [
                    'horizontal' => '0',
                    'vertical' => '4',
                    'blur' => '6',
                    'spread' => '-2',
                    'color' => 'rgba(0,0,0,0.05)',
                    'inset' => false,
                ]
            ],
            'hover_shadows' => []
        ];
    }

    /**
     * Preset: Elevated with hover lift
     *
     * @return array Shadow configuration
     */
    public static function preset_elevated(): array
    {
        return [
            'shadows' => [
                [
                    'horizontal' => '0',
                    'vertical' => '4',
                    'blur' => '6',
                    'spread' => '0',
                    'color' => 'rgba(0,0,0,0.1)',
                    'inset' => false,
                ]
            ],
            'hover_shadows' => [
                [
                    'horizontal' => '0',
                    'vertical' => '10',
                    'blur' => '20',
                    'spread' => '0',
                    'color' => 'rgba(0,0,0,0.15)',
                    'inset' => false,
                ]
            ]
        ];
    }

    /**
     * Preset: Inset shadow
     *
     * @return array Shadow configuration
     */
    public static function preset_inset(): array
    {
        return [
            'shadows' => [
                [
                    'horizontal' => '0',
                    'vertical' => '2',
                    'blur' => '4',
                    'spread' => '0',
                    'color' => 'rgba(0,0,0,0.1)',
                    'inset' => true,
                ]
            ],
            'hover_shadows' => []
        ];
    }
}
