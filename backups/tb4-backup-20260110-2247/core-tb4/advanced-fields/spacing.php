<?php
/**
 * TB4 Spacing Advanced Field
 *
 * Handles margin and padding settings with responsive breakpoints and visual box model UI
 *
 * @package TB4\AdvancedFields
 * @since 1.0.0
 */

namespace TB4\AdvancedFields;

require_once __DIR__ . '/advancedfield.php';

class Spacing extends AdvancedField
{
    /**
     * Available spacing units
     * @var array
     */
    protected array $units = [
        'px'  => 'px',
        'em'  => 'em',
        'rem' => 'rem',
        '%'   => '%',
        'vh'  => 'vh',
        'vw'  => 'vw',
    ];

    /**
     * Get default spacing values
     *
     * @return array
     */
    public function get_defaults(): array
    {
        return [
            'margin' => [
                'desktop' => [
                    'top'    => '',
                    'right'  => '',
                    'bottom' => '',
                    'left'   => '',
                    'unit'   => 'px',
                    'linked' => false,
                ],
                'tablet' => [
                    'top'    => '',
                    'right'  => '',
                    'bottom' => '',
                    'left'   => '',
                    'unit'   => 'px',
                    'linked' => false,
                ],
                'mobile' => [
                    'top'    => '',
                    'right'  => '',
                    'bottom' => '',
                    'left'   => '',
                    'unit'   => 'px',
                    'linked' => false,
                ],
            ],
            'padding' => [
                'desktop' => [
                    'top'    => '',
                    'right'  => '',
                    'bottom' => '',
                    'left'   => '',
                    'unit'   => 'px',
                    'linked' => false,
                ],
                'tablet' => [
                    'top'    => '',
                    'right'  => '',
                    'bottom' => '',
                    'left'   => '',
                    'unit'   => 'px',
                    'linked' => false,
                ],
                'mobile' => [
                    'top'    => '',
                    'right'  => '',
                    'bottom' => '',
                    'left'   => '',
                    'unit'   => 'px',
                    'linked' => false,
                ],
            ],
        ];
    }

    /**
     * Render spacing control panel with visual box model
     *
     * @param string $prefix Input name prefix
     * @param array $values Current values
     * @return string HTML
     */
    public function render_controls(string $prefix, array $values): string
    {
        $values = $this->merge_defaults($values);

        $html = '<div class="tb4-spacing-panel tb4-advanced-panel">';
        $html .= '<div class="tb4-panel-header">Spacing</div>';
        $html .= '<div class="tb4-panel-content">';

        // Responsive Tabs
        $html .= $this->render_responsive_tabs($prefix);

        // Responsive Panels
        foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
            $active = $breakpoint === 'desktop' ? ' tb4-active' : '';
            $html .= sprintf(
                '<div class="tb4-spacing-breakpoint%s" data-breakpoint="%s">',
                $active,
                $breakpoint
            );

            // Visual Box Model
            $html .= $this->render_box_model(
                $prefix,
                $breakpoint,
                $values['margin'][$breakpoint],
                $values['padding'][$breakpoint]
            );

            $html .= '</div>';
        }

        $html .= '</div>'; // panel-content
        $html .= '</div>'; // panel

