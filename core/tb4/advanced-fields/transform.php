<?php
/**
 * TB4 Transform Advanced Field
 *
 * Handles CSS transform properties including translate, rotate, scale, skew
 * with responsive breakpoints and hover state support
 *
 * @package TB4\AdvancedFields
 * @since 1.0.0
 */

namespace TB4\AdvancedFields;

require_once __DIR__ . '/advancedfield.php';

class Transform extends AdvancedField
{
    /**
     * Transform function definitions with their units and ranges
     * @var array
     */
    protected array $transform_config = [
        'translate_x' => [
            'label'   => 'Translate X',
            'unit'    => 'px',
            'min'     => -500,
            'max'     => 500,
            'step'    => 1,
            'default' => 0,
        ],
        'translate_y' => [
            'label'   => 'Translate Y',
            'unit'    => 'px',
            'min'     => -500,
            'max'     => 500,
            'step'    => 1,
            'default' => 0,
        ],
        'rotate' => [
            'label'   => 'Rotate',
            'unit'    => 'deg',
            'min'     => -360,
            'max'     => 360,
            'step'    => 1,
            'default' => 0,
        ],
        'scale_x' => [
            'label'   => 'Scale X',
            'unit'    => '',
            'min'     => 0,
            'max'     => 2,
            'step'    => 0.05,
            'default' => 1,
        ],
        'scale_y' => [
            'label'   => 'Scale Y',
            'unit'    => '',
            'min'     => 0,
            'max'     => 2,
            'step'    => 0.05,
            'default' => 1,
        ],
        'skew_x' => [
            'label'   => 'Skew X',
            'unit'    => 'deg',
            'min'     => -90,
            'max'     => 90,
            'step'    => 1,
            'default' => 0,
        ],
        'skew_y' => [
            'label'   => 'Skew Y',
            'unit'    => 'deg',
            'min'     => -90,
            'max'     => 90,
            'step'    => 1,
            'default' => 0,
        ],
    ];

    /**
     * Transform origin options
     * @var array
     */
    protected array $origin_options = [
        'x' => [
            ''       => 'Default',
            'left'   => 'Left',
            'center' => 'Center',
            'right'  => 'Right',
        ],
        'y' => [
            ''       => 'Default',
            'top'    => 'Top',
            'center' => 'Center',
            'bottom' => 'Bottom',
        ],
    ];

    /**
     * Breakpoint labels
     * @var array
     */
    protected array $breakpoints = [
        'desktop' => 'Desktop',
        'tablet'  => 'Tablet',
        'mobile'  => 'Mobile',
    ];

    /**
     * Get default transform values
     *
     * @return array
     */
    public function get_defaults(): array
    {
        $defaults = [];

        // Responsive transform values (desktop, tablet, mobile)
        foreach (array_keys($this->transform_config) as $key) {
            $defaults[$key] = [
                'desktop' => '',
                'tablet'  => '',
                'mobile'  => '',
            ];
        }

        // Transform origin (non-responsive)
        $defaults['origin_x'] = '';
        $defaults['origin_y'] = '';

        // Hover transforms (non-responsive)
        foreach (array_keys($this->transform_config) as $key) {
            $defaults['hover_' . $key] = '';
        }

        return $defaults;
    }

