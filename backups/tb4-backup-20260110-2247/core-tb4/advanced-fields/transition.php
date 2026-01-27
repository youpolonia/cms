<?php
/**
 * TB4 Transition Advanced Field
 *
 * Handles CSS transitions with configurable duration, delay, easing,
 * and property selection including custom cubic-bezier support
 *
 * @package TB4\AdvancedFields
 * @since 1.0.0
 */

namespace TB4\AdvancedFields;

require_once __DIR__ . '/advancedfield.php';

class Transition extends AdvancedField
{
    /**
     * Easing function options
     * @var array
     */
    protected array $easing_options = [
        'ease'         => 'Ease',
        'ease-in'      => 'Ease In',
        'ease-out'     => 'Ease Out',
        'ease-in-out'  => 'Ease In Out',
        'linear'       => 'Linear',
        'cubic-bezier' => 'Custom Cubic Bezier',
    ];

    /**
     * Transition property options
     * @var array
     */
    protected array $property_options = [
        'all'              => 'All Properties',
        'none'             => 'None',
        'opacity'          => 'Opacity',
        'transform'        => 'Transform',
        'background'       => 'Background',
        'background-color' => 'Background Color',
        'color'            => 'Color',
        'border'           => 'Border',
        'border-color'     => 'Border Color',
        'box-shadow'       => 'Box Shadow',
        'width'            => 'Width',
        'height'           => 'Height',
        'margin'           => 'Margin',
        'padding'          => 'Padding',
        'custom'           => 'Custom',
    ];

    /**
     * Get default transition values
     *
     * @return array
     */
    public function get_defaults(): array
    {
        return [
            'enabled'        => true,
            'duration'       => '300',
            'delay'          => '0',
            'easing'         => 'ease',
            'properties'     => 'all',
            'custom_bezier'  => [
                'x1' => '0.25',
                'y1' => '0.1',
                'x2' => '0.25',
                'y2' => '1.0',
            ],
            'custom_property' => '',
        ];
    }

    /**
     * Render transition control panel
     *
     * @param string $prefix Input name prefix
     * @param array $values Current values
     * @return string HTML
     */
    public function render_controls(string $prefix, array $values): string
    {
        $values = $this->merge_defaults($values);

        $html = '<div class="tb4-transition-panel tb4-advanced-panel">';
        $html .= '<div class="tb4-panel-header">Transition</div>';
        $html .= '<div class="tb4-panel-content">';

        // Enable/Disable Toggle
        $html .= $this->render_toggle(
            "{$prefix}[enabled]",
            (bool) $values['enabled'],
            'Enable Transition'
        );

        // Main Settings Section
        $html .= '<div class="tb4-section tb4-transition-settings" data-requires-enabled="true">';
        $html .= '<div class="tb4-section-header">Settings</div>';
        $html .= '<div class="tb4-section-content">';

        // Duration with slider
        $html .= $this->render_range_input(
            "{$prefix}[duration]",
            $values['duration'],
            'Duration',
            'ms',
            0,
            2000,
            10
        );

        // Delay with slider
        $html .= $this->render_range_input(
            "{$prefix}[delay]",
            $values['delay'],
            'Delay',
            'ms',
            0,
            1000,
            10
        );

        // Easing
        $html .= $this->render_select(
            "{$prefix}[easing]",
            $values['easing'],
            'Easing',
            $this->easing_options
        );

        // Custom Cubic Bezier (shown when cubic-bezier is selected)
        $bezier_visible = $values['easing'] === 'cubic-bezier' ? '' : ' style="display:none;"';
        $html .= sprintf(
            '<div class="tb4-cubic-bezier-controls" data-show-when-easing="cubic-bezier"%s>',
            $bezier_visible
        );
        $html .= $this->render_cubic_bezier_inputs($prefix, $values['custom_bezier']);
        $html .= '</div>';

        $html .= '</div>'; // section-content
        $html .= '</div>'; // section

        // Properties Section
        $html .= '<div class="tb4-section tb4-transition-properties" data-requires-enabled="true">';
        $html .= '<div class="tb4-section-header">Properties</div>';
        $html .= '<div class="tb4-section-content">';

        // Properties select
        $html .= $this->render_select(
            "{$prefix}[properties]",
            $values['properties'],
            'Transition Property',
            $this->property_options
        );

        // Custom property input (shown when custom is selected)
        $custom_visible = $values['properties'] === 'custom' ? '' : ' style="display:none;"';
        $html .= sprintf(
            '<div class="tb4-custom-property-field" data-show-when-property="custom"%s>',
            $custom_visible
        );
        $html .= $this->render_text_input(
            "{$prefix}[custom_property]",
            $values['custom_property'] ?? '',
            'Custom Property',
            'e.g., transform, opacity'
        );
        $html .= '</div>';

        $html .= '</div>'; // section-content
        $html .= '</div>'; // section

        // Preview Section
        $html .= '<div class="tb4-section tb4-transition-preview" data-requires-enabled="true">';
        $html .= '<div class="tb4-section-header">Preview</div>';
        $html .= '<div class="tb4-section-content">';
        $html .= '<div class="tb4-transition-preview-box" title="Hover to preview transition">';
        $html .= '<span>Hover to preview</span>';
        $html .= '</div>';
        $html .= '<div class="tb4-transition-preview-output">';
        $html .= '<code class="tb4-css-output"></code>';
        $html .= '</div>';
        $html .= '</div>'; // section-content
        $html .= '</div>'; // section

        $html .= '</div>'; // panel-content
        $html .= '</div>'; // panel

        return $html;
    }

