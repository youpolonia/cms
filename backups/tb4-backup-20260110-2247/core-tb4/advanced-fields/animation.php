<?php
/**
 * TB4 Animation Advanced Field
 *
 * Handles CSS animations including fade, slide, zoom, bounce, flip, rotate, and pulse
 * with configurable timing, easing, iteration, and trigger options
 *
 * @package TB4\AdvancedFields
 * @since 1.0.0
 */

namespace TB4\AdvancedFields;

require_once __DIR__ . '/advancedfield.php';

class Animation extends AdvancedField
{
    /**
     * Animation type options
     * @var array
     */
    protected array $animation_types = [
        ''            => 'None',
        'fade'        => 'Fade In',
        'fade-out'    => 'Fade Out',
        'slide-up'    => 'Slide Up',
        'slide-down'  => 'Slide Down',
        'slide-left'  => 'Slide Left',
        'slide-right' => 'Slide Right',
        'zoom-in'     => 'Zoom In',
        'zoom-out'    => 'Zoom Out',
        'bounce'      => 'Bounce',
        'flip'        => 'Flip',
        'rotate'      => 'Rotate',
        'pulse'       => 'Pulse',
    ];

    /**
     * Easing function options
     * @var array
     */
    protected array $easing_options = [
        'ease'        => 'Ease',
        'ease-in'     => 'Ease In',
        'ease-out'    => 'Ease Out',
        'ease-in-out' => 'Ease In Out',
        'linear'      => 'Linear',
    ];

    /**
     * Iteration count options
     * @var array
     */
    protected array $iteration_options = [
        '1'        => '1',
        '2'        => '2',
        '3'        => '3',
        'infinite' => 'Infinite',
    ];

    /**
     * Animation direction options
     * @var array
     */
    protected array $direction_options = [
        'normal'            => 'Normal',
        'reverse'           => 'Reverse',
        'alternate'         => 'Alternate',
        'alternate-reverse' => 'Alternate Reverse',
    ];

    /**
     * Fill mode options
     * @var array
     */
    protected array $fill_mode_options = [
        'none'      => 'None',
        'forwards'  => 'Forwards',
        'backwards' => 'Backwards',
        'both'      => 'Both',
    ];

    /**
     * Trigger options
     * @var array
     */
    protected array $trigger_options = [
        'load'   => 'On Page Load',
        'scroll' => 'On Scroll Into View',
        'hover'  => 'On Hover',
    ];

    /**
     * Get default animation values
     *
     * @return array
     */
    public function get_defaults(): array
    {
        return [
            'type'          => '',
            'duration'      => '400',
            'delay'         => '0',
            'easing'        => 'ease',
            'iteration'     => '1',
            'direction'     => 'normal',
            'fill_mode'     => 'forwards',
            'trigger'       => 'load',
            'scroll_offset' => '100',
        ];
    }

