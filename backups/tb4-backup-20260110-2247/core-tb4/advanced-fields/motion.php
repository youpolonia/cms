<?php
/**
 * TB4 Motion Advanced Field
 *
 * Handles scroll-triggered motion effects including parallax, fade, scale,
 * rotate, and blur animations based on viewport position
 *
 * @package TB4\AdvancedFields
 * @since 1.0.0
 */

namespace TB4\AdvancedFields;

require_once __DIR__ . '/advancedfield.php';

class Motion extends AdvancedField
{
    /**
     * Motion effect configurations
     * @var array
     */
    protected array $effect_config = [
        'vertical' => [
            'label'       => 'Vertical Parallax',
            'icon'        => '↕',
            'description' => 'Move element vertically as user scrolls',
            'start_unit'  => 'px',
            'end_unit'    => 'px',
            'start_range' => [-500, 500],
            'end_range'   => [-500, 500],
        ],
        'horizontal' => [
            'label'       => 'Horizontal Parallax',
            'icon'        => '↔',
            'description' => 'Move element horizontally as user scrolls',
            'start_unit'  => 'px',
            'end_unit'    => 'px',
            'start_range' => [-500, 500],
            'end_range'   => [-500, 500],
        ],
        'fade' => [
            'label'       => 'Fade',
            'icon'        => '◐',
            'description' => 'Fade element opacity during scroll',
            'start_unit'  => '%',
            'end_unit'    => '%',
            'start_range' => [0, 100],
            'end_range'   => [0, 100],
        ],
        'scale' => [
            'label'       => 'Scale',
            'icon'        => '⊕',
            'description' => 'Scale element size during scroll',
            'start_unit'  => '',
            'end_unit'    => '',
            'start_range' => [0, 2],
            'end_range'   => [0, 2],
        ],
        'rotate' => [
            'label'       => 'Rotate',
            'icon'        => '↻',
            'description' => 'Rotate element during scroll',
            'start_unit'  => 'deg',
            'end_unit'    => 'deg',
            'start_range' => [-360, 360],
            'end_range'   => [-360, 360],
        ],
        'blur' => [
            'label'       => 'Blur',
            'icon'        => '◌',
            'description' => 'Apply blur effect during scroll',
            'start_unit'  => 'px',
            'end_unit'    => 'px',
            'start_range' => [0, 50],
            'end_range'   => [0, 50],
        ],
    ];

    /**
     * Viewport position options
     * @var array
     */
    protected array $viewport_options = [
        'bottom' => 'Bottom of Viewport',
        'middle' => 'Middle of Viewport',
        'top'    => 'Top of Viewport',
    ];

    /**
     * Get default motion values
     *
     * @return array
     */
    public function get_defaults(): array
    {
        return [
            'enabled' => false,
            'effects' => [
                'vertical' => [
                    'enabled'        => false,
                    'start'          => '0',
                    'end'            => '0',
                    'viewport_start' => 'bottom',
                    'viewport_end'   => 'top',
                ],
                'horizontal' => [
                    'enabled'        => false,
                    'start'          => '0',
                    'end'            => '0',
                    'viewport_start' => 'bottom',
                    'viewport_end'   => 'top',
                ],
                'fade' => [
                    'enabled'        => false,
                    'start'          => '0',
                    'end'            => '100',
                    'viewport_start' => 'bottom',
                    'viewport_end'   => 'top',
                ],
                'scale' => [
                    'enabled'        => false,
                    'start'          => '0.5',
                    'end'            => '1',
                    'viewport_start' => 'bottom',
                    'viewport_end'   => 'top',
                ],
                'rotate' => [
                    'enabled'        => false,
                    'start'          => '0',
                    'end'            => '0',
                    'viewport_start' => 'bottom',
                    'viewport_end'   => 'top',
                ],
                'blur' => [
                    'enabled'        => false,
                    'start'          => '10',
                    'end'            => '0',
                    'viewport_start' => 'bottom',
                    'viewport_end'   => 'top',
                ],
            ],
        ];
    }