    /**
     * Render a toggle switch control
     *
     * @param string $name Input name
     * @param bool $checked Current state
     * @param string $label Label text
     * @return string HTML
     */
    protected function render_toggle(string $name, bool $checked, string $label): string
    {
        $id = 'tb4-' . str_replace(['[', ']', ' '], ['-', '', '-'], $name);
        $checked_attr = $checked ? ' checked' : '';

        return sprintf(
            '<div class="tb4-field-control tb4-toggle-control">
                <label class="tb4-toggle-label">
                    <input type="hidden" name="%s" value="0">
                    <input type="checkbox" id="%s" name="%s" value="1"%s class="tb4-toggle-input">
                    <span class="tb4-toggle-switch"></span>
                    <span class="tb4-toggle-text">%s</span>
                </label>
            </div>',
            $this->esc_attr($name),
            $this->esc_attr($id),
            $this->esc_attr($name),
            $checked_attr,
            $this->esc_attr($label)
        );
    }

    /**
     * Render a range input with number display
     *
     * @param string $name Input name
     * @param string $value Current value
     * @param string $label Label text
     * @param string $unit Unit label
     * @param int $min Minimum value
     * @param int $max Maximum value
     * @param int $step Step value
     * @return string HTML
     */
    protected function render_range_input(
        string $name,
        string $value,
        string $label,
        string $unit,
        int $min = 0,
        int $max = 2000,
        int $step = 10
    ): string {
        $id = 'tb4-' . str_replace(['[', ']', ' '], ['-', '', '-'], $name);

        return sprintf(
            '<div class="tb4-field-control tb4-range-control">
                <label for="%s">%s</label>
                <div class="tb4-range-input-wrapper">
                    <input type="range"
                           id="%s-range"
                           min="%d"
                           max="%d"
                           step="%d"
                           value="%s"
                           class="tb4-range-slider"
                           data-target="%s">
                    <div class="tb4-range-number-wrapper">
                        <input type="number"
                               id="%s"
                               name="%s"
                               value="%s"
                               min="%d"
                               max="%d"
                               step="%d"
                               class="tb4-input tb4-input-small">
                        <span class="tb4-unit-label">%s</span>
                    </div>
                </div>
            </div>',
            $this->esc_attr($id),
            $this->esc_attr($label),
            $this->esc_attr($id),
            $min,
            $max,
            $step,
            $this->esc_attr($value),
            $this->esc_attr($id),
            $this->esc_attr($id),
            $this->esc_attr($name),
            $this->esc_attr($value),
            $min,
            $max,
            $step,
            $this->esc_attr($unit)
        );
    }

