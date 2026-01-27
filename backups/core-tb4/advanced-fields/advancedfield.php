<?php
/**
 * TB4 Advanced Field Base Class
 *
 * Abstract base class for all advanced field types (typography, background, spacing, etc.)
 *
 * @package TB4\AdvancedFields
 * @since 1.0.0
 */

namespace TB4\AdvancedFields;

abstract class AdvancedField
{
    /**
     * Default values for the field
     * @var array
     */
    protected array $defaults = [];

    /**
     * Field identifier
     * @var string
     */
    protected string $field_id = '';

    /**
     * Responsive breakpoints configuration
     * @var array
     */
    protected array $breakpoints = [
        'desktop' => '',
        'tablet'  => '1024px',
        'mobile'  => '768px'
    ];

    /**
     * Get the default values for this field type
     *
     * @return array Default field values
     */
    abstract public function get_defaults(): array;

    /**
     * Render the control panel HTML for this field
     *
     * @param string $prefix Input name prefix for form fields
     * @param array $values Current field values
     * @return string HTML output for the control panel
     */
    abstract public function render_controls(string $prefix, array $values): string;

    /**
     * Generate CSS from field values
     *
     * @param string $selector CSS selector to apply styles to
     * @param array $values Field values
     * @return string Generated CSS
     */
    abstract public function generate_css(string $selector, array $values): string;

    /**
     * Merge provided values with defaults
     *
     * @param array $values Values to merge
     * @return array Merged values with defaults
     */
    public function merge_defaults(array $values): array
    {
        return $this->array_merge_recursive_distinct($this->get_defaults(), $values);
    }