    /**
     * Render motion control panel
     *
     * @param string $prefix Input name prefix
     * @param array $values Current values
     * @return string HTML
     */
    public function render_controls(string $prefix, array $values): string
    {
        $values = $this->merge_defaults($values);
        $enabled = !empty($values['enabled']);
        $enabled_checked = $enabled ? ' checked' : '';

        $html = '<div class="tb4-motion-panel tb4-advanced-panel">';
        $html .= '<div class="tb4-panel-header">Scroll Motion Effects</div>';
        $html .= '<div class="tb4-panel-content">';

        // Master enable toggle
        $html .= '<div class="tb4-section tb4-motion-master">';
        $html .= '<div class="tb4-field-control tb4-toggle-control">';
        $html .= sprintf(
            '<label class="tb4-toggle">
                <input type="checkbox" name="%s[enabled]" value="1"%s class="tb4-motion-master-toggle">
                <span class="tb4-toggle-slider"></span>
                <span class="tb4-toggle-label">Enable Scroll Motion</span>
            </label>',
            $this->esc_attr($prefix),
            $enabled_checked
        );
        $html .= '<p class="tb4-field-description">Animate element properties based on scroll position</p>';
        $html .= '</div>';
        $html .= '</div>';

        // Visual scroll progress diagram
        $html .= $this->render_scroll_diagram();

        // Effects accordion
        $html .= '<div class="tb4-motion-effects-container"' . ($enabled ? '' : ' style="display:none;"') . '>';
        $html .= '<div class="tb4-accordion">';

        foreach ($this->effect_config as $effect_key => $config) {
            $effect_values = $values['effects'][$effect_key] ?? [];
            $html .= $this->render_effect_accordion(
                $prefix,
                $effect_key,
                $config,
                $effect_values
            );
        }

        $html .= '</div>'; // accordion
        $html .= '</div>'; // effects-container

        $html .= '</div>'; // panel-content
        $html .= '</div>'; // panel

        return $html;
    }

