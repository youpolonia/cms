<?php
/**
 * TB4 Border Advanced Field
 *
 * Handles border settings including width, style, color, and radius with responsive support
 *
 * @package TB4\AdvancedFields
 * @since 1.0.0
 */

namespace TB4\AdvancedFields;

require_once __DIR__ . '/advancedfield.php';

class Border extends AdvancedField
{
    /**
     * Available border styles
     * @var array
     */
    protected array $border_styles = [
        ''        => 'Default',
        'none'    => 'None',
        'solid'   => 'Solid',
        'dashed'  => 'Dashed',
        'dotted'  => 'Dotted',
        'double'  => 'Double',
        'groove'  => 'Groove',
        'ridge'   => 'Ridge',
        'inset'   => 'Inset',
        'outset'  => 'Outset',
    ];

    /**
     * Get default border values
     *
     * @return array
     */
    public function get_defaults(): array
    {
        return [
            'width' => [
                'desktop' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => '', 'linked' => true],
                'tablet'  => ['top' => '', 'right' => '', 'bottom' => '', 'left' => '', 'linked' => true],
                'mobile'  => ['top' => '', 'right' => '', 'bottom' => '', 'left' => '', 'linked' => true],
            ],
            'style' => [
                'top' => '', 'right' => '', 'bottom' => '', 'left' => '', 'linked' => true
            ],
            'color' => [
                'top' => '', 'right' => '', 'bottom' => '', 'left' => '', 'linked' => true
            ],
            'radius' => [
                'desktop' => ['top_left' => '', 'top_right' => '', 'bottom_right' => '', 'bottom_left' => '', 'linked' => true],
                'tablet'  => ['top_left' => '', 'top_right' => '', 'bottom_right' => '', 'bottom_left' => '', 'linked' => true],
                'mobile'  => ['top_left' => '', 'top_right' => '', 'bottom_right' => '', 'bottom_left' => '', 'linked' => true],
            ],
        ];
    }

    /**
     * Render border control panel
     *
     * @param string $prefix Input name prefix
     * @param array $values Current values
     * @return string HTML
     */
    public function render_controls(string $prefix, array $values): string
    {
        $values = $this->merge_defaults($values);

        $html = '<div class="tb4-border-panel tb4-advanced-panel">';
        $html .= '<div class="tb4-panel-header">Border</div>';
        $html .= '<div class="tb4-panel-content">';

        // Border Width (responsive with linked sides)
        $html .= '<div class="tb4-section-header">Border Width</div>';
        $html .= $this->render_responsive_linked_inputs(
            "{$prefix}[width]",
            $values['width'],
            'Width',
            ['top', 'right', 'bottom', 'left'],
            '0px'
        );

        // Border Style (linked sides)
        $html .= '<div class="tb4-section-header">Border Style</div>';
        $html .= $this->render_linked_selects(
            "{$prefix}[style]",
            $values['style'],
            'Style',
            ['top', 'right', 'bottom', 'left'],
            $this->border_styles
        );

        // Border Color (linked sides)
        $html .= '<div class="tb4-section-header">Border Color</div>';
        $html .= $this->render_linked_colors(
            "{$prefix}[color]",
            $values['color'],
            'Color',
            ['top', 'right', 'bottom', 'left']
        );

        // Border Radius (responsive with linked corners)
        $html .= '<div class="tb4-section-header">Border Radius</div>';
        $html .= $this->render_responsive_linked_inputs(
            "{$prefix}[radius]",
            $values['radius'],
            'Radius',
            ['top_left', 'top_right', 'bottom_right', 'bottom_left'],
            '0px'
        );

        $html .= '</div>'; // panel-content
        $html .= '</div>'; // panel

        return $html;
    }

    /**
     * Generate CSS from border values
     *
     * @param string $selector CSS selector
     * @param array $values Field values
     * @return string CSS
     */
    public function generate_css(string $selector, array $values): string
    {
        $values = $this->merge_defaults($values);
        $css = '';

        // Border Style (non-responsive)
        $style_rules = $this->generate_side_css('border', 'style', $values['style'], '');
        if (!empty($style_rules)) {
            $css .= "{$selector} {\n    " . implode(";\n    ", $style_rules) . ";\n}\n";
        }

        // Border Color (non-responsive)
        $color_rules = $this->generate_side_css('border', 'color', $values['color'], '');
        if (!empty($color_rules)) {
            $css .= "{$selector} {\n    " . implode(";\n    ", $color_rules) . ";\n}\n";
        }

        // Border Width (responsive)
        $css .= $this->generate_responsive_side_css(
            $selector,
            'border',
            'width',
            $values['width'],
            ['top', 'right', 'bottom', 'left'],
            'px'
        );

        // Border Radius (responsive)
        $css .= $this->generate_responsive_radius_css($selector, $values['radius']);

        return $css;
    }

    /**
     * Render responsive linked input controls for four sides
     *
     * @param string $name Base input name
     * @param array $values Current values keyed by breakpoint
     * @param string $label Label text
     * @param array $sides Array of side keys
     * @param string $placeholder Placeholder text
     * @return string HTML
     */
    protected function render_responsive_linked_inputs(
        string $name,
        array $values,
        string $label,
        array $sides,
        string $placeholder = ''
    ): string {
        $html = '<div class="tb4-field-control tb4-responsive-linked-control">';
        $html .= sprintf('<label>%s</label>', $this->esc_attr($label));

        $icons = [
            'desktop' => '🖥️',
            'tablet'  => '📱',
            'mobile'  => '📲'
        ];

        $side_labels = [
            'top'          => 'T',
            'right'        => 'R',
            'bottom'       => 'B',
            'left'         => 'L',
            'top_left'     => 'TL',
            'top_right'    => 'TR',
            'bottom_right' => 'BR',
            'bottom_left'  => 'BL',
        ];

        foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
            $bp_values = $values[$breakpoint] ?? [];
            $linked = $bp_values['linked'] ?? true;
            $linked_checked = $linked ? ' checked' : '';

            $html .= sprintf(
                '<div class="tb4-responsive-group" data-breakpoint="%s">
                    <span class="tb4-breakpoint-icon" title="%s">%s</span>
                    <div class="tb4-linked-inputs">',
                $breakpoint,
                ucfirst($breakpoint),
                $icons[$breakpoint]
            );

            foreach ($sides as $side) {
                $value = $bp_values[$side] ?? '';
                $html .= sprintf(
                    '<div class="tb4-side-input">
                        <span class="tb4-side-label">%s</span>
                        <input type="text" name="%s[%s][%s]" value="%s" placeholder="%s" class="tb4-input tb4-input-tiny tb4-linked-input">
                    </div>',
                    $side_labels[$side],
                    $this->esc_attr($name),
                    $breakpoint,
                    $side,
                    $this->esc_attr($value),
                    $this->esc_attr($placeholder)
                );
            }

            $html .= sprintf(
                '<label class="tb4-link-toggle" title="Link values">
                        <input type="checkbox" name="%s[%s][linked]" value="1"%s class="tb4-link-checkbox">
                        <span class="tb4-link-icon">🔗</span>
                    </label>
                    </div>
                </div>',
                $this->esc_attr($name),
                $breakpoint,
                $linked_checked
            );
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Render linked select controls for four sides
     *
     * @param string $name Base input name
     * @param array $values Current values
     * @param string $label Label text
     * @param array $sides Array of side keys
     * @param array $options Select options
     * @return string HTML
     */
    protected function render_linked_selects(
        string $name,
        array $values,
        string $label,
        array $sides,
        array $options
    ): string {
        $html = '<div class="tb4-field-control tb4-linked-selects-control">';
        $html .= sprintf('<label>%s</label>', $this->esc_attr($label));
        $html .= '<div class="tb4-linked-inputs">';

        $side_labels = [
            'top'    => 'Top',
            'right'  => 'Right',
            'bottom' => 'Bottom',
            'left'   => 'Left',
        ];

        foreach ($sides as $side) {
            $value = $values[$side] ?? '';

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
                '<div class="tb4-side-select">
                    <span class="tb4-side-label">%s</span>
                    <select name="%s[%s]" class="tb4-select tb4-select-small tb4-linked-input">%s</select>
                </div>',
                $side_labels[$side],
                $this->esc_attr($name),
                $side,
                $options_html
            );
        }

        $linked = $values['linked'] ?? true;
        $linked_checked = $linked ? ' checked' : '';

        $html .= sprintf(
            '<label class="tb4-link-toggle" title="Link values">
                <input type="checkbox" name="%s[linked]" value="1"%s class="tb4-link-checkbox">
                <span class="tb4-link-icon">🔗</span>
            </label>',
            $this->esc_attr($name),
            $linked_checked
        );

        $html .= '</div></div>';
        return $html;
    }

    /**
     * Render linked color picker controls for four sides
     *
     * @param string $name Base input name
     * @param array $values Current values
     * @param string $label Label text
     * @param array $sides Array of side keys
     * @return string HTML
     */
    protected function render_linked_colors(
        string $name,
        array $values,
        string $label,
        array $sides
    ): string {
        $html = '<div class="tb4-field-control tb4-linked-colors-control">';
        $html .= sprintf('<label>%s</label>', $this->esc_attr($label));
        $html .= '<div class="tb4-linked-inputs">';

        $side_labels = [
            'top'    => 'Top',
            'right'  => 'Right',
            'bottom' => 'Bottom',
            'left'   => 'Left',
        ];

        foreach ($sides as $side) {
            $value = $values[$side] ?? '';
            $id = 'tb4-' . str_replace(['[', ']', ' '], ['-', '', '-'], "{$name}_{$side}");

            $html .= sprintf(
                '<div class="tb4-side-color">
                    <span class="tb4-side-label">%s</span>
                    <div class="tb4-color-picker-wrapper tb4-color-wrapper-small">
                        <input type="color" id="%s-picker" value="%s" class="tb4-color-picker tb4-color-small" data-target="%s">
                        <input type="text" id="%s" name="%s[%s]" value="%s" class="tb4-color-input tb4-input-small tb4-linked-input" placeholder="#000">
                    </div>
                </div>',
                $side_labels[$side],
                $this->esc_attr($id),
                $this->esc_attr($value ?: '#000000'),
                $this->esc_attr($id),
                $this->esc_attr($id),
                $this->esc_attr($name),
                $side,
                $this->esc_attr($value)
            );
        }

        $linked = $values['linked'] ?? true;
        $linked_checked = $linked ? ' checked' : '';

        $html .= sprintf(
            '<label class="tb4-link-toggle" title="Link values">
                <input type="checkbox" name="%s[linked]" value="1"%s class="tb4-link-checkbox">
                <span class="tb4-link-icon">🔗</span>
            </label>',
            $this->esc_attr($name),
            $linked_checked
        );

        $html .= '</div></div>';
        return $html;
    }

    /**
     * Generate CSS rules for sided properties (top, right, bottom, left)
     *
     * @param string $property_base Base property name (e.g., 'border')
     * @param string $property_suffix Property suffix (e.g., 'style', 'color', 'width')
     * @param array $values Values for each side
     * @param string $unit Default unit
     * @return array CSS rules
     */
    protected function generate_side_css(
        string $property_base,
        string $property_suffix,
        array $values,
        string $unit
    ): array {
        $rules = [];
        $sides = ['top', 'right', 'bottom', 'left'];

        foreach ($sides as $side) {
            if (!isset($values[$side]) || $values[$side] === '') {
                continue;
            }

            $value = $unit ? $this->css_unit($values[$side], $unit) : $this->sanitize_css_value($values[$side]);
            if ($value !== '') {
                $rules[] = "{$property_base}-{$side}-{$property_suffix}: {$value}";
            }
        }

        return $rules;
    }

    /**
     * Generate responsive CSS for sided properties
     *
     * @param string $selector CSS selector
     * @param string $property_base Base property name
     * @param string $property_suffix Property suffix
     * @param array $responsive_values Values keyed by breakpoint
     * @param array $sides Array of side keys
     * @param string $unit Default unit
     * @return string Generated CSS
     */
    protected function generate_responsive_side_css(
        string $selector,
        string $property_base,
        string $property_suffix,
        array $responsive_values,
        array $sides,
        string $unit = 'px'
    ): string {
        $output = '';

        foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
            if (!isset($responsive_values[$breakpoint])) {
                continue;
            }

            $bp_values = $responsive_values[$breakpoint];
            $rules = [];

            foreach ($sides as $side) {
                if (!isset($bp_values[$side]) || $bp_values[$side] === '') {
                    continue;
                }

                $value = $this->css_unit($bp_values[$side], $unit);
                if ($value !== '') {
                    $rules[] = "{$property_base}-{$side}-{$property_suffix}: {$value}";
                }
            }

            if (!empty($rules)) {
                $css = "{$selector} {\n    " . implode(";\n    ", $rules) . ";\n}\n";
                $output .= $this->wrap_media_query($breakpoint, $css);
            }
        }

        return $output;
    }

    /**
     * Generate responsive CSS for border radius
     *
     * @param string $selector CSS selector
     * @param array $responsive_values Values keyed by breakpoint
     * @return string Generated CSS
     */
    protected function generate_responsive_radius_css(string $selector, array $responsive_values): string
    {
        $output = '';

        $corner_map = [
            'top_left'     => 'border-top-left-radius',
            'top_right'    => 'border-top-right-radius',
            'bottom_right' => 'border-bottom-right-radius',
            'bottom_left'  => 'border-bottom-left-radius',
        ];

        foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
            if (!isset($responsive_values[$breakpoint])) {
                continue;
            }

            $bp_values = $responsive_values[$breakpoint];
            $rules = [];

            foreach ($corner_map as $corner => $property) {
                if (!isset($bp_values[$corner]) || $bp_values[$corner] === '') {
                    continue;
                }

                $value = $this->css_unit($bp_values[$corner], 'px');
                if ($value !== '') {
                    $rules[] = "{$property}: {$value}";
                }
            }

            if (!empty($rules)) {
                $css = "{$selector} {\n    " . implode(";\n    ", $rules) . ";\n}\n";
                $output .= $this->wrap_media_query($breakpoint, $css);
            }
        }

        return $output;
    }
}