    /**
     * Recursive array merge that replaces values instead of appending
     *
     * @param array $base Base array
     * @param array $override Override array
     * @return array Merged array
     */
    protected function array_merge_recursive_distinct(array $base, array $override): array
    {
        $merged = $base;

        foreach ($override as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->array_merge_recursive_distinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Sanitize a CSS value
     *
     * @param mixed $value Value to sanitize
     * @return string Sanitized value
     */
    protected function sanitize_css_value(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        // Remove potentially dangerous characters
        $value = (string) $value;
        $value = preg_replace('/[;\{\}]/', '', $value);

        return trim($value);
    }

    /**
     * Generate a CSS unit value (adds unit if numeric)
     *
     * @param mixed $value The value
     * @param string $default_unit Default unit to use if value is numeric
     * @return string Value with unit
     */
    protected function css_unit(mixed $value, string $default_unit = 'px'): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $value = $this->sanitize_css_value($value);

        // If already has a unit or is a special value, return as-is
        if (preg_match('/(px|em|rem|%|vh|vw|auto|inherit|initial|unset)$/i', $value)) {
            return $value;
        }

        // If numeric, add default unit
        if (is_numeric($value)) {
            return $value . $default_unit;
        }

        return $value;
    }

    /**
     * Escape HTML attribute
     *
     * @param string $value Value to escape
     * @return string Escaped value
     */
    protected function esc_attr(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate media query wrapper for responsive CSS
     *
     * @param string $breakpoint Breakpoint key (desktop, tablet, mobile)
     * @param string $css CSS content
     * @return string CSS wrapped in media query if needed
     */
    protected function wrap_media_query(string $breakpoint, string $css): string
    {
        if (empty($css)) {
            return '';
        }

        if ($breakpoint === 'desktop' || empty($this->breakpoints[$breakpoint])) {
            return $css;
        }

        $max_width = $this->breakpoints[$breakpoint];
        return "@media (max-width: {$max_width}) {\n{$css}\n}";
    }

    /**
     * Generate responsive CSS for a property
     *
     * @param string $selector CSS selector
     * @param string $property CSS property
     * @param array $responsive_values Values keyed by breakpoint
     * @param string $unit Default unit
     * @return string Generated CSS with media queries
     */
    protected function generate_responsive_css(
        string $selector,
        string $property,
        array $responsive_values,
        string $unit = 'px'
    ): string {
        $output = '';

        foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
            if (!isset($responsive_values[$breakpoint]) || $responsive_values[$breakpoint] === '') {
                continue;
            }

            $value = $this->css_unit($responsive_values[$breakpoint], $unit);
            $css = "{$selector} {\n    {$property}: {$value};\n}\n";
            $output .= $this->wrap_media_query($breakpoint, $css);
        }

        return $output;
    }

    /**
     * Render a text input control
     *
     * @param string $name Input name
     * @param string $value Current value
     * @param string $label Label text
     * @param string $placeholder Placeholder text
     * @return string HTML
     */
    protected function render_text_input(
        string $name,
        string $value,
        string $label,
        string $placeholder = ''
    ): string {
        $id = 'tb4-' . str_replace(['[', ']', ' '], ['-', '', '-'], $name);

        return sprintf(
            '<div class="tb4-field-control">
                <label for="%s">%s</label>
                <input type="text" id="%s" name="%s" value="%s" placeholder="%s" class="tb4-input">
            </div>',
            $this->esc_attr($id),
            $this->esc_attr($label),
            $this->esc_attr($id),
            $this->esc_attr($name),
            $this->esc_attr($value),
            $this->esc_attr($placeholder)
        );
    }

    /**
     * Render a select control
     *
     * @param string $name Input name
     * @param string $value Current value
     * @param string $label Label text
     * @param array $options Options as value => label pairs
     * @return string HTML
     */
    protected function render_select(
        string $name,
        string $value,
        string $label,
        array $options
    ): string {
        $id = 'tb4-' . str_replace(['[', ']', ' '], ['-', '', '-'], $name);

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

        return sprintf(
            '<div class="tb4-field-control">
                <label for="%s">%s</label>
                <select id="%s" name="%s" class="tb4-select">%s</select>
            </div>',
            $this->esc_attr($id),
            $this->esc_attr($label),
            $this->esc_attr($id),
            $this->esc_attr($name),
            $options_html
        );
    }

    /**
     * Render a color picker control
     *
     * @param string $name Input name
     * @param string $value Current value
     * @param string $label Label text
     * @return string HTML
     */
    protected function render_color_picker(string $name, string $value, string $label): string
    {
        $id = 'tb4-' . str_replace(['[', ']', ' '], ['-', '', '-'], $name);

        return sprintf(
            '<div class="tb4-field-control tb4-color-control">
                <label for="%s">%s</label>
                <div class="tb4-color-picker-wrapper">
                    <input type="color" id="%s-picker" value="%s" class="tb4-color-picker" data-target="%s">
                    <input type="text" id="%s" name="%s" value="%s" class="tb4-color-input" placeholder="#000000 or rgba()">
                </div>
            </div>',
            $this->esc_attr($id),
            $this->esc_attr($label),
            $this->esc_attr($id),
            $this->esc_attr($value ?: '#000000'),
            $this->esc_attr($id),
            $this->esc_attr($id),
            $this->esc_attr($name),
            $this->esc_attr($value)
        );
    }

    /**
     * Render a responsive input control (desktop/tablet/mobile)
     *
     * @param string $name Base input name
     * @param array $values Current values keyed by breakpoint
     * @param string $label Label text
     * @param string $placeholder Placeholder text
     * @return string HTML
     */
    protected function render_responsive_input(
        string $name,
        array $values,
        string $label,
        string $placeholder = ''
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
            $html .= sprintf(
                '<div class="tb4-responsive-input" data-breakpoint="%s">
                    <span class="tb4-breakpoint-icon" title="%s">%s</span>
                    <input type="text" name="%s[%s]" value="%s" placeholder="%s" class="tb4-input tb4-input-small">
                </div>',
                $breakpoint,
                ucfirst($breakpoint),
                $icons[$breakpoint],
                $this->esc_attr($name),
                $breakpoint,
                $this->esc_attr($value),
                $this->esc_attr($placeholder)
            );
        }

        $html .= '</div></div>';
        return $html;
    }

    /**
     * Set custom breakpoints
     *
     * @param array $breakpoints Breakpoints configuration
     * @return self
     */
    public function set_breakpoints(array $breakpoints): self
    {
        $this->breakpoints = array_merge($this->breakpoints, $breakpoints);
        return $this;
    }
}
