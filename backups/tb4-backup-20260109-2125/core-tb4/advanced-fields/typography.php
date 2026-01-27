<?php
/**
 * TB4 Typography Advanced Field
 *
 * Handles typography settings including fonts, sizes, weights, colors, and text styling
 *
 * @package TB4\AdvancedFields
 * @since 1.0.0
 */

namespace TB4\AdvancedFields;

require_once __DIR__ . '/advancedfield.php';

class Typography extends AdvancedField
{
    /**
     * Available font families
     * @var array
     */
    protected array $font_families = [
        ''              => 'Default',
        'inherit'       => 'Inherit',
        'Arial, sans-serif' => 'Arial',
        'Helvetica, sans-serif' => 'Helvetica',
        'Georgia, serif' => 'Georgia',
        'Times New Roman, serif' => 'Times New Roman',
        'Courier New, monospace' => 'Courier New',
        'Verdana, sans-serif' => 'Verdana',
        'Tahoma, sans-serif' => 'Tahoma',
        'system-ui, sans-serif' => 'System UI',
    ];

    /**
     * Available font weights
     * @var array
     */
    protected array $font_weights = [
        ''    => 'Default',
        '100' => 'Thin (100)',
        '200' => 'Extra Light (200)',
        '300' => 'Light (300)',
        '400' => 'Normal (400)',
        '500' => 'Medium (500)',
        '600' => 'Semi Bold (600)',
        '700' => 'Bold (700)',
        '800' => 'Extra Bold (800)',
        '900' => 'Black (900)',
    ];

    /**
     * Available text alignments
     * @var array
     */
    protected array $text_alignments = [
        ''        => 'Default',
        'left'    => 'Left',
        'center'  => 'Center',
        'right'   => 'Right',
        'justify' => 'Justify',
    ];

    /**
     * Available text transforms
     * @var array
     */
    protected array $text_transforms = [
        ''           => 'Default',
        'none'       => 'None',
        'uppercase'  => 'UPPERCASE',
        'lowercase'  => 'lowercase',
        'capitalize' => 'Capitalize',
    ];

    /**
     * Available text decorations
     * @var array
     */
    protected array $text_decorations = [
        ''             => 'Default',
        'none'         => 'None',
        'underline'    => 'Underline',
        'overline'     => 'Overline',
        'line-through' => 'Line Through',
    ];

    /**
     * Available font styles
     * @var array
     */
    protected array $font_styles = [
        ''       => 'Default',
        'normal' => 'Normal',
        'italic' => 'Italic',
        'oblique'=> 'Oblique',
    ];

    /**
     * Get default typography values
     *
     * @return array
     */
    public function get_defaults(): array
    {
        return [
            'font_family'     => '',
            'font_size'       => [
                'desktop' => '',
                'tablet'  => '',
                'mobile'  => '',
            ],
            'font_weight'     => '',
            'font_style'      => '',
            'line_height'     => [
                'desktop' => '',
                'tablet'  => '',
                'mobile'  => '',
            ],
            'letter_spacing'  => [
                'desktop' => '',
                'tablet'  => '',
                'mobile'  => '',
            ],
            'text_align'      => [
                'desktop' => '',
                'tablet'  => '',
                'mobile'  => '',
            ],
            'text_transform'  => '',
            'text_decoration' => '',
            'color'           => '',
            'color_hover'     => '',
        ];
    }

    /**
     * Render typography control panel
     *
     * @param string $prefix Input name prefix
     * @param array $values Current values
     * @return string HTML
     */
    public function render_controls(string $prefix, array $values): string
    {
        $values = $this->merge_defaults($values);

        $html = '<div class="tb4-typography-panel tb4-advanced-panel">';
        $html .= '<div class="tb4-panel-header">Typography</div>';
        $html .= '<div class="tb4-panel-content">';

        // Font Family
        $html .= $this->render_select(
            "{$prefix}[font_family]",
            $values['font_family'],
            'Font Family',
            $this->font_families
        );

        // Font Size (responsive)
        $html .= $this->render_responsive_input(
            "{$prefix}[font_size]",
            $values['font_size'],
            'Font Size',
            '16px'
        );

        // Font Weight & Style Row
        $html .= '<div class="tb4-control-row">';
        $html .= $this->render_select(
            "{$prefix}[font_weight]",
            $values['font_weight'],
            'Weight',
            $this->font_weights
        );
        $html .= $this->render_select(
            "{$prefix}[font_style]",
            $values['font_style'],
            'Style',
            $this->font_styles
        );
        $html .= '</div>';

        // Line Height (responsive)
        $html .= $this->render_responsive_input(
            "{$prefix}[line_height]",
            $values['line_height'],
            'Line Height',
            '1.5'
        );

        // Letter Spacing (responsive)
        $html .= $this->render_responsive_input(
            "{$prefix}[letter_spacing]",
            $values['letter_spacing'],
            'Letter Spacing',
            '0px'
        );

        // Text Align (responsive)
        $html .= $this->render_responsive_select(
            "{$prefix}[text_align]",
            $values['text_align'],
            'Text Align',
            $this->text_alignments
        );

        // Text Transform & Decoration Row
        $html .= '<div class="tb4-control-row">';
        $html .= $this->render_select(
            "{$prefix}[text_transform]",
            $values['text_transform'],
            'Transform',
            $this->text_transforms
        );
        $html .= $this->render_select(
            "{$prefix}[text_decoration]",
            $values['text_decoration'],
            'Decoration',
            $this->text_decorations
        );
        $html .= '</div>';

        // Colors Row
        $html .= '<div class="tb4-control-row">';
        $html .= $this->render_color_picker(
            "{$prefix}[color]",
            $values['color'],
            'Color'
        );
        $html .= $this->render_color_picker(
            "{$prefix}[color_hover]",
            $values['color_hover'],
            'Hover Color'
        );
        $html .= '</div>';

        $html .= '</div>'; // panel-content
        $html .= '</div>'; // panel

        return $html;
    }

