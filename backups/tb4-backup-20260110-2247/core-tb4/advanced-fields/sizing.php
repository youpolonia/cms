<?php
/**
 * TB4 Sizing Advanced Field
 *
 * Handles width and height settings with min/max constraints and responsive breakpoints
 *
 * @package TB4\AdvancedFields
 * @since 1.0.0
 */

namespace TB4\AdvancedFields;

require_once __DIR__ . '/advancedfield.php';

class Sizing extends AdvancedField
{
    /**
     * Available sizing units
     * @var array
     */
    protected array $units = [
        'px'   => 'px',
        '%'    => '%',
        'vw'   => 'vw',
        'vh'   => 'vh',
        'em'   => 'em',
        'rem'  => 'rem',
        'auto' => 'auto',
    ];

    /**
     * Get default sizing values
     *
     * @return array
     */
    public function get_defaults(): array
    {
        return [
            'width' => [
                'desktop' => '',
                'tablet'  => '',
                'mobile'  => '',
            ],
            'max_width' => [
                'desktop' => '',
                'tablet'  => '',
                'mobile'  => '',
            ],
            'min_width' => [
                'desktop' => '',
                'tablet'  => '',
                'mobile'  => '',
            ],
            'height' => [
                'desktop' => '',
                'tablet'  => '',
                'mobile'  => '',
            ],
            'max_height' => [
                'desktop' => '',
                'tablet'  => '',
                'mobile'  => '',
            ],
            'min_height' => [
                'desktop' => '',
                'tablet'  => '',
                'mobile'  => '',
            ],
            'width_unit'  => 'px',
            'height_unit' => 'px',
        ];
    }