    /**
     * Render animation control panel
     *
     * @param string $prefix Input name prefix
     * @param array $values Current values
     * @return string HTML
     */
    public function render_controls(string $prefix, array $values): string
    {
        $values = $this->merge_defaults($values);

        $html = '<div class="tb4-animation-panel tb4-advanced-panel">';
        $html .= '<div class="tb4-panel-header">Animation</div>';
        $html .= '<div class="tb4-panel-content">';

        // Animation Type
        $html .= $this->render_select(
            "{$prefix}[type]",
            $values['type'],
            'Animation Type',
            $this->animation_types
        );

        // Timing Section
        $html .= '<div class="tb4-section">';
        $html .= '<div class="tb4-section-header">Timing</div>';
        $html .= '<div class="tb4-section-content">';

        // Duration
        $html .= $this->render_number_input(
            "{$prefix}[duration]",
            $values['duration'],
            'Duration',
            'ms',
            0,
            10000,
            50
        );

        // Delay
        $html .= $this->render_number_input(
            "{$prefix}[delay]",
            $values['delay'],
            'Delay',
            'ms',
            0,
            10000,
            50
        );

        // Easing
        $html .= $this->render_select(
            "{$prefix}[easing]",
            $values['easing'],
            'Easing',
            $this->easing_options
        );

        $html .= '</div>'; // section-content
        $html .= '</div>'; // section

        // Behavior Section
        $html .= '<div class="tb4-section">';
        $html .= '<div class="tb4-section-header">Behavior</div>';
        $html .= '<div class="tb4-section-content">';

        // Iteration
        $html .= $this->render_select(
            "{$prefix}[iteration]",
            $values['iteration'],
            'Iteration Count',
            $this->iteration_options
        );

        // Direction
        $html .= $this->render_select(
            "{$prefix}[direction]",
            $values['direction'],
            'Direction',
            $this->direction_options
        );

        // Fill Mode
        $html .= $this->render_select(
            "{$prefix}[fill_mode]",
            $values['fill_mode'],
            'Fill Mode',
            $this->fill_mode_options
        );

        $html .= '</div>'; // section-content
        $html .= '</div>'; // section

        // Trigger Section
        $html .= '<div class="tb4-section">';
        $html .= '<div class="tb4-section-header">Trigger</div>';
        $html .= '<div class="tb4-section-content">';

        // Trigger Type
        $html .= $this->render_select(
            "{$prefix}[trigger]",
            $values['trigger'],
            'Trigger',
            $this->trigger_options
        );

        // Scroll Offset (conditional visibility via data attribute)
        $scroll_visible = $values['trigger'] === 'scroll' ? '' : ' style="display:none;"';
        $html .= sprintf(
            '<div class="tb4-field-control tb4-scroll-offset-field" data-show-when-trigger="scroll"%s>',
            $scroll_visible
        );
        $html .= $this->render_number_input(
            "{$prefix}[scroll_offset]",
            $values['scroll_offset'],
            'Scroll Offset',
            'px',
            0,
            1000,
            10
        );
        $html .= '</div>';

        $html .= '</div>'; // section-content
        $html .= '</div>'; // section

        $html .= '</div>'; // panel-content
        $html .= '</div>'; // panel

        return $html;
    }

    /**
     * Render a number input with unit
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
    protected function render_number_input(
        string $name,
        string $value,
        string $label,
        string $unit,
        int $min = 0,
        int $max = 10000,
        int $step = 1
    ): string {
        $id = 'tb4-' . str_replace(['[', ']', ' '], ['-', '', '-'], $name);

        return sprintf(
            '<div class="tb4-field-control tb4-number-control">
                <label for="%s">%s</label>
                <div class="tb4-number-input-wrapper">
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
            </div>',
            $this->esc_attr($id),
            $this->esc_attr($label),
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
     * Generate CSS from animation values
     *
     * @param string $selector CSS selector
     * @param array $values Field values
     * @return string CSS
     */
    public function generate_css(string $selector, array $values): string
    {
        $values = $this->merge_defaults($values);
        $css = '';

        // If no animation type selected, return empty
        $type = $this->sanitize_css_value($values['type']);
        if (empty($type)) {
            return '';
        }

        // Sanitize all values
        $duration = $this->sanitize_css_value($values['duration']);
        $delay = $this->sanitize_css_value($values['delay']);
        $easing = $this->sanitize_css_value($values['easing']);
        $iteration = $this->sanitize_css_value($values['iteration']);
        $direction = $this->sanitize_css_value($values['direction']);
        $fill_mode = $this->sanitize_css_value($values['fill_mode']);
        $trigger = $this->sanitize_css_value($values['trigger']);

        // Build animation shorthand
        $animation_name = 'tb4-' . $type;
        $animation_value = sprintf(
            '%s %sms %s %sms %s %s %s',
            $animation_name,
            $duration,
            $easing,
            $delay,
            $iteration,
            $direction,
            $fill_mode
        );

        // Generate CSS based on trigger type
        switch ($trigger) {
            case 'hover':
                // Animation only on hover
                $css .= "{$selector}:hover {\n";
                $css .= "    animation: {$animation_value};\n";
                $css .= "}\n";
                break;

            case 'scroll':
                // Initial hidden state, animation triggered by JS
                $css .= "{$selector} {\n";
                $css .= "    opacity: 0;\n";
                $css .= "}\n";
                $css .= "{$selector}.tb4-animated {\n";
                $css .= "    animation: {$animation_value};\n";
                $css .= "}\n";
                break;

            case 'load':
            default:
                // Animation on page load
                $css .= "{$selector} {\n";
                $css .= "    animation: {$animation_value};\n";
                $css .= "}\n";
                break;
        }

        return $css;
    }