    /**
     * Generate CSS from typography values
     *
     * @param string $selector CSS selector
     * @param array $values Field values
     * @return string CSS
     */
    public function generate_css(string $selector, array $values): string
    {
        $values = $this->merge_defaults($values);
        $css = '';
        $desktop_rules = [];

        // Font Family
        if (!empty($values['font_family'])) {
            $desktop_rules[] = 'font-family: ' . $this->sanitize_css_value($values['font_family']);
        }

        // Font Weight
        if (!empty($values['font_weight'])) {
            $desktop_rules[] = 'font-weight: ' . $this->sanitize_css_value($values['font_weight']);
        }

        // Font Style
        if (!empty($values['font_style'])) {
            $desktop_rules[] = 'font-style: ' . $this->sanitize_css_value($values['font_style']);
        }

        // Text Transform
        if (!empty($values['text_transform'])) {
            $desktop_rules[] = 'text-transform: ' . $this->sanitize_css_value($values['text_transform']);
        }

        // Text Decoration
        if (!empty($values['text_decoration'])) {
            $desktop_rules[] = 'text-decoration: ' . $this->sanitize_css_value($values['text_decoration']);
        }

        // Color
        if (!empty($values['color'])) {
            $desktop_rules[] = 'color: ' . $this->sanitize_css_value($values['color']);
        }

        // Build desktop CSS block
        if (!empty($desktop_rules)) {
            $css .= "{$selector} {\n    " . implode(";\n    ", $desktop_rules) . ";\n}\n";
        }

        // Hover Color
        if (!empty($values['color_hover'])) {
            $css .= "{$selector}:hover {\n    color: " . $this->sanitize_css_value($values['color_hover']) . ";\n}\n";
        }

        // Responsive: Font Size
        $css .= $this->generate_responsive_css($selector, 'font-size', $values['font_size'], 'px');

        // Responsive: Line Height
        $css .= $this->generate_responsive_css($selector, 'line-height', $values['line_height'], '');

        // Responsive: Letter Spacing
        $css .= $this->generate_responsive_css($selector, 'letter-spacing', $values['letter_spacing'], 'px');

        // Responsive: Text Align
        $css .= $this->generate_responsive_css($selector, 'text-align', $values['text_align'], '');

        return $css;
    }

    /**
     * Render responsive select control
     *
     * @param string $name Base input name
     * @param array $values Current values keyed by breakpoint
     * @param string $label Label text
     * @param array $options Select options
     * @return string HTML
     */
    protected function render_responsive_select(
        string $name,
        array $values,
        string $label,
        array $options
    ): string {
        $html = '<div class="tb4-field-control tb4-responsive-control">';
        $html .= sprintf('<label>%s</label>', $this->esc_attr($label));
        $html .= '<div class="tb4-responsive-inputs">';

        $icons = [
            'desktop' => '🖥️',
            'tablet'  => '📱',
            'mobile'  => '📲'
        ];

        foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
            $value = $values[$breakpoint] ?? '';

            $options_html = '';
            foreach ($options as $opt_value => $opt_label) {
                $selected = ($value === (string)$opt_value) ? ' selected' : '';
                $options_html .= sprintf(
                    '<option value="%s"%s>%s</option>',
                    $this->esc_attr((string)$opt_value),
                    $selected,
                    $this->esc_attr($opt_label)
                );
            }

            $html .= sprintf(
                '<div class="tb4-responsive-input" data-breakpoint="%s">
                    <span class="tb4-breakpoint-icon" title="%s">%s</span>
                    <select name="%s[%s]" class="tb4-select tb4-select-small">%s</select>
                </div>',
                $breakpoint,
                ucfirst($breakpoint),
                $icons[$breakpoint],
                $this->esc_attr($name),
                $breakpoint,
                $options_html
            );
        }

        $html .= '</div></div>';
        return $html;
    }

    /**
     * Add custom font family
     *
     * @param string $value CSS font-family value
     * @param string $label Display label
     * @return self
     */
    public function add_font_family(string $value, string $label): self
    {
        $this->font_families[$value] = $label;
        return $this;
    }

    /**
     * Set Google Font families
     *
     * @param array $fonts Array of font names
     * @return self
     */
    public function set_google_fonts(array $fonts): self
    {
        foreach ($fonts as $font) {
            $this->font_families["'{$font}', sans-serif"] = $font;
        }
        return $this;
    }
}