    /**
     * Render transform control panel
     *
     * @param string $prefix Input name prefix
     * @param array $values Current values
     * @return string HTML
     */
    public function render_controls(string $prefix, array $values): string
    {
        $values = $this->merge_defaults($values);

        $html = '<div class="tb4-transform-panel tb4-advanced-panel">';
        $html .= '<div class="tb4-panel-header">CSS Transforms</div>';
        $html .= '<div class="tb4-panel-content">';

        // Normal State Section - Responsive
        $html .= '<div class="tb4-section">';
        $html .= '<div class="tb4-section-header">Normal State</div>';
        $html .= '<div class="tb4-section-content">';

        // Translate controls
        $html .= '<div class="tb4-subsection">';
        $html .= '<div class="tb4-subsection-header">Translate</div>';
        $html .= $this->render_responsive_input("{$prefix}[translate_x]", $values['translate_x'], $this->transform_config['translate_x']);
        $html .= $this->render_responsive_input("{$prefix}[translate_y]", $values['translate_y'], $this->transform_config['translate_y']);
        $html .= '</div>';

        // Rotate control
        $html .= '<div class="tb4-subsection">';
        $html .= '<div class="tb4-subsection-header">Rotate</div>';
        $html .= $this->render_responsive_input("{$prefix}[rotate]", $values['rotate'], $this->transform_config['rotate']);
        $html .= '</div>';

        // Scale controls
        $html .= '<div class="tb4-subsection">';
        $html .= '<div class="tb4-subsection-header">Scale</div>';
        $html .= $this->render_responsive_input("{$prefix}[scale_x]", $values['scale_x'], $this->transform_config['scale_x']);
        $html .= $this->render_responsive_input("{$prefix}[scale_y]", $values['scale_y'], $this->transform_config['scale_y']);
        $html .= '</div>';

        // Skew controls
        $html .= '<div class="tb4-subsection">';
        $html .= '<div class="tb4-subsection-header">Skew</div>';
        $html .= $this->render_responsive_input("{$prefix}[skew_x]", $values['skew_x'], $this->transform_config['skew_x']);
        $html .= $this->render_responsive_input("{$prefix}[skew_y]", $values['skew_y'], $this->transform_config['skew_y']);
        $html .= '</div>';

        // Transform Origin
        $html .= '<div class="tb4-subsection">';
        $html .= '<div class="tb4-subsection-header">Transform Origin</div>';
        $html .= $this->render_origin_controls($prefix, $values);
        $html .= '</div>';

        $html .= '</div>'; // section-content
        $html .= '</div>'; // section

        // Hover State Section - Non-responsive
        $html .= '<div class="tb4-section">';
        $html .= '<div class="tb4-section-header">Hover State</div>';
        $html .= '<div class="tb4-section-content">';

        // Hover Translate
        $html .= '<div class="tb4-subsection">';
        $html .= '<div class="tb4-subsection-header">Translate</div>';
        $html .= $this->render_single_input("{$prefix}[hover_translate_x]", $values['hover_translate_x'], $this->transform_config['translate_x'], 'Translate X (Hover)');
        $html .= $this->render_single_input("{$prefix}[hover_translate_y]", $values['hover_translate_y'], $this->transform_config['translate_y'], 'Translate Y (Hover)');
        $html .= '</div>';

        // Hover Rotate
        $html .= '<div class="tb4-subsection">';
        $html .= '<div class="tb4-subsection-header">Rotate</div>';
        $html .= $this->render_single_input("{$prefix}[hover_rotate]", $values['hover_rotate'], $this->transform_config['rotate'], 'Rotate (Hover)');
        $html .= '</div>';

        // Hover Scale
        $html .= '<div class="tb4-subsection">';
        $html .= '<div class="tb4-subsection-header">Scale</div>';
        $html .= $this->render_single_input("{$prefix}[hover_scale_x]", $values['hover_scale_x'], $this->transform_config['scale_x'], 'Scale X (Hover)');
        $html .= $this->render_single_input("{$prefix}[hover_scale_y]", $values['hover_scale_y'], $this->transform_config['scale_y'], 'Scale Y (Hover)');
        $html .= '</div>';

        // Hover Skew
        $html .= '<div class="tb4-subsection">';
        $html .= '<div class="tb4-subsection-header">Skew</div>';
        $html .= $this->render_single_input("{$prefix}[hover_skew_x]", $values['hover_skew_x'], $this->transform_config['skew_x'], 'Skew X (Hover)');
        $html .= $this->render_single_input("{$prefix}[hover_skew_y]", $values['hover_skew_y'], $this->transform_config['skew_y'], 'Skew Y (Hover)');
        $html .= '</div>';

        $html .= '</div>'; // section-content
        $html .= '</div>'; // section

        $html .= '</div>'; // panel-content
        $html .= '</div>'; // panel

        return $html;
    }

    /**
     * Render responsive input control (desktop, tablet, mobile)
     *
     * @param string $name Input name
     * @param array $values Current values for each breakpoint
     * @param array $config Transform configuration
     * @return string HTML
     */
    protected function render_responsive_input(string $name, array $values, array $config): string
    {
        $base_id = 'tb4-' . str_replace(['[', ']', ' '], ['-', '', '-'], $name);
        $unit_label = $config['unit'] === 'deg' ? '°' : ($config['unit'] ?: '×');

        $html = '<div class="tb4-field-control tb4-transform-control tb4-responsive-control">';
        $html .= sprintf('<label>%s <span class="tb4-unit">(%s)</span></label>', $this->esc_attr($config['label']), $this->esc_attr($unit_label));

        $html .= '<div class="tb4-responsive-inputs">';
        foreach ($this->breakpoints as $breakpoint => $label) {
            $input_id = "{$base_id}-{$breakpoint}";
            $input_name = "{$name}[{$breakpoint}]";
            $value = $values[$breakpoint] ?? '';

            $html .= sprintf(
                '<div class="tb4-breakpoint-input" data-breakpoint="%s">
                    <span class="tb4-breakpoint-label">%s</span>
                    <div class="tb4-input-wrapper">
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
                               class="tb4-input tb4-input-small tb4-transform-value">
                        <span class="tb4-unit-label">%s</span>
                    </div>
                </div>',
                $this->esc_attr($breakpoint),
                $this->esc_attr($label),
                $this->esc_attr($input_id),
                $this->esc_attr((string)$config['min']),
                $this->esc_attr((string)$config['max']),
                $this->esc_attr((string)$config['step']),
                $this->esc_attr($value !== '' ? $value : (string)$config['default']),
                $this->esc_attr($input_id),
                $this->esc_attr($input_id),
                $this->esc_attr($input_name),
                $this->esc_attr($value),
                $this->esc_attr((string)$config['default']),
                $this->esc_attr($unit_label)
            );
        }
        $html .= '</div>'; // responsive-inputs

        $html .= '</div>'; // field-control

        return $html;
    }