    /**
     * Render scroll progress diagram
     *
     * @return string HTML
     */
    protected function render_scroll_diagram(): string
    {
        $html = '<div class="tb4-motion-diagram">';
        $html .= '<div class="tb4-diagram-header">Scroll Progress Reference</div>';
        $html .= '<div class="tb4-diagram-content">';
        $html .= '<div class="tb4-viewport-diagram">';
        $html .= '<div class="tb4-viewport-frame">';
        $html .= '<span class="tb4-viewport-label tb4-viewport-top">Top</span>';
        $html .= '<span class="tb4-viewport-label tb4-viewport-middle">Middle</span>';
        $html .= '<span class="tb4-viewport-label tb4-viewport-bottom">Bottom</span>';
        $html .= '<div class="tb4-element-indicator"></div>';
        $html .= '</div>';
        $html .= '<div class="tb4-scroll-arrow">↓ Scroll Direction</div>';
        $html .= '</div>';
        $html .= '<p class="tb4-diagram-description">';
        $html .= 'Effects transition from <strong>Start</strong> to <strong>End</strong> values ';
        $html .= 'as the element moves from <strong>Viewport Start</strong> to <strong>Viewport End</strong> position.';
        $html .= '</p>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render individual effect accordion panel
     *
     * @param string $prefix Input name prefix
     * @param string $effect_key Effect identifier
     * @param array $config Effect configuration
     * @param array $values Current effect values
     * @return string HTML
     */
    protected function render_effect_accordion(
        string $prefix,
        string $effect_key,
        array $config,
        array $values
    ): string {
        $effect_enabled = !empty($values['enabled']);
        $enabled_checked = $effect_enabled ? ' checked' : '';
        $expanded_class = $effect_enabled ? ' tb4-accordion-expanded' : '';
        $effect_prefix = "{$prefix}[effects][{$effect_key}]";

        $html = '<div class="tb4-accordion-item' . $expanded_class . '" data-effect="' . $this->esc_attr($effect_key) . '">';

        // Accordion header
        $html .= '<div class="tb4-accordion-header">';
        $html .= sprintf(
            '<span class="tb4-effect-icon">%s</span>
            <span class="tb4-effect-label">%s</span>
            <label class="tb4-toggle tb4-toggle-small">
                <input type="checkbox" name="%s[enabled]" value="1"%s class="tb4-effect-toggle">
                <span class="tb4-toggle-slider"></span>
            </label>
            <span class="tb4-accordion-arrow">▼</span>',
            $this->esc_attr($config['icon']),
            $this->esc_attr($config['label']),
            $this->esc_attr($effect_prefix),
            $enabled_checked
        );
        $html .= '</div>';

        // Accordion content
        $html .= '<div class="tb4-accordion-content"' . ($effect_enabled ? '' : ' style="display:none;"') . '>';
        $html .= '<p class="tb4-effect-description">' . $this->esc_attr($config['description']) . '</p>';

        // Start/End value controls
        $html .= '<div class="tb4-motion-values">';

        // Start value
        $html .= $this->render_value_control(
            $effect_prefix,
            'start',
            'Start Value',
            $values['start'] ?? '0',
            $config['start_unit'],
            $config['start_range']
        );

        // End value
        $html .= $this->render_value_control(
            $effect_prefix,
            'end',
            'End Value',
            $values['end'] ?? '0',
            $config['end_unit'],
            $config['end_range']
        );

        $html .= '</div>'; // motion-values

        // Viewport position controls
        $html .= '<div class="tb4-viewport-positions">';
        $html .= '<div class="tb4-viewport-row">';

        // Viewport start
        $html .= $this->render_viewport_select(
            $effect_prefix,
            'viewport_start',
            'Animation Starts When',
            $values['viewport_start'] ?? 'bottom'
        );

        // Viewport end
        $html .= $this->render_viewport_select(
            $effect_prefix,
            'viewport_end',
            'Animation Ends When',
            $values['viewport_end'] ?? 'top'
        );

        $html .= '</div>'; // viewport-row
        $html .= '</div>'; // viewport-positions

        $html .= '</div>'; // accordion-content
        $html .= '</div>'; // accordion-item

        return $html;
    }

    /**
     * Render value control with range slider
     *
     * @param string $prefix Input name prefix
     * @param string $key Value key (start/end)
     * @param string $label Label text
     * @param string $value Current value
     * @param string $unit Unit label
     * @param array $range [min, max] range
     * @return string HTML
     */
    protected function render_value_control(
        string $prefix,
        string $key,
        string $label,
        string $value,
        string $unit,
        array $range
    ): string {
        $name = "{$prefix}[{$key}]";
        $id = 'tb4-' . str_replace(['[', ']', ' '], ['-', '', '-'], $name);
        $unit_label = $unit ?: '×';
        $step = $unit === '' ? '0.05' : '1';

        return sprintf(
            '<div class="tb4-field-control tb4-motion-value-control">
                <label for="%s">%s <span class="tb4-unit">(%s)</span></label>
                <div class="tb4-input-wrapper">
                    <input type="range"
                           id="%s-range"
                           min="%s"
                           max="%s"
                           step="%s"
                           value="%s"
                           class="tb4-range tb4-motion-range"
                           data-target="%s">
                    <input type="text"
                           id="%s"
                           name="%s"
                           value="%s"
                           class="tb4-input tb4-input-small tb4-motion-value">
                    <span class="tb4-unit-label">%s</span>
                </div>
            </div>',
            $this->esc_attr($id),
            $this->esc_attr($label),
            $this->esc_attr($unit_label),
            $this->esc_attr($id),
            $this->esc_attr((string)$range[0]),
            $this->esc_attr((string)$range[1]),
            $this->esc_attr($step),
            $this->esc_attr($value),
            $this->esc_attr($id),
            $this->esc_attr($id),
            $this->esc_attr($name),
            $this->esc_attr($value),
            $this->esc_attr($unit_label)
        );
    }

    /**
     * Render viewport position select
     *
     * @param string $prefix Input name prefix
     * @param string $key Value key (viewport_start/viewport_end)
     * @param string $label Label text
     * @param string $value Current value
     * @return string HTML
     */
    protected function render_viewport_select(
        string $prefix,
        string $key,
        string $label,
        string $value
    ): string {
        $name = "{$prefix}[{$key}]";
        $id = 'tb4-' . str_replace(['[', ']', ' '], ['-', '', '-'], $name);

        $options_html = '';
        foreach ($this->viewport_options as $opt_value => $opt_label) {
            $selected = $value === $opt_value ? ' selected' : '';
            $options_html .= sprintf(
                '<option value="%s"%s>%s</option>',
                $this->esc_attr($opt_value),
                $selected,
                $this->esc_attr($opt_label)
            );
        }

        return sprintf(
            '<div class="tb4-field-control tb4-viewport-control">
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
     * Generate CSS for initial state
     *
     * @param string $selector CSS selector
     * @param array $values Field values
     * @return string CSS
     */
    public function generate_css(string $selector, array $values): string
    {
        $values = $this->merge_defaults($values);

        if (empty($values['enabled'])) {
            return '';
        }

        $effects = $values['effects'] ?? [];
        $transforms = [];
        $opacity = null;
        $filter = null;

        // Build initial state from start values
        foreach ($effects as $effect_key => $effect) {
            if (empty($effect['enabled'])) {
                continue;
            }

            $start = $this->sanitize_css_value($effect['start'] ?? '0');
            if ($start === '') {
                continue;
            }

            switch ($effect_key) {
                case 'vertical':
                    if ($start !== '0') {
                        $transforms[] = sprintf('translateY(%s)', $this->css_unit($start, 'px'));
                    }
                    break;

                case 'horizontal':
                    if ($start !== '0') {
                        $transforms[] = sprintf('translateX(%s)', $this->css_unit($start, 'px'));
                    }
                    break;

                case 'fade':
                    $opacity_val = floatval($start) / 100;
                    if ($opacity_val !== 1.0) {
                        $opacity = $opacity_val;
                    }
                    break;

                case 'scale':
                    if ($start !== '1') {
                        $transforms[] = sprintf('scale(%s)', $start);
                    }
                    break;

                case 'rotate':
                    if ($start !== '0') {
                        $transforms[] = sprintf('rotate(%sdeg)', $start);
                    }
                    break;

                case 'blur':
                    if ($start !== '0') {
                        $filter = sprintf('blur(%spx)', $start);
                    }
                    break;
            }
        }

        // Generate CSS only if there are styles to apply
        if (empty($transforms) && $opacity === null && $filter === null) {
            return '';
        }

        $css = "{$selector} {\n";
        $css .= "    will-change: transform, opacity, filter;\n";

        if (!empty($transforms)) {
            $css .= sprintf("    transform: %s;\n", implode(' ', $transforms));
        }

        if ($opacity !== null) {
            $css .= sprintf("    opacity: %s;\n", $opacity);
        }

        if ($filter !== null) {
            $css .= sprintf("    filter: %s;\n", $filter);
        }

        $css .= "}\n";

        return $css;
    }

    /**
     * Generate data attributes for JavaScript scroll handler
     *
     * @param array $values Field values
     * @return string Data attributes string
     */
    public function generate_data_attributes(array $values): string
    {
        $values = $this->merge_defaults($values);

        if (empty($values['enabled'])) {
            return '';
        }

        $effects = $values['effects'] ?? [];
        $attributes = ['data-tb4-motion="true"'];

        foreach ($effects as $effect_key => $effect) {
            if (empty($effect['enabled'])) {
                continue;
            }

            $effect_data = [
                'start'         => $this->sanitize_css_value($effect['start'] ?? '0'),
                'end'           => $this->sanitize_css_value($effect['end'] ?? '0'),
                'viewportStart' => $effect['viewport_start'] ?? 'bottom',
                'viewportEnd'   => $effect['viewport_end'] ?? 'top',
            ];

            $json = json_encode($effect_data, JSON_UNESCAPED_SLASHES);
            $attributes[] = sprintf(
                'data-tb4-motion-%s=\'%s\'',
                $this->esc_attr($effect_key),
                $json
            );
        }

        return implode(' ', $attributes);
    }

    /**
     * Check if motion is enabled
     *
     * @param array $values Field values
     * @return bool
     */
    public function is_enabled(array $values): bool
    {
        $values = $this->merge_defaults($values);
        return !empty($values['enabled']);
    }

    /**
     * Get enabled effects list
     *
     * @param array $values Field values
     * @return array List of enabled effect keys
     */
    public function get_enabled_effects(array $values): array
    {
        $values = $this->merge_defaults($values);
        $enabled = [];

        if (empty($values['enabled'])) {
            return $enabled;
        }

        foreach ($values['effects'] ?? [] as $effect_key => $effect) {
            if (!empty($effect['enabled'])) {
                $enabled[] = $effect_key;
            }
        }

        return $enabled;
    }

    /**
     * Get effect configuration
     *
     * @return array
     */
    public function get_effect_config(): array
    {
        return $this->effect_config;
    }

    /**
     * Get viewport options
     *
     * @return array
     */
    public function get_viewport_options(): array
    {
        return $this->viewport_options;
    }

    /**
     * Validate effect values
     *
     * @param array $values Raw values
     * @return array Validated values
     */
    public function validate(array $values): array
    {
        $validated = $this->get_defaults();
        $validated['enabled'] = !empty($values['enabled']);

        foreach ($this->effect_config as $effect_key => $config) {
            if (!isset($values['effects'][$effect_key])) {
                continue;
            }

            $effect = $values['effects'][$effect_key];
            $validated['effects'][$effect_key]['enabled'] = !empty($effect['enabled']);

            // Validate start value within range
            if (isset($effect['start'])) {
                $start = floatval($effect['start']);
                $min = $config['start_range'][0];
                $max = $config['start_range'][1];
                $validated['effects'][$effect_key]['start'] = (string)max($min, min($max, $start));
            }

            // Validate end value within range
            if (isset($effect['end'])) {
                $end = floatval($effect['end']);
                $min = $config['end_range'][0];
                $max = $config['end_range'][1];
                $validated['effects'][$effect_key]['end'] = (string)max($min, min($max, $end));
            }

            // Validate viewport positions
            if (isset($effect['viewport_start']) && isset($this->viewport_options[$effect['viewport_start']])) {
                $validated['effects'][$effect_key]['viewport_start'] = $effect['viewport_start'];
            }

            if (isset($effect['viewport_end']) && isset($this->viewport_options[$effect['viewport_end']])) {
                $validated['effects'][$effect_key]['viewport_end'] = $effect['viewport_end'];
            }
        }

        return $validated;
    }
}