    /**
     * Render sizing control panel with responsive tabs
     *
     * @param string $prefix Input name prefix
     * @param array $values Current values
     * @return string HTML
     */
    public function render_controls(string $prefix, array $values): string
    {
        $values = $this->merge_defaults($values);

        $html = '<div class="tb4-sizing-panel tb4-advanced-panel">';
        $html .= '<div class="tb4-panel-header">Sizing</div>';
        $html .= '<div class="tb4-panel-content">';

        // Responsive Tabs
        $html .= $this->render_responsive_tabs($prefix);

        // Responsive Panels
        foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
            $active = $breakpoint === 'desktop' ? ' tb4-active' : '';
            $html .= sprintf(
                '<div class="tb4-sizing-breakpoint%s" data-breakpoint="%s">',
                $active,
                $breakpoint
            );

            // Width Section
            $html .= $this->render_dimension_section(
                $prefix,
                $breakpoint,
                'width',
                $values,
                'Width'
            );

            // Height Section
            $html .= $this->render_dimension_section(
                $prefix,
                $breakpoint,
                'height',
                $values,
                'Height'
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

        $html = '<div class="tb4-sizing-tabs">';
        foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
            $active = $breakpoint === 'desktop' ? ' tb4-active' : '';
            $html .= sprintf(
                '<button type="button" class="tb4-sizing-tab%s" data-breakpoint="%s" title="%s">%s</button>',
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
     * Render a dimension section (width or height with min/max)
     *
     * @param string $prefix Input prefix
     * @param string $breakpoint Current breakpoint
     * @param string $dimension width or height
     * @param array $values All values
     * @param string $label Section label
     * @return string HTML
     */
    protected function render_dimension_section(
        string $prefix,
        string $breakpoint,
        string $dimension,
        array $values,
        string $label
    ): string {
        $unit_key = $dimension . '_unit';
        $current_unit = $values[$unit_key] ?? 'px';

        $html = '<div class="tb4-sizing-section tb4-sizing-' . $dimension . '">';
        $html .= '<div class="tb4-sizing-section-header">';
        $html .= '<span class="tb4-sizing-section-label">' . $this->esc_attr($label) . '</span>';

        // Unit selector (only show on desktop breakpoint to avoid duplication)
        if ($breakpoint === 'desktop') {
            $html .= $this->render_unit_selector(
                "{$prefix}[{$unit_key}]",
                $current_unit,
                $dimension
            );
        }

        $html .= '</div>';

        $html .= '<div class="tb4-sizing-inputs">';

        // Main dimension (width or height)
        $html .= $this->render_sizing_input(
            "{$prefix}[{$dimension}][{$breakpoint}]",
            $values[$dimension][$breakpoint] ?? '',
            ucfirst($dimension),
            $dimension
        );

        // Min dimension
        $min_key = 'min_' . $dimension;
        $html .= $this->render_sizing_input(
            "{$prefix}[{$min_key}][{$breakpoint}]",
            $values[$min_key][$breakpoint] ?? '',
            'Min',
            $min_key
        );

        // Max dimension
        $max_key = 'max_' . $dimension;
        $html .= $this->render_sizing_input(
            "{$prefix}[{$max_key}][{$breakpoint}]",
            $values[$max_key][$breakpoint] ?? '',
            'Max',
            $max_key
        );

        $html .= '</div>'; // tb4-sizing-inputs
        $html .= '</div>'; // tb4-sizing-section

        return $html;
    }

    /**
     * Render a sizing input field
     *
     * @param string $name Input name
     * @param string $value Current value
     * @param string $label Input label
     * @param string $type Type identifier for styling
     * @return string HTML
     */
    protected function render_sizing_input(
        string $name,
        string $value,
        string $label,
        string $type
    ): string {
        $id = 'tb4-' . str_replace(['[', ']'], ['-', ''], $name);

        return sprintf(
            '<div class="tb4-sizing-input-wrapper">
                <label for="%s" class="tb4-sizing-input-label">%s</label>
                <input type="text"
                    id="%s"
                    name="%s"
                    value="%s"
                    class="tb4-sizing-input tb4-sizing-%s"
                    data-type="%s"
                    placeholder="-"
                    autocomplete="off">
            </div>',
            $this->esc_attr($id),
            $this->esc_attr($label),
            $this->esc_attr($id),
            $this->esc_attr($name),
            $this->esc_attr($value),
            $this->esc_attr($type),
            $this->esc_attr($type)
        );
    }

    /**
     * Render unit selector
     *
     * @param string $name Input name
     * @param string $value Current unit
     * @param string $type Type (width, height)
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
     * Generate CSS from sizing values
     *
     * @param string $selector CSS selector
     * @param array $values Field values
     * @return string CSS
     */
    public function generate_css(string $selector, array $values): string
    {
        $values = $this->merge_defaults($values);
        $css = '';

        $width_unit = $values['width_unit'] ?? 'px';
        $height_unit = $values['height_unit'] ?? 'px';

        // Generate CSS for each breakpoint
        foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
            $rules = [];

            // Width properties
            $width_props = ['width', 'min_width', 'max_width'];
            foreach ($width_props as $prop) {
                $value = $values[$prop][$breakpoint] ?? '';
                if ($value !== '') {
                    $css_prop = str_replace('_', '-', $prop);
                    $formatted = $this->format_sizing_value($value, $width_unit);
                    $rules[] = "{$css_prop}: {$formatted}";
                }
            }

            // Height properties
            $height_props = ['height', 'min_height', 'max_height'];
            foreach ($height_props as $prop) {
                $value = $values[$prop][$breakpoint] ?? '';
                if ($value !== '') {
                    $css_prop = str_replace('_', '-', $prop);
                    $formatted = $this->format_sizing_value($value, $height_unit);
                    $rules[] = "{$css_prop}: {$formatted}";
                }
            }

            if (!empty($rules)) {
                $breakpoint_css = "{$selector} {\n    " . implode(";\n    ", $rules) . ";\n}\n";
                $css .= $this->wrap_media_query($breakpoint, $breakpoint_css);
            }
        }

        return $css;
    }

    /**
     * Format a sizing value with unit
     *
     * @param string $value The value
     * @param string $unit The unit
     * @return string Formatted value
     */
    protected function format_sizing_value(string $value, string $unit): string
    {
        $value = $this->sanitize_css_value($value);

        // Handle special values
        if ($value === 'auto' || $value === 'inherit' || $value === 'initial' || $value === 'none') {
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

        // If unit is auto, return auto keyword
        if ($unit === 'auto') {
            return 'auto';
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

        $width_unit = $values['width_unit'] ?? 'px';
        $height_unit = $values['height_unit'] ?? 'px';

        // Width properties
        $width_props = ['width', 'min_width', 'max_width'];
        foreach ($width_props as $prop) {
            $value = $values[$prop][$breakpoint] ?? '';
            if ($value !== '') {
                $css_prop = str_replace('_', '-', $prop);
                $styles[] = "{$css_prop}: " . $this->format_sizing_value($value, $width_unit);
            }
        }

        // Height properties
        $height_props = ['height', 'min_height', 'max_height'];
        foreach ($height_props as $prop) {
            $value = $values[$prop][$breakpoint] ?? '';
            if ($value !== '') {
                $css_prop = str_replace('_', '-', $prop);
                $styles[] = "{$css_prop}: " . $this->format_sizing_value($value, $height_unit);
            }
        }

        return implode('; ', $styles);
    }

    /**
     * Set width values for a specific breakpoint
     *
     * @param string $breakpoint desktop, tablet, or mobile
     * @param string $width Width value
     * @param string $min_width Min width value
     * @param string $max_width Max width value
     * @param string $unit Unit to use
     * @return array Updated values structure
     */
    public function set_width(
        string $breakpoint,
        string $width,
        string $min_width = '',
        string $max_width = '',
        string $unit = 'px'
    ): array {
        return [
            'width' => [$breakpoint => $width],
            'min_width' => [$breakpoint => $min_width],
            'max_width' => [$breakpoint => $max_width],
            'width_unit' => $unit,
        ];
    }

    /**
     * Set height values for a specific breakpoint
     *
     * @param string $breakpoint desktop, tablet, or mobile
     * @param string $height Height value
     * @param string $min_height Min height value
     * @param string $max_height Max height value
     * @param string $unit Unit to use
     * @return array Updated values structure
     */
    public function set_height(
        string $breakpoint,
        string $height,
        string $min_height = '',
        string $max_height = '',
        string $unit = 'px'
    ): array {
        return [
            'height' => [$breakpoint => $height],
            'min_height' => [$breakpoint => $min_height],
            'max_height' => [$breakpoint => $max_height],
            'height_unit' => $unit,
        ];
    }

    /**
     * Set full-width responsive sizing (100% width with max-width)
     *
     * @param string $max_width Maximum width value
     * @param string $unit Unit for max-width
     * @return array Values structure for all breakpoints
     */
    public function set_full_width_container(string $max_width = '1200', string $unit = 'px'): array
    {
        return [
            'width' => [
                'desktop' => '100',
                'tablet'  => '100',
                'mobile'  => '100',
            ],
            'max_width' => [
                'desktop' => $max_width,
                'tablet'  => '',
                'mobile'  => '',
            ],
            'min_width' => [
                'desktop' => '',
                'tablet'  => '',
                'mobile'  => '',
            ],
            'width_unit' => '%',
        ];
    }

    /**
     * Set fixed dimensions for all breakpoints
     *
     * @param string $width Width value
     * @param string $height Height value
     * @param string $unit Unit to use
     * @return array Values structure
     */
    public function set_fixed_dimensions(
        string $width,
        string $height,
        string $unit = 'px'
    ): array {
        $dimensions = [
            'width' => [
                'desktop' => $width,
                'tablet'  => $width,
                'mobile'  => $width,
            ],
            'height' => [
                'desktop' => $height,
                'tablet'  => $height,
                'mobile'  => $height,
            ],
            'width_unit'  => $unit,
            'height_unit' => $unit,
        ];

        return array_merge($this->get_defaults(), $dimensions);
    }
}