    /**
     * Get data attributes for scroll trigger
     *
     * @param array $values Field values
     * @return string HTML data attributes
     */
    public function get_data_attributes(array $values): string
    {
        $values = $this->merge_defaults($values);

        if (empty($values['type'])) {
            return '';
        }

        $attrs = [];
        $attrs[] = sprintf('data-tb4-animation="%s"', $this->esc_attr($values['type']));

        if ($values['trigger'] === 'scroll') {
            $offset = $this->sanitize_css_value($values['scroll_offset']);
            $attrs[] = sprintf('data-tb4-offset="%s"', $this->esc_attr($offset));
        }

        return implode(' ', $attrs);
    }

    /**
     * Get all keyframes definitions
     *
     * @return string CSS keyframes
     */
    public function get_keyframes(): string
    {
        $keyframes = '';

        // Fade In
        $keyframes .= "@keyframes tb4-fade {\n";
        $keyframes .= "    from { opacity: 0; }\n";
        $keyframes .= "    to { opacity: 1; }\n";
        $keyframes .= "}\n\n";

        // Fade Out
        $keyframes .= "@keyframes tb4-fade-out {\n";
        $keyframes .= "    from { opacity: 1; }\n";
        $keyframes .= "    to { opacity: 0; }\n";
        $keyframes .= "}\n\n";

        // Slide Up
        $keyframes .= "@keyframes tb4-slide-up {\n";
        $keyframes .= "    from { opacity: 0; transform: translateY(30px); }\n";
        $keyframes .= "    to { opacity: 1; transform: translateY(0); }\n";
        $keyframes .= "}\n\n";

        // Slide Down
        $keyframes .= "@keyframes tb4-slide-down {\n";
        $keyframes .= "    from { opacity: 0; transform: translateY(-30px); }\n";
        $keyframes .= "    to { opacity: 1; transform: translateY(0); }\n";
        $keyframes .= "}\n\n";

        // Slide Left
        $keyframes .= "@keyframes tb4-slide-left {\n";
        $keyframes .= "    from { opacity: 0; transform: translateX(30px); }\n";
        $keyframes .= "    to { opacity: 1; transform: translateX(0); }\n";
        $keyframes .= "}\n\n";

        // Slide Right
        $keyframes .= "@keyframes tb4-slide-right {\n";
        $keyframes .= "    from { opacity: 0; transform: translateX(-30px); }\n";
        $keyframes .= "    to { opacity: 1; transform: translateX(0); }\n";
        $keyframes .= "}\n\n";

        // Zoom In
        $keyframes .= "@keyframes tb4-zoom-in {\n";
        $keyframes .= "    from { opacity: 0; transform: scale(0.8); }\n";
        $keyframes .= "    to { opacity: 1; transform: scale(1); }\n";
        $keyframes .= "}\n\n";

        // Zoom Out
        $keyframes .= "@keyframes tb4-zoom-out {\n";
        $keyframes .= "    from { opacity: 0; transform: scale(1.2); }\n";
        $keyframes .= "    to { opacity: 1; transform: scale(1); }\n";
        $keyframes .= "}\n\n";

        // Bounce
        $keyframes .= "@keyframes tb4-bounce {\n";
        $keyframes .= "    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }\n";
        $keyframes .= "    40% { transform: translateY(-20px); }\n";
        $keyframes .= "    60% { transform: translateY(-10px); }\n";
        $keyframes .= "}\n\n";

        // Flip
        $keyframes .= "@keyframes tb4-flip {\n";
        $keyframes .= "    from { opacity: 0; transform: perspective(400px) rotateY(90deg); }\n";
        $keyframes .= "    to { opacity: 1; transform: perspective(400px) rotateY(0); }\n";
        $keyframes .= "}\n\n";

        // Rotate
        $keyframes .= "@keyframes tb4-rotate {\n";
        $keyframes .= "    from { opacity: 0; transform: rotate(-180deg); }\n";
        $keyframes .= "    to { opacity: 1; transform: rotate(0); }\n";
        $keyframes .= "}\n\n";

        // Pulse
        $keyframes .= "@keyframes tb4-pulse {\n";
        $keyframes .= "    0% { transform: scale(1); }\n";
        $keyframes .= "    50% { transform: scale(1.05); }\n";
        $keyframes .= "    100% { transform: scale(1); }\n";
        $keyframes .= "}\n";

        return $keyframes;
    }

    /**
     * Get animation type options
     *
     * @return array
     */
    public function get_animation_types(): array
    {
        return $this->animation_types;
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
     * Get trigger options
     *
     * @return array
     */
    public function get_trigger_options(): array
    {
        return $this->trigger_options;
    }
}
