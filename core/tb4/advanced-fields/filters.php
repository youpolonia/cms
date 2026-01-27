<?php
/**
 * TB4 Filters Advanced Field
 *
 * Handles CSS filter effects including blur, brightness, contrast, grayscale,
 * hue-rotate, invert, opacity, saturate, and sepia with hover state support
 *
 * @package TB4\AdvancedFields
 * @since 1.0.0
 */

namespace TB4\AdvancedFields;

require_once __DIR__ . '/advancedfield.php';

class Filters extends AdvancedField
{
    /**
     * Filter definitions with their units and ranges
     * @var array
     */
    protected array $filter_config = [
        'blur' => [
            'label' => 'Blur',
            'unit'  => 'px',
            'min'   => 0,
            'max'   => 20,
            'step'  => 0.5,
            'default' => 0,
        ],
        'brightness' => [
            'label' => 'Brightness',
            'unit'  => '%',
            'min'   => 0,
            'max'   => 200,
            'step'  => 1,
            'default' => 100,
        ],
        'contrast' => [
            'label' => 'Contrast',
            'unit'  => '%',
            'min'   => 0,
            'max'   => 200,
            'step'  => 1,
            'default' => 100,
        ],
        'grayscale' => [
            'label' => 'Grayscale',
            'unit'  => '%',
            'min'   => 0,
            'max'   => 100,
            'step'  => 1,
            'default' => 0,
        ],
        'hue_rotate' => [
            'label' => 'Hue Rotate',
            'unit'  => 'deg',
            'min'   => 0,
            'max'   => 360,
            'step'  => 1,
            'default' => 0,
        ],
        'invert' => [
            'label' => 'Invert',
            'unit'  => '%',
            'min'   => 0,
            'max'   => 100,
            'step'  => 1,
            'default' => 0,
        ],
        'opacity' => [
            'label' => 'Opacity',
            'unit'  => '%',
            'min'   => 0,
            'max'   => 100,
            'step'  => 1,
            'default' => 100,
        ],
        'saturate' => [
            'label' => 'Saturate',
            'unit'  => '%',
            'min'   => 0,
            'max'   => 200,
            'step'  => 1,
            'default' => 100,
        ],
        'sepia' => [
            'label' => 'Sepia',
            'unit'  => '%',
            'min'   => 0,
            'max'   => 100,
            'step'  => 1,
            'default' => 0,
        ],
    ];

    /**
     * Get default filter values
     *
     * @return array
     */
    public function get_defaults(): array
    {
        return [
            // Normal state filters
            'blur'        => '',
            'brightness'  => '',
            'contrast'    => '',
            'grayscale'   => '',
            'hue_rotate'  => '',
            'invert'      => '',
            'opacity'     => '',
            'saturate'    => '',
            'sepia'       => '',
            // Hover state filters
            'hover_blur'       => '',
            'hover_brightness' => '',
            'hover_contrast'   => '',
            'hover_grayscale'  => '',
            'hover_hue_rotate' => '',
            'hover_invert'     => '',
            'hover_opacity'    => '',
            'hover_saturate'   => '',
            'hover_sepia'      => '',
        ];
    }

    /**
     * Render filters control panel
     *
     * @param string $prefix Input name prefix
     * @param array $values Current values
     * @return string HTML
     */
    public function render_controls(string $prefix, array $values): string
    {
        $values = $this->merge_defaults($values);

        $html = '<div class="tb4-filters-panel tb4-advanced-panel">';
        $html .= '<div class="tb4-panel-header">CSS Filters</div>';
        $html .= '<div class="tb4-panel-content">';

        // Normal State Section
        $html .= '<div class="tb4-section">';
        $html .= '<div class="tb4-section-header">Normal State</div>';
        $html .= '<div class="tb4-section-content">';

        foreach ($this->filter_config as $key => $config) {
            $html .= $this->render_filter_input(
                "{$prefix}[{$key}]",
                $values[$key],
                $config
            );
        }

        $html .= '</div>'; // section-content
        $html .= '</div>'; // section

        // Hover State Section
        $html .= '<div class="tb4-section">';
        $html .= '<div class="tb4-section-header">Hover State</div>';
        $html .= '<div class="tb4-section-content">';

        foreach ($this->filter_config as $key => $config) {
            $hover_key = 'hover_' . $key;
            $hover_config = $config;
            $hover_config['label'] = $config['label'] . ' (Hover)';

            $html .= $this->render_filter_input(
                "{$prefix}[{$hover_key}]",
                $values[$hover_key],
                $hover_config
            );
        }

        $html .= '</div>'; // section-content
        $html .= '</div>'; // section

        $html .= '</div>'; // panel-content
        $html .= '</div>'; // panel

        return $html;
    }