    /**
     * Render cubic bezier inputs
     *
     * @param string $prefix Input name prefix
     * @param array $values Current bezier values
     * @return string HTML
     */
    protected function render_cubic_bezier_inputs(string $prefix, array $values): string
    {
        $html = '<div class="tb4-cubic-bezier-grid">';
        $html .= '<div class="tb4-bezier-label">cubic-bezier(</div>';

        $params = ['x1', 'y1', 'x2', 'y2'];
        foreach ($params as $index => $param) {
            $id = "tb4-{$prefix}-bezier-{$param}";
            $value = $values[$param] ?? '0';
            $min = ($param === 'y1' || $param === 'y2') ? '-2' : '0';
            $max = ($param === 'y1' || $param === 'y2') ? '2' : '1';

            $html .= sprintf(
                '<div class="tb4-bezier-input">
                    <input type="number"
                           id="%s"
                           name="%s[custom_bezier][%s]"
                           value="%s"
                           min="%s"
                           max="%s"
                           step="0.01"
                           class="tb4-input tb4-input-tiny"
                           placeholder="%s">
                </div>',
                $this->esc_attr($id),
                $this->esc_attr($prefix),
                $param,
                $this->esc_attr($value),
                $min,
                $max,
                $param
            );

            if ($index < 3) {
                $html .= '<span class="tb4-bezier-comma">,</span>';
            }
        }

        $html .= '<div class="tb4-bezier-label">)</div>';
        $html .= '</div>';

        // Common presets
        $html .= '<div class="tb4-bezier-presets">';
        $html .= '<label>Presets:</label>';
        $html .= '<div class="tb4-bezier-preset-buttons">';

        $presets = [
            'ease-in-quad'     => ['0.55', '0.085', '0.68', '0.53'],
            'ease-out-quad'    => ['0.25', '0.46', '0.45', '0.94'],
            'ease-in-out-quad' => ['0.455', '0.03', '0.515', '0.955'],
            'ease-in-cubic'    => ['0.55', '0.055', '0.675', '0.19'],
            'ease-out-cubic'   => ['0.215', '0.61', '0.355', '1'],
            'ease-in-back'     => ['0.6', '-0.28', '0.735', '0.045'],
            'ease-out-back'    => ['0.175', '0.885', '0.32', '1.275'],
            'ease-in-out-back' => ['0.68', '-0.55', '0.265', '1.55'],
        ];

        foreach ($presets as $preset_name => $preset_values) {
            $html .= sprintf(
                '<button type="button"
                         class="tb4-bezier-preset-btn"
                         data-preset="%s"
                         data-x1="%s"
                         data-y1="%s"
                         data-x2="%s"
                         data-y2="%s"
                         title="%s">%s</button>',
                $this->esc_attr($preset_name),
                $this->esc_attr($preset_values[0]),
                $this->esc_attr($preset_values[1]),
                $this->esc_attr($preset_values[2]),
                $this->esc_attr($preset_values[3]),
                $this->esc_attr("cubic-bezier({$preset_values[0]}, {$preset_values[1]}, {$preset_values[2]}, {$preset_values[3]})"),
                $this->esc_attr(str_replace('-', ' ', ucwords($preset_name, '-')))
            );
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Generate CSS from transition values
     *
     * @param string $selector CSS selector
     * @param array $values Field values
     * @return string CSS
     */
    public function generate_css(string $selector, array $values): string
    {
        $values = $this->merge_defaults($values);

        // If transition is disabled, return empty
        if (empty($values['enabled']) || $values['enabled'] === '0') {
            return '';
        }

        // If properties is 'none', return empty
        $properties = $this->sanitize_css_value($values['properties']);
        if ($properties === 'none') {
            return '';
        }

        // Sanitize values
        $duration = $this->sanitize_css_value($values['duration']);
        $delay = $this->sanitize_css_value($values['delay']);
        $easing = $this->sanitize_css_value($values['easing']);

        // Build easing value
        if ($easing === 'cubic-bezier') {
            $bezier = $values['custom_bezier'] ?? [];
            $x1 = $this->sanitize_bezier_value($bezier['x1'] ?? '0.25');
            $y1 = $this->sanitize_bezier_value($bezier['y1'] ?? '0.1');
            $x2 = $this->sanitize_bezier_value($bezier['x2'] ?? '0.25');
            $y2 = $this->sanitize_bezier_value($bezier['y2'] ?? '1.0');
            $easing_value = "cubic-bezier({$x1}, {$y1}, {$x2}, {$y2})";
        } else {
            $easing_value = $easing;
        }

        // Handle custom property
        if ($properties === 'custom') {
            $custom_property = $this->sanitize_css_value($values['custom_property'] ?? '');
            if (empty($custom_property)) {
                $properties = 'all';
            } else {
                $properties = $custom_property;
            }
        }

        // Build transition value
        // Format: property duration timing-function delay
        $delay_value = $delay !== '0' ? " {$delay}ms" : '';
        $transition_value = "{$properties} {$duration}ms {$easing_value}{$delay_value}";

        // Generate CSS
        $css = "{$selector} {\n";
        $css .= "    transition: {$transition_value};\n";
        $css .= "    -webkit-transition: {$transition_value};\n";
        $css .= "}\n";

        return $css;
    }

    /**
     * Sanitize a cubic bezier value
     *
     * @param string $value Value to sanitize
     * @return string Sanitized numeric value
     */
    protected function sanitize_bezier_value(string $value): string
    {
        $value = trim($value);

        // Must be a valid number
        if (!is_numeric($value)) {
            return '0';
        }

        $float_value = (float) $value;

        return (string) round($float_value, 3);
    }

    /**
     * Get transition shorthand for inline style
     *
     * @param array $values Field values
     * @return string CSS transition value (without property name)
     */
    public function get_inline_value(array $values): string
    {
        $values = $this->merge_defaults($values);

        if (empty($values['enabled']) || $values['enabled'] === '0') {
            return '';
        }

        $properties = $this->sanitize_css_value($values['properties']);
        if ($properties === 'none') {
            return 'none';
        }

        $duration = $this->sanitize_css_value($values['duration']);
        $delay = $this->sanitize_css_value($values['delay']);
        $easing = $this->sanitize_css_value($values['easing']);

        if ($easing === 'cubic-bezier') {
            $bezier = $values['custom_bezier'] ?? [];
            $x1 = $this->sanitize_bezier_value($bezier['x1'] ?? '0.25');
            $y1 = $this->sanitize_bezier_value($bezier['y1'] ?? '0.1');
            $x2 = $this->sanitize_bezier_value($bezier['x2'] ?? '0.25');
            $y2 = $this->sanitize_bezier_value($bezier['y2'] ?? '1.0');
            $easing = "cubic-bezier({$x1}, {$y1}, {$x2}, {$y2})";
        }

        if ($properties === 'custom') {
            $properties = $this->sanitize_css_value($values['custom_property'] ?? '') ?: 'all';
        }

        $delay_part = $delay !== '0' ? " {$delay}ms" : '';

        return "{$properties} {$duration}ms {$easing}{$delay_part}";
    }

    /**
     * Get data attributes for JavaScript interaction
     *
     * @param array $values Field values
     * @return string HTML data attributes
     */
    public function get_data_attributes(array $values): string
    {
        $values = $this->merge_defaults($values);

        if (empty($values['enabled']) || $values['enabled'] === '0') {
            return '';
        }

        $attrs = [];
        $attrs[] = sprintf('data-tb4-transition="true"');
        $attrs[] = sprintf('data-tb4-duration="%s"', $this->esc_attr($values['duration']));
        $attrs[] = sprintf('data-tb4-delay="%s"', $this->esc_attr($values['delay']));
        $attrs[] = sprintf('data-tb4-easing="%s"', $this->esc_attr($values['easing']));

        return implode(' ', $attrs);
    }

    /**
     * Get easing options
     *
     * @return array
     */
    public function get_easing_options(): array
    {
        return $this->easing_options;
    }

    /**
     * Get property options
     *
     * @return array
     */
    public function get_property_options(): array
    {
        return $this->property_options;
    }

    /**
     * Get common cubic bezier presets
     *
     * @return array
     */
    public function get_bezier_presets(): array
    {
        return [
            'ease-in-quad'     => [0.55, 0.085, 0.68, 0.53],
            'ease-out-quad'    => [0.25, 0.46, 0.45, 0.94],
            'ease-in-out-quad' => [0.455, 0.03, 0.515, 0.955],
            'ease-in-cubic'    => [0.55, 0.055, 0.675, 0.19],
            'ease-out-cubic'   => [0.215, 0.61, 0.355, 1],
            'ease-in-quart'    => [0.895, 0.03, 0.685, 0.22],
            'ease-out-quart'   => [0.165, 0.84, 0.44, 1],
            'ease-in-quint'    => [0.755, 0.05, 0.855, 0.06],
            'ease-out-quint'   => [0.23, 1, 0.32, 1],
            'ease-in-expo'     => [0.95, 0.05, 0.795, 0.035],
            'ease-out-expo'    => [0.19, 1, 0.22, 1],
            'ease-in-circ'     => [0.6, 0.04, 0.98, 0.335],
            'ease-out-circ'    => [0.075, 0.82, 0.165, 1],
            'ease-in-back'     => [0.6, -0.28, 0.735, 0.045],
            'ease-out-back'    => [0.175, 0.885, 0.32, 1.275],
            'ease-in-out-back' => [0.68, -0.55, 0.265, 1.55],
        ];
    }
}