    /**
     * Render single (non-responsive) input control
     *
     * @param string $name Input name
     * @param string $value Current value
     * @param array $config Transform configuration
     * @param string $label_override Optional label override
     * @return string HTML
     */
    protected function render_single_input(string $name, string $value, array $config, string $label_override = ''): string
    {
        $id = 'tb4-' . str_replace(['[', ']', ' '], ['-', '', '-'], $name);
        $unit_label = $config['unit'] === 'deg' ? '°' : ($config['unit'] ?: '×');
        $label = $label_override ?: $config['label'];

        return sprintf(
            '<div class="tb4-field-control tb4-transform-control">
                <label for="%s">%s <span class="tb4-unit">(%s)</span></label>
                <div class="tb4-input-wrapper">
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
                           class="tb4-input tb4-input-small tb4-transform-value">
                    <span class="tb4-unit-label">%s</span>
                </div>
            </div>',
            $this->esc_attr($id),
            $this->esc_attr($label),
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
     * Render transform origin controls
     *
     * @param string $prefix Input name prefix
     * @param array $values Current values
     * @return string HTML
     */
    protected function render_origin_controls(string $prefix, array $values): string
    {
        $html = '<div class="tb4-origin-controls">';

        // Origin X
        $html .= '<div class="tb4-field-control">';
        $html .= '<label for="tb4-origin-x">Origin X</label>';
        $html .= sprintf('<select id="tb4-origin-x" name="%s[origin_x]" class="tb4-select">', $this->esc_attr($prefix));
        foreach ($this->origin_options['x'] as $value => $label) {
            $selected = ($values['origin_x'] ?? '') === $value ? ' selected' : '';
            $html .= sprintf('<option value="%s"%s>%s</option>', $this->esc_attr($value), $selected, $this->esc_attr($label));
        }
        $html .= '</select>';
        $html .= '<input type="text" name="' . $this->esc_attr($prefix) . '[origin_x_custom]" value="' . $this->esc_attr($values['origin_x_custom'] ?? '') . '" placeholder="e.g. 20% or 50px" class="tb4-input tb4-input-small tb4-origin-custom">';
        $html .= '</div>';

        // Origin Y
        $html .= '<div class="tb4-field-control">';
        $html .= '<label for="tb4-origin-y">Origin Y</label>';
        $html .= sprintf('<select id="tb4-origin-y" name="%s[origin_y]" class="tb4-select">', $this->esc_attr($prefix));
        foreach ($this->origin_options['y'] as $value => $label) {
            $selected = ($values['origin_y'] ?? '') === $value ? ' selected' : '';
            $html .= sprintf('<option value="%s"%s>%s</option>', $this->esc_attr($value), $selected, $this->esc_attr($label));
        }
        $html .= '</select>';
        $html .= '<input type="text" name="' . $this->esc_attr($prefix) . '[origin_y_custom]" value="' . $this->esc_attr($values['origin_y_custom'] ?? '') . '" placeholder="e.g. 20% or 50px" class="tb4-input tb4-input-small tb4-origin-custom">';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Generate CSS from transform values
     *
     * @param string $selector CSS selector
     * @param array $values Field values
     * @return string CSS
     */
    public function generate_css(string $selector, array $values): string
    {
        $values = $this->merge_defaults($values);
        $css = '';

        // Desktop (default) styles
        $desktop_transform = $this->build_transform_string($values, 'desktop');
        $origin = $this->build_origin_string($values);

        if (!empty($desktop_transform) || !empty($origin)) {
            $css .= "{$selector} {\n";
            if (!empty($desktop_transform)) {
                $css .= "    transform: {$desktop_transform};\n";
            }
            if (!empty($origin)) {
                $css .= "    transform-origin: {$origin};\n";
            }
            $css .= "}\n";
        }

        // Hover state
        $hover_transform = $this->build_hover_transform_string($values);
        if (!empty($hover_transform)) {
            $css .= "{$selector}:hover {\n    transform: {$hover_transform};\n}\n";
        }

        // Tablet media query (max-width: 1024px)
        $tablet_transform = $this->build_transform_string($values, 'tablet');
        if (!empty($tablet_transform)) {
            $css .= "@media (max-width: 1024px) {\n";
            $css .= "    {$selector} {\n        transform: {$tablet_transform};\n    }\n";
            $css .= "}\n";
        }

        // Mobile media query (max-width: 768px)
        $mobile_transform = $this->build_transform_string($values, 'mobile');
        if (!empty($mobile_transform)) {
            $css .= "@media (max-width: 768px) {\n";
            $css .= "    {$selector} {\n        transform: {$mobile_transform};\n    }\n";
            $css .= "}\n";
        }

        return $css;
    }

    /**
     * Build CSS transform string for a specific breakpoint
     *
     * @param array $values Field values
     * @param string $breakpoint Breakpoint key (desktop, tablet, mobile)
     * @return string Transform CSS string
     */
    protected function build_transform_string(array $values, string $breakpoint): string
    {
        $transforms = [];

        foreach ($this->transform_config as $key => $config) {
            $value = $values[$key][$breakpoint] ?? '';

            if ($value === '' || $value === null) {
                continue;
            }

            $sanitized_value = $this->sanitize_css_value($value);
            if ($sanitized_value === '') {
                continue;
            }

            $transform_func = $this->build_transform_function($key, $sanitized_value, $config);
            if (!empty($transform_func)) {
                $transforms[] = $transform_func;
            }
        }

        return implode(' ', $transforms);
    }

    /**
     * Build hover state transform string
     *
     * @param array $values Field values
     * @return string Transform CSS string
     */
    protected function build_hover_transform_string(array $values): string
    {
        $transforms = [];

        foreach ($this->transform_config as $key => $config) {
            $hover_key = 'hover_' . $key;
            $value = $values[$hover_key] ?? '';

            if ($value === '' || $value === null) {
                continue;
            }

            $sanitized_value = $this->sanitize_css_value($value);
            if ($sanitized_value === '') {
                continue;
            }

            $transform_func = $this->build_transform_function($key, $sanitized_value, $config);
            if (!empty($transform_func)) {
                $transforms[] = $transform_func;
            }
        }

        return implode(' ', $transforms);
    }

    /**
     * Build a single transform function string
     *
     * @param string $key Transform key
     * @param string $value Sanitized value
     * @param array $config Transform configuration
     * @return string Transform function CSS
     */
    protected function build_transform_function(string $key, string $value, array $config): string
    {
        $unit = $config['unit'];

        // Add unit if value is numeric and doesn't already have one
        if (is_numeric($value) && !empty($unit)) {
            $value .= $unit;
        }

        // Map keys to CSS function names
        $function_map = [
            'translate_x' => 'translateX',
            'translate_y' => 'translateY',
            'rotate'      => 'rotate',
            'scale_x'     => 'scaleX',
            'scale_y'     => 'scaleY',
            'skew_x'      => 'skewX',
            'skew_y'      => 'skewY',
        ];

        $func_name = $function_map[$key] ?? $key;
        return "{$func_name}({$value})";
    }

    /**
     * Build transform-origin string from values
     *
     * @param array $values Field values
     * @return string Transform origin CSS
     */
    protected function build_origin_string(array $values): string
    {
        $origin_x = $values['origin_x'] ?? '';
        $origin_y = $values['origin_y'] ?? '';
        $origin_x_custom = $values['origin_x_custom'] ?? '';
        $origin_y_custom = $values['origin_y_custom'] ?? '';

        // Use custom values if provided, otherwise use select values
        $x = !empty($origin_x_custom) ? $this->sanitize_css_value($origin_x_custom) : $origin_x;
        $y = !empty($origin_y_custom) ? $this->sanitize_css_value($origin_y_custom) : $origin_y;

        if (empty($x) && empty($y)) {
            return '';
        }

        // Default to center if only one axis is specified
        if (empty($x)) {
            $x = 'center';
        }
        if (empty($y)) {
            $y = 'center';
        }

        return "{$x} {$y}";
    }

    /**
     * Get transform configuration
     *
     * @return array
     */
    public function get_transform_config(): array
    {
        return $this->transform_config;
    }

    /**
     * Get origin options
     *
     * @return array
     */
    public function get_origin_options(): array
    {
        return $this->origin_options;
    }

    /**
     * Set custom min/max for a transform property
     *
     * @param string $transform Transform key
     * @param float $min Minimum value
     * @param float $max Maximum value
     * @return self
     */
    public function set_transform_range(string $transform, float $min, float $max): self
    {
        if (isset($this->transform_config[$transform])) {
            $this->transform_config[$transform]['min'] = $min;
            $this->transform_config[$transform]['max'] = $max;
        }
        return $this;
    }

    /**
     * Set custom step for a transform property
     *
     * @param string $transform Transform key
     * @param float $step Step value
     * @return self
     */
    public function set_transform_step(string $transform, float $step): self
    {
        if (isset($this->transform_config[$transform])) {
            $this->transform_config[$transform]['step'] = $step;
        }
        return $this;
    }
}