    /**
     * Render a single filter input control
     *
     * @param string $name Input name
     * @param string $value Current value
     * @param array $config Filter configuration
     * @return string HTML
     */
    protected function render_filter_input(string $name, string $value, array $config): string
    {
        $id = 'tb4-' . str_replace(['[', ']', ' '], ['-', '', '-'], $name);
        $unit_label = $config['unit'] === 'deg' ? '°' : $config['unit'];

        return sprintf(
            '<div class="tb4-field-control tb4-filter-control">
                <label for="%s">%s <span class="tb4-unit">(%s)</span></label>
                <div class="tb4-filter-input-wrapper">
                    <input type="range"
                           id="%s-range"
                           min="%s"
                           max="%s"
                           step="%s"
                           value="%s"
                           class="tb4-range"
                           data-target="%s">
                    <input type="text"
                           id="%s"
                           name="%s"
                           value="%s"
                           placeholder="%s"
                           class="tb4-input tb4-input-small tb4-filter-value">
                    <span class="tb4-unit-label">%s</span>
                </div>
            </div>',
            $this->esc_attr($id),
            $this->esc_attr($config['label']),
            $this->esc_attr($unit_label),
            $this->esc_attr($id),
            $this->esc_attr((string)$config['min']),
            $this->esc_attr((string)$config['max']),
            $this->esc_attr((string)$config['step']),
            $this->esc_attr($value !== '' ? $value : (string)$config['default']),
            $this->esc_attr($id),
            $this->esc_attr($id),
            $this->esc_attr($name),
            $this->esc_attr($value),
            $this->esc_attr((string)$config['default']),
            $this->esc_attr($unit_label)
        );
    }

    /**
     * Generate CSS from filter values
     *
     * @param string $selector CSS selector
     * @param array $values Field values
     * @return string CSS
     */
    public function generate_css(string $selector, array $values): string
    {
        $values = $this->merge_defaults($values);
        $css = '';

        // Build normal state filter string
        $normal_filters = $this->build_filter_string($values, false);
        if (!empty($normal_filters)) {
            $css .= "{$selector} {\n    filter: {$normal_filters};\n}\n";
        }

        // Build hover state filter string
        $hover_filters = $this->build_filter_string($values, true);
        if (!empty($hover_filters)) {
            $css .= "{$selector}:hover {\n    filter: {$hover_filters};\n}\n";
        }

        return $css;
    }

    /**
     * Build CSS filter string from values
     *
     * @param array $values Field values
     * @param bool $hover Whether to use hover values
     * @return string Filter CSS string
     */
    protected function build_filter_string(array $values, bool $hover = false): string
    {
        $filters = [];
        $prefix = $hover ? 'hover_' : '';

        foreach ($this->filter_config as $key => $config) {
            $value_key = $prefix . $key;
            $value = $values[$value_key] ?? '';

            if ($value === '' || $value === null) {
                continue;
            }

            $sanitized_value = $this->sanitize_css_value($value);
            if ($sanitized_value === '') {
                continue;
            }

            // Build the filter function
            $css_function = $this->get_css_function_name($key);
            $unit = $config['unit'];

            // Add unit if value is numeric and doesn't already have one
            if (is_numeric($sanitized_value)) {
                $sanitized_value .= $unit;
            }

            $filters[] = "{$css_function}({$sanitized_value})";
        }

        return implode(' ', $filters);
    }

    /**
     * Convert field key to CSS function name
     *
     * @param string $key Field key
     * @return string CSS function name
     */
    protected function get_css_function_name(string $key): string
    {
        // Convert underscores to hyphens for CSS
        $map = [
            'blur'       => 'blur',
            'brightness' => 'brightness',
            'contrast'   => 'contrast',
            'grayscale'  => 'grayscale',
            'hue_rotate' => 'hue-rotate',
            'invert'     => 'invert',
            'opacity'    => 'opacity',
            'saturate'   => 'saturate',
            'sepia'      => 'sepia',
        ];

        return $map[$key] ?? $key;
    }

    /**
     * Get filter configuration
     *
     * @return array
     */
    public function get_filter_config(): array
    {
        return $this->filter_config;
    }

    /**
     * Set custom min/max for a filter
     *
     * @param string $filter Filter key
     * @param float $min Minimum value
     * @param float $max Maximum value
     * @return self
     */
    public function set_filter_range(string $filter, float $min, float $max): self
    {
        if (isset($this->filter_config[$filter])) {
            $this->filter_config[$filter]['min'] = $min;
            $this->filter_config[$filter]['max'] = $max;
        }
        return $this;
    }

    /**
     * Set custom step for a filter
     *
     * @param string $filter Filter key
     * @param float $step Step value
     * @return self
     */
    public function set_filter_step(string $filter, float $step): self
    {
        if (isset($this->filter_config[$filter])) {
            $this->filter_config[$filter]['step'] = $step;
        }
        return $this;
    }
}
