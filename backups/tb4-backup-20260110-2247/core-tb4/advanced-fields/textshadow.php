<?php
/**
 * TB4 Text Shadow Advanced Field
 *
 * Handles text shadow settings including multiple shadows and hover states
 *
 * @package TB4\AdvancedFields
 * @since 1.0.0
 */

namespace TB4\AdvancedFields;

require_once __DIR__ . '/advancedfield.php';

class TextShadow extends AdvancedField
{
    /**
     * Get default text shadow values
     *
     * @return array
     */
    public function get_defaults(): array
    {
        return [
            'shadows' => [
                [
                    'horizontal' => '0',
                    'vertical' => '1',
                    'blur' => '2',
                    'color' => 'rgba(0,0,0,0.3)',
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
            'vertical' => '1',
            'blur' => '2',
            'color' => 'rgba(0,0,0,0.3)',
        ];
    }

    /**
     * Render text shadow control panel
     *
     * @param string $prefix Input name prefix
     * @param array $values Current values
     * @return string HTML
     */
    public function render_controls(string $prefix, array $values): string
    {
        $values = $this->merge_defaults($values);

        $html = '<div class="tb4-textshadow-panel tb4-advanced-panel">';
        $html .= '<div class="tb4-panel-header">Text Shadow</div>';
        $html .= '<div class="tb4-panel-content">';

        // Preview section
        $html .= $this->render_preview($values);

        // Normal state shadows
        $html .= '<div class="tb4-textshadow-section">';
        $html .= '<h4 class="tb4-section-title">Normal State</h4>';
        $html .= $this->render_shadow_list("{$prefix}[shadows]", $values['shadows'], 'text_shadows');
        $html .= '</div>';

        // Hover state shadows
        $html .= '<div class="tb4-textshadow-section tb4-textshadow-hover">';
        $html .= '<h4 class="tb4-section-title">Hover State</h4>';
        $html .= $this->render_shadow_list("{$prefix}[hover_shadows]", $values['hover_shadows'], 'text_hover_shadows');
        $html .= '</div>';

        $html .= '</div>'; // panel-content
        $html .= '</div>'; // panel

        return $html;
    }

    /**
     * Render preview section with sample text
     *
     * @param array $values Current values
     * @return string HTML
     */
    protected function render_preview(array $values): string
    {
        $shadow_value = $this->build_shadow_value($values['shadows']);
        $style = !empty($shadow_value) ? "text-shadow: {$shadow_value};" : '';

        $html = '<div class="tb4-textshadow-preview-section">';
        $html .= '<h4 class="tb4-section-title">Preview</h4>';
        $html .= '<div class="tb4-textshadow-preview-container">';
        $html .= sprintf(
            '<div class="tb4-textshadow-preview" style="font-size: 24px; font-weight: bold; padding: 20px; text-align: center; background: #f5f5f5; border-radius: 4px; %s">',
            $this->esc_attr($style)
        );
        $html .= 'Sample Text';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

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

        // Blur & Color row
        $html .= '<div class="tb4-control-row">';
        $html .= $this->render_number_input(
            "{$base_name}[blur]",
            $shadow['blur'],
            'Blur',
            'px'
        );
        $html .= $this->render_color_picker(
            "{$base_name}[color]",
            $shadow['color'],
            'Color'
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
     * Generate CSS from text shadow values
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
            $css .= "{$selector} {\n    text-shadow: {$normal_shadow};\n}\n";
        }

        // Hover state shadows
        $hover_shadow = $this->build_shadow_value($values['hover_shadows']);
        if (!empty($hover_shadow)) {
            $css .= "{$selector}:hover {\n    text-shadow: {$hover_shadow};\n}\n";
        }

        return $css;
    }

    /**
     * Build the text-shadow CSS value from shadow array
     *
     * @param array $shadows Array of shadow configurations
     * @return string CSS text-shadow value
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
            $color = $this->sanitize_css_value($shadow['color']);

            // Skip if essential values are missing
            if ($horizontal === '' && $vertical === '') {
                continue;
            }

            // Build shadow string: horizontal vertical blur color
            $parts = [];
            $parts[] = $this->css_unit($horizontal, 'px');
            $parts[] = $this->css_unit($vertical, 'px');
            $parts[] = $this->css_unit($blur, 'px');

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
        return "{$selector} {\n    text-shadow: none;\n}\n";
    }

    /**
     * Preset: Subtle drop shadow
     *
     * A soft, barely noticeable shadow for depth
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
                    'blur' => '2',
                    'color' => 'rgba(0,0,0,0.2)',
                ]
            ],
            'hover_shadows' => []
        ];
    }

    /**
     * Preset: Text outline effect
     *
     * Creates an outline around text using multiple shadows
     *
     * @return array Shadow configuration
     */
    public static function preset_outline(): array
    {
        return [
            'shadows' => [
                [
                    'horizontal' => '-1',
                    'vertical' => '-1',
                    'blur' => '0',
                    'color' => '#000000',
                ],
                [
                    'horizontal' => '1',
                    'vertical' => '-1',
                    'blur' => '0',
                    'color' => '#000000',
                ],
                [
                    'horizontal' => '-1',
                    'vertical' => '1',
                    'blur' => '0',
                    'color' => '#000000',
                ],
                [
                    'horizontal' => '1',
                    'vertical' => '1',
                    'blur' => '0',
                    'color' => '#000000',
                ]
            ],
            'hover_shadows' => []
        ];
    }

    /**
     * Preset: Glowing text effect
     *
     * Creates a luminous glow around text
     *
     * @return array Shadow configuration
     */
    public static function preset_glow(): array
    {
        return [
            'shadows' => [
                [
                    'horizontal' => '0',
                    'vertical' => '0',
                    'blur' => '10',
                    'color' => 'rgba(255,255,255,0.8)',
                ],
                [
                    'horizontal' => '0',
                    'vertical' => '0',
                    'blur' => '20',
                    'color' => 'rgba(255,255,255,0.6)',
                ],
                [
                    'horizontal' => '0',
                    'vertical' => '0',
                    'blur' => '40',
                    'color' => 'rgba(255,255,255,0.4)',
                ]
            ],
            'hover_shadows' => [
                [
                    'horizontal' => '0',
                    'vertical' => '0',
                    'blur' => '15',
                    'color' => 'rgba(255,255,255,0.9)',
                ],
                [
                    'horizontal' => '0',
                    'vertical' => '0',
                    'blur' => '30',
                    'color' => 'rgba(255,255,255,0.7)',
                ],
                [
                    'horizontal' => '0',
                    'vertical' => '0',
                    'blur' => '60',
                    'color' => 'rgba(255,255,255,0.5)',
                ]
            ]
        ];
    }

    /**
     * Preset: Letterpress / embossed effect
     *
     * Creates an inset/debossed appearance for text
     *
     * @return array Shadow configuration
     */
    public static function preset_letterpress(): array
    {
        return [
            'shadows' => [
                [
                    'horizontal' => '0',
                    'vertical' => '-1',
                    'blur' => '0',
                    'color' => 'rgba(0,0,0,0.4)',
                ],
                [
                    'horizontal' => '0',
                    'vertical' => '1',
                    'blur' => '0',
                    'color' => 'rgba(255,255,255,0.3)',
                ]
            ],
            'hover_shadows' => []
        ];
    }

    /**
     * Preset: Neon glow effect
     *
     * Creates a vibrant neon sign appearance
     *
     * @return array Shadow configuration
     */
    public static function preset_neon(): array
    {
        return [
            'shadows' => [
                [
                    'horizontal' => '0',
                    'vertical' => '0',
                    'blur' => '5',
                    'color' => '#ff00ff',
                ],
                [
                    'horizontal' => '0',
                    'vertical' => '0',
                    'blur' => '10',
                    'color' => '#ff00ff',
                ],
                [
                    'horizontal' => '0',
                    'vertical' => '0',
                    'blur' => '20',
                    'color' => '#ff00ff',
                ],
                [
                    'horizontal' => '0',
                    'vertical' => '0',
                    'blur' => '40',
                    'color' => '#ff00ff',
                ]
            ],
            'hover_shadows' => []
        ];
    }

    /**
     * Preset: Long shadow effect
     *
     * Creates a flat design long shadow
     *
     * @return array Shadow configuration
     */
    public static function preset_long_shadow(): array
    {
        return [
            'shadows' => [
                [
                    'horizontal' => '1',
                    'vertical' => '1',
                    'blur' => '0',
                    'color' => 'rgba(0,0,0,0.1)',
                ],
                [
                    'horizontal' => '2',
                    'vertical' => '2',
                    'blur' => '0',
                    'color' => 'rgba(0,0,0,0.1)',
                ],
                [
                    'horizontal' => '3',
                    'vertical' => '3',
                    'blur' => '0',
                    'color' => 'rgba(0,0,0,0.1)',
                ],
                [
                    'horizontal' => '4',
                    'vertical' => '4',
                    'blur' => '0',
                    'color' => 'rgba(0,0,0,0.1)',
                ],
                [
                    'horizontal' => '5',
                    'vertical' => '5',
                    'blur' => '0',
                    'color' => 'rgba(0,0,0,0.1)',
                ]
            ],
            'hover_shadows' => []
        ];
    }

    /**
     * Preset: 3D text effect
     *
     * Creates a 3D extruded text appearance
     *
     * @return array Shadow configuration
     */
    public static function preset_3d(): array
    {
        return [
            'shadows' => [
                [
                    'horizontal' => '1',
                    'vertical' => '1',
                    'blur' => '0',
                    'color' => '#bbb',
                ],
                [
                    'horizontal' => '2',
                    'vertical' => '2',
                    'blur' => '0',
                    'color' => '#aaa',
                ],
                [
                    'horizontal' => '3',
                    'vertical' => '3',
                    'blur' => '0',
                    'color' => '#999',
                ],
                [
                    'horizontal' => '4',
                    'vertical' => '4',
                    'blur' => '0',
                    'color' => '#888',
                ]
            ],
            'hover_shadows' => []
        ];
    }

    /**
     * Get all available presets
     *
     * @return array Preset names and descriptions
     */
    public static function get_presets(): array
    {
        return [
            'subtle' => [
                'name' => 'Subtle',
                'description' => 'Soft drop shadow for depth',
                'method' => 'preset_subtle'
            ],
            'outline' => [
                'name' => 'Outline',
                'description' => 'Text outline effect',
                'method' => 'preset_outline'
            ],
            'glow' => [
                'name' => 'Glow',
                'description' => 'Luminous glow effect',
                'method' => 'preset_glow'
            ],
            'letterpress' => [
                'name' => 'Letterpress',
                'description' => 'Embossed/debossed effect',
                'method' => 'preset_letterpress'
            ],
            'neon' => [
                'name' => 'Neon',
                'description' => 'Vibrant neon sign effect',
                'method' => 'preset_neon'
            ],
            'long_shadow' => [
                'name' => 'Long Shadow',
                'description' => 'Flat design long shadow',
                'method' => 'preset_long_shadow'
            ],
            '3d' => [
                'name' => '3D',
                'description' => 'Extruded 3D text effect',
                'method' => 'preset_3d'
            ]
        ];
    }

    /**
     * Apply a preset by name
     *
     * @param string $preset_name Preset identifier
     * @return array Shadow configuration or defaults if not found
     */
    public static function apply_preset(string $preset_name): array
    {
        $presets = self::get_presets();

        if (!isset($presets[$preset_name])) {
            return (new self())->get_defaults();
        }

        $method = $presets[$preset_name]['method'];

        if (method_exists(self::class, $method)) {
            return self::$method();
        }

        return (new self())->get_defaults();
    }
}