        return $html;
    }

    /**
     * Render responsive tabs
     *
     * @param string $prefix Input prefix
     * @return string HTML
     */
    protected function render_responsive_tabs(string $prefix): string
    {
        $icons = [
            'desktop' => '🖥️',
            'tablet'  => '📱',
            'mobile'  => '📲',
        ];

        $html = '<div class="tb4-spacing-tabs">';
        foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
            $active = $breakpoint === 'desktop' ? ' tb4-active' : '';
            $html .= sprintf(
                '<button type="button" class="tb4-spacing-tab%s" data-breakpoint="%s" title="%s">%s</button>',
                $active,
                $breakpoint,
                ucfirst($breakpoint),
                $icons[$breakpoint]
            );
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Render visual box model UI
     *
     * @param string $prefix Input prefix
     * @param string $breakpoint Current breakpoint
     * @param array $margin Margin values
     * @param array $padding Padding values
     * @return string HTML
     */
    protected function render_box_model(
        string $prefix,
        string $breakpoint,
        array $margin,
        array $padding
    ): string {
        $html = '<div class="tb4-box-model">';

        // Margin Label
        $html .= '<div class="tb4-box-label tb4-margin-label">MARGIN</div>';

        // Margin Box (outer)
        $html .= '<div class="tb4-box-margin">';

        // Margin Top
        $html .= $this->render_spacing_input(
            "{$prefix}[margin][{$breakpoint}][top]",
            $margin['top'],
            'top',
            'margin'
        );

        // Margin Right
        $html .= $this->render_spacing_input(
            "{$prefix}[margin][{$breakpoint}][right]",
            $margin['right'],
            'right',
            'margin'
        );

        // Margin Bottom
        $html .= $this->render_spacing_input(
            "{$prefix}[margin][{$breakpoint}][bottom]",
            $margin['bottom'],
            'bottom',
            'margin'
        );

        // Margin Left
        $html .= $this->render_spacing_input(
            "{$prefix}[margin][{$breakpoint}][left]",
            $margin['left'],
            'left',
            'margin'
        );

        // Margin Link Toggle
        $html .= $this->render_link_toggle(
            "{$prefix}[margin][{$breakpoint}][linked]",
            $margin['linked'],
            'margin'
        );

        // Margin Unit Selector
        $html .= $this->render_unit_selector(
            "{$prefix}[margin][{$breakpoint}][unit]",
            $margin['unit'],
            'margin'
        );

        // Padding Label
        $html .= '<div class="tb4-box-label tb4-padding-label">PADDING</div>';

        // Padding Box (inner)
        $html .= '<div class="tb4-box-padding">';

        // Padding Top
        $html .= $this->render_spacing_input(
            "{$prefix}[padding][{$breakpoint}][top]",
            $padding['top'],
            'top',
            'padding'
        );

        // Padding Right
        $html .= $this->render_spacing_input(
            "{$prefix}[padding][{$breakpoint}][right]",
            $padding['right'],
            'right',
            'padding'
        );

        // Padding Bottom
        $html .= $this->render_spacing_input(
            "{$prefix}[padding][{$breakpoint}][bottom]",
            $padding['bottom'],
            'bottom',
            'padding'
        );

        // Padding Left
        $html .= $this->render_spacing_input(
            "{$prefix}[padding][{$breakpoint}][left]",
            $padding['left'],
            'left',
            'padding'
        );

        // Padding Link Toggle
        $html .= $this->render_link_toggle(
            "{$prefix}[padding][{$breakpoint}][linked]",
            $padding['linked'],
            'padding'
        );

        // Padding Unit Selector
        $html .= $this->render_unit_selector(
            "{$prefix}[padding][{$breakpoint}][unit]",
            $padding['unit'],
            'padding'
        );

        // Content Box (center)
        $html .= '<div class="tb4-box-content">CONTENT</div>';

        $html .= '</div>'; // tb4-box-padding
        $html .= '</div>'; // tb4-box-margin
        $html .= '</div>'; // tb4-box-model

        return $html;
    }

    /**
     * Render a spacing input field
     *
     * @param string $name Input name
     * @param string $value Current value
     * @param string $position Position (top, right, bottom, left)
     * @param string $type Type (margin, padding)
     * @return string HTML
     */
    protected function render_spacing_input(
        string $name,
        string $value,
        string $position,
        string $type
    ): string {
        return sprintf(
            '<input type="text"
                name="%s"
                value="%s"
                class="tb4-spacing-input tb4-%s-%s"
                data-position="%s"
                data-type="%s"
                placeholder="-"
                autocomplete="off">',
            $this->esc_attr($name),
            $this->esc_attr($value),
            $type,
            $position,
            $position,
            $type
        );
    }

    /**
     * Render link toggle button
     *
     * @param string $name Input name
     * @param bool $linked Whether values are linked
     * @param string $type Type (margin, padding)
     * @return string HTML
     */
    protected function render_link_toggle(string $name, bool $linked, string $type): string
    {
        $id = 'tb4-' . str_replace(['[', ']'], ['-', ''], $name);
        $active = $linked ? ' tb4-active' : '';
        $checked = $linked ? ' checked' : '';

        return sprintf(
            '<div class="tb4-link-toggle tb4-%s-link">
                <input type="hidden" name="%s" value="0">
                <input type="checkbox" id="%s" name="%s" value="1"%s class="tb4-link-checkbox">
                <label for="%s" class="tb4-link-btn%s" title="Link values">
                    <span class="tb4-link-icon">🔗</span>
                </label>
            </div>',
            $type,
            $this->esc_attr($name),
            $this->esc_attr($id),
            $this->esc_attr($name),
            $checked,
            $this->esc_attr($id),
            $active
        );
    }

    /**
     * Render unit selector
     *
     * @param string $name Input name
     * @param string $value Current unit
     * @param string $type Type (margin, padding)
     * @return string HTML
     */
    protected function render_unit_selector(string $name, string $value, string $type): string
    {
        $options_html = '';
        foreach ($this->units as $unit_value => $unit_label) {
            $selected = ($value === $unit_value) ? ' selected' : '';
            $options_html .= sprintf(
                '<option value="%s"%s>%s</option>',
                $this->esc_attr($unit_value),
                $selected,
                $this->esc_attr($unit_label)
            );
        }

        return sprintf(
            '<select name="%s" class="tb4-unit-select tb4-%s-unit">%s</select>',
            $this->esc_attr($name),
            $type,
            $options_html
        );
    }

    /**
     * Generate CSS from spacing values
     *
     * @param string $selector CSS selector
     * @param array $values Field values
     * @return string CSS
     */
    public function generate_css(string $selector, array $values): string
    {
        $values = $this->merge_defaults($values);
        $css = '';

        // Generate margin and padding for each breakpoint
        foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
            $breakpoint_css = '';

            // Margin
            $margin_css = $this->generate_spacing_rules(
                $selector,
                'margin',
                $values['margin'][$breakpoint]
            );

            // Padding
            $padding_css = $this->generate_spacing_rules(
                $selector,
                'padding',
                $values['padding'][$breakpoint]
            );

            $breakpoint_css = $margin_css . $padding_css;

            if (!empty($breakpoint_css)) {
                $css .= $this->wrap_media_query($breakpoint, $breakpoint_css);
            }
        }

        return $css;
    }

    /**
     * Generate spacing rules for a single type (margin or padding)
     *
     * @param string $selector CSS selector
     * @param string $property Property name (margin, padding)
     * @param array $values Spacing values for sides
     * @return string CSS rules
     */
    protected function generate_spacing_rules(
        string $selector,
        string $property,
        array $values
    ): string {
        $unit = $values['unit'] ?? 'px';
        $rules = [];

        // Check if we can use shorthand
        $top = $values['top'] ?? '';
        $right = $values['right'] ?? '';
        $bottom = $values['bottom'] ?? '';
        $left = $values['left'] ?? '';

        // If all values are empty, return empty string
        if ($top === '' && $right === '' && $bottom === '' && $left === '') {
            return '';
        }

        // If all values are the same and not empty
        if ($top !== '' && $top === $right && $top === $bottom && $top === $left) {
            $value = $this->format_spacing_value($top, $unit);
            $rules[] = "{$property}: {$value}";
        }
        // If vertical and horizontal are pairs
        elseif ($top !== '' && $right !== '' && $top === $bottom && $right === $left) {
            $v = $this->format_spacing_value($top, $unit);
            $h = $this->format_spacing_value($right, $unit);
            $rules[] = "{$property}: {$v} {$h}";
        }
        // Otherwise, use individual properties
        else {
            if ($top !== '') {
                $rules[] = "{$property}-top: " . $this->format_spacing_value($top, $unit);
            }
            if ($right !== '') {
                $rules[] = "{$property}-right: " . $this->format_spacing_value($right, $unit);
            }
            if ($bottom !== '') {
                $rules[] = "{$property}-bottom: " . $this->format_spacing_value($bottom, $unit);
            }
            if ($left !== '') {
                $rules[] = "{$property}-left: " . $this->format_spacing_value($left, $unit);
            }
        }

        if (empty($rules)) {
            return '';
        }

        return "{$selector} {\n    " . implode(";\n    ", $rules) . ";\n}\n";
    }

    /**
     * Format a spacing value with unit
     *
     * @param string $value The value
     * @param string $unit The unit
     * @return string Formatted value
     */
    protected function format_spacing_value(string $value, string $unit): string
    {
        $value = $this->sanitize_css_value($value);

        // Handle special values
        if ($value === 'auto' || $value === 'inherit' || $value === 'initial') {
            return $value;
        }

        // If value already has a unit, use as-is
        if (preg_match('/(px|em|rem|%|vh|vw)$/i', $value)) {
            return $value;
        }

        // If value is 0, no unit needed
        if ($value === '0') {
            return '0';
        }

        // Add unit
        return $value . $unit;
    }

    /**
     * Get inline styles for quick application
     *
     * @param array $values Field values
     * @param string $breakpoint Specific breakpoint (default: desktop)
     * @return string Inline style attribute value
     */
    public function get_inline_styles(array $values, string $breakpoint = 'desktop'): string
    {
        $values = $this->merge_defaults($values);
        $styles = [];

        // Margin
        $margin = $values['margin'][$breakpoint];
        $margin_unit = $margin['unit'] ?? 'px';

        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (!empty($margin[$side])) {
                $styles[] = "margin-{$side}: " . $this->format_spacing_value($margin[$side], $margin_unit);
            }
        }

        // Padding
        $padding = $values['padding'][$breakpoint];
        $padding_unit = $padding['unit'] ?? 'px';

        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (!empty($padding[$side])) {
                $styles[] = "padding-{$side}: " . $this->format_spacing_value($padding[$side], $padding_unit);
            }
        }

        return implode('; ', $styles);
    }

    /**
     * Set all spacing values at once
     *
     * @param string $type margin or padding
     * @param string $breakpoint desktop, tablet, or mobile
     * @param string $top Top value
     * @param string $right Right value
     * @param string $bottom Bottom value
     * @param string $left Left value
     * @param string $unit Unit to use
     * @return array Updated values structure
     */
    public function set_spacing(
        string $type,
        string $breakpoint,
        string $top,
        string $right,
        string $bottom,
        string $left,
        string $unit = 'px'
    ): array {
        return [
            $type => [
                $breakpoint => [
                    'top'    => $top,
                    'right'  => $right,
                    'bottom' => $bottom,
                    'left'   => $left,
                    'unit'   => $unit,
                    'linked' => ($top === $right && $top === $bottom && $top === $left),
                ],
            ],
        ];
    }

    /**
     * Set uniform spacing (same value for all sides)
     *
     * @param string $type margin or padding
     * @param string $value The value for all sides
     * @param string $unit Unit to use
     * @return array Values structure for all breakpoints
     */
    public function set_uniform_spacing(string $type, string $value, string $unit = 'px'): array
    {
        $breakpoint_values = [
            'top'    => $value,
            'right'  => $value,
            'bottom' => $value,
            'left'   => $value,
            'unit'   => $unit,
            'linked' => true,
        ];

        return [
            $type => [
                'desktop' => $breakpoint_values,
                'tablet'  => $breakpoint_values,
                'mobile'  => $breakpoint_values,
            ],
        ];
    }
}
