<?php
/**
 * TB4 Overlay Advanced Field
 *
 * Handles overlay settings including colors, gradients, blend modes, and hover states
 * Uses ::before pseudo-element for layering over content
 *
 * @package TB4\AdvancedFields
 * @since 1.0.0
 */

namespace TB4\AdvancedFields;

require_once __DIR__ . '/advancedfield.php';

class Overlay extends AdvancedField
{
    /**
     * Available overlay types
     * @var array
     */
    protected array $overlay_types = [
        'color'    => 'Solid Color',
        'gradient' => 'Gradient',
    ];

    /**
     * Available gradient types
     * @var array
     */
    protected array $gradient_types = [
        'linear' => 'Linear',
        'radial' => 'Radial',
    ];

    /**
     * Available gradient directions (for linear)
     * @var array
     */
    protected array $gradient_directions = [
        '0deg'   => 'To Top',
        '45deg'  => 'To Top Right',
        '90deg'  => 'To Right',
        '135deg' => 'To Bottom Right',
        '180deg' => 'To Bottom',
        '225deg' => 'To Bottom Left',
        '270deg' => 'To Left',
        '315deg' => 'To Top Left',
    ];

    /**
     * Available blend modes
     * @var array
     */
    protected array $blend_modes = [
        'normal'      => 'Normal',
        'multiply'    => 'Multiply',
        'screen'      => 'Screen',
        'overlay'     => 'Overlay',
        'darken'      => 'Darken',
        'lighten'     => 'Lighten',
        'color-dodge' => 'Color Dodge',
        'color-burn'  => 'Color Burn',
        'hard-light'  => 'Hard Light',
        'soft-light'  => 'Soft Light',
        'difference'  => 'Difference',
        'exclusion'   => 'Exclusion',
        'hue'         => 'Hue',
        'saturation'  => 'Saturation',
        'color'       => 'Color',
        'luminosity'  => 'Luminosity',
    ];

    /**
     * Get default overlay values
     *
     * @return array
     */
    public function get_defaults(): array
    {
        return [
            'enabled'       => false,
            'type'          => 'color',
            'color'         => 'rgba(0,0,0,0.5)',
            'gradient'      => [
                'type'        => 'linear',
                'direction'   => '180deg',
                'start_color' => 'rgba(0,0,0,0.8)',
                'end_color'   => 'rgba(0,0,0,0)',
            ],
            'blend_mode'    => 'normal',
            'hover_enabled' => false,
            'hover_color'   => '',
            'hover_opacity' => '',
        ];
    }

    /**
     * Render overlay control panel
     *
     * @param string $prefix Input name prefix
     * @param array $values Current values
     * @return string HTML
     */
    public function render_controls(string $prefix, array $values): string
    {
        $values = $this->merge_defaults($values);

        $html = '<div class="tb4-overlay-panel tb4-advanced-panel">';
        $html .= '<div class="tb4-panel-header">Overlay</div>';
        $html .= '<div class="tb4-panel-content">';

        // Enable Toggle
        $html .= $this->render_checkbox("{$prefix}[enabled]", (bool)$values['enabled'], 'Enable Overlay');

        // Overlay Type Selector
        $html .= $this->render_select(
            "{$prefix}[type]",
            $values['type'],
            'Overlay Type',
            $this->overlay_types
        );

        // Color Section
        $html .= $this->render_section_color($prefix, $values);

        // Gradient Section
        $html .= $this->render_section_gradient($prefix, $values);

        // Blend Mode (applies to both types)
        $html .= $this->render_select(
            "{$prefix}[blend_mode]",
            $values['blend_mode'],
            'Blend Mode',
            $this->blend_modes
        );

        // Hover Section
        $html .= $this->render_section_hover($prefix, $values);

        $html .= '</div>'; // panel-content
        $html .= '</div>'; // panel

        return $html;
    }

    /**
     * Render color section
     *
     * @param string $prefix Input prefix
     * @param array $values Current values
     * @return string HTML
     */
    protected function render_section_color(string $prefix, array $values): string
    {
        $display = $values['type'] === 'color' ? '' : 'style="display:none;"';

        $html = sprintf('<div class="tb4-overlay-section tb4-overlay-color-section" data-overlay-type="color" %s>', $display);
        $html .= $this->render_color_picker(
            "{$prefix}[color]",
            $values['color'],
            'Overlay Color'
        );
        $html .= '</div>';

        return $html;
    }

    /**
     * Render gradient section
     *
     * @param string $prefix Input prefix
     * @param array $values Current values
     * @return string HTML
     */
    protected function render_section_gradient(string $prefix, array $values): string
    {
        $display = $values['type'] === 'gradient' ? '' : 'style="display:none;"';
        $gradient = $values['gradient'];

        $html = sprintf('<div class="tb4-overlay-section tb4-overlay-gradient-section" data-overlay-type="gradient" %s>', $display);

        // Gradient Type & Direction Row
        $html .= '<div class="tb4-control-row">';
        $html .= $this->render_select(
            "{$prefix}[gradient][type]",
            $gradient['type'],
            'Gradient Type',
            $this->gradient_types
        );
        $html .= $this->render_select(
            "{$prefix}[gradient][direction]",
            $gradient['direction'],
            'Direction',
            $this->gradient_directions
        );
        $html .= '</div>';

        // Start Color
        $html .= $this->render_color_picker(
            "{$prefix}[gradient][start_color]",
            $gradient['start_color'],
            'Start Color'
        );

        // End Color
        $html .= $this->render_color_picker(
            "{$prefix}[gradient][end_color]",
            $gradient['end_color'],
            'End Color'
        );

        // Gradient Preview
        $html .= '<div class="tb4-gradient-preview tb4-overlay-gradient-preview"></div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Render hover section
     *
     * @param string $prefix Input prefix
     * @param array $values Current values
     * @return string HTML
     */
    protected function render_section_hover(string $prefix, array $values): string
    {
        $html = '<div class="tb4-overlay-section tb4-overlay-hover-section">';
        $html .= '<div class="tb4-section-header">Hover Effects</div>';

        $html .= $this->render_checkbox("{$prefix}[hover_enabled]", (bool)$values['hover_enabled'], 'Enable Hover Effect');

        // Hover Color Override
        $html .= $this->render_color_picker(
            "{$prefix}[hover_color]",
            $values['hover_color'],
            'Hover Color'
        );

        // Hover Opacity Override
        $html .= $this->render_text_input(
            "{$prefix}[hover_opacity]",
            $values['hover_opacity'],
            'Hover Opacity',
            '0 - 1 (e.g., 0.3)'
        );

        $html .= '</div>';

        return $html;
    }

    /**
     * Render checkbox control
     *
     * @param string $name Input name
     * @param bool $checked Whether checked
     * @param string $label Label text
     * @return string HTML
     */
    protected function render_checkbox(string $name, bool $checked, string $label): string
    {
        $id = 'tb4-' . str_replace(['[', ']', ' '], ['-', '', '-'], $name);
        $checked_attr = $checked ? ' checked' : '';

        return sprintf(
            '<label class="tb4-checkbox">
                <input type="hidden" name="%s" value="0">
                <input type="checkbox" id="%s" name="%s" value="1"%s>
                <span>%s</span>
            </label>',
            $this->esc_attr($name),
            $this->esc_attr($id),
            $this->esc_attr($name),
            $checked_attr,
            $this->esc_attr($label)
        );
    }

    /**
     * Generate CSS from overlay values
     *
     * @param string $selector CSS selector
     * @param array $values Field values
     * @return string CSS
     */
    public function generate_css(string $selector, array $values): string
    {
        $values = $this->merge_defaults($values);

        // If overlay is not enabled, return empty CSS
        if (empty($values['enabled'])) {
            return '';
        }

        $css = '';

        // Parent element needs position: relative
        $css .= "{$selector} {\n    position: relative;\n}\n";

        // Build overlay background
        $overlay_bg = $this->build_overlay_background($values);
        $blend_mode = $this->sanitize_css_value($values['blend_mode']);

        // Generate ::before pseudo-element styles
        $css .= "{$selector}::before {\n";
        $css .= "    content: '';\n";
        $css .= "    position: absolute;\n";
        $css .= "    top: 0;\n";
        $css .= "    left: 0;\n";
        $css .= "    right: 0;\n";
        $css .= "    bottom: 0;\n";
        $css .= "    background: {$overlay_bg};\n";

        if (!empty($blend_mode) && $blend_mode !== 'normal') {
            $css .= "    mix-blend-mode: {$blend_mode};\n";
        }

        $css .= "    pointer-events: none;\n";
        $css .= "    z-index: 1;\n";
        $css .= "}\n";

        // Hover state
        if (!empty($values['hover_enabled'])) {
            $hover_css = $this->build_hover_css($values);
            if (!empty($hover_css)) {
                $css .= "{$selector}:hover::before {\n";
                $css .= $hover_css;
                $css .= "}\n";
            }
        }

        return $css;
    }

    /**
     * Build overlay background value
     *
     * @param array $values Field values
     * @return string CSS background value
     */
    protected function build_overlay_background(array $values): string
    {
        if ($values['type'] === 'gradient') {
            return $this->build_gradient_css($values['gradient']);
        }

        // Default: color
        return $this->sanitize_css_value($values['color']);
    }

    /**
     * Build gradient CSS value
     *
     * @param array $gradient Gradient settings
     * @return string CSS gradient value
     */
    protected function build_gradient_css(array $gradient): string
    {
        $type = $gradient['type'] ?? 'linear';
        $start_color = $this->sanitize_css_value($gradient['start_color'] ?? 'rgba(0,0,0,0.8)');
        $end_color = $this->sanitize_css_value($gradient['end_color'] ?? 'rgba(0,0,0,0)');

        if ($type === 'radial') {
            return "radial-gradient(circle, {$start_color}, {$end_color})";
        }

        // Linear gradient
        $direction = $this->sanitize_css_value($gradient['direction'] ?? '180deg');
        return "linear-gradient({$direction}, {$start_color}, {$end_color})";
    }

    /**
     * Build hover CSS rules
     *
     * @param array $values Field values
     * @return string CSS rules for hover state
     */
    protected function build_hover_css(array $values): string
    {
        $css = '';

        // If hover color is set, use it
        if (!empty($values['hover_color'])) {
            $hover_color = $this->sanitize_css_value($values['hover_color']);
            $css .= "    background: {$hover_color};\n";
        }
        // If hover opacity is set, apply it
        elseif (!empty($values['hover_opacity'])) {
            $opacity = $this->sanitize_css_value($values['hover_opacity']);
            // Validate opacity is a valid number between 0 and 1
            if (is_numeric($opacity) && (float)$opacity >= 0 && (float)$opacity <= 1) {
                $css .= "    opacity: {$opacity};\n";
            }
        }

        return $css;
    }

    /**
     * Generate standalone overlay element CSS
     *
     * This can be used when you need the ::before pseudo-element
     * CSS without the parent selector modifications
     *
     * @param array $values Field values
     * @return string CSS for ::before pseudo-element only
     */
    public function generate_overlay_element(array $values): string
    {
        $values = $this->merge_defaults($values);

        if (empty($values['enabled'])) {
            return '';
        }

        $overlay_bg = $this->build_overlay_background($values);
        $blend_mode = $this->sanitize_css_value($values['blend_mode']);

        $css = "content: '';\n";
        $css .= "position: absolute;\n";
        $css .= "top: 0;\n";
        $css .= "left: 0;\n";
        $css .= "right: 0;\n";
        $css .= "bottom: 0;\n";
        $css .= "background: {$overlay_bg};\n";

        if (!empty($blend_mode) && $blend_mode !== 'normal') {
            $css .= "mix-blend-mode: {$blend_mode};\n";
        }

        $css .= "pointer-events: none;\n";
        $css .= "z-index: 1;\n";

        return $css;
    }

    /**
     * Check if overlay is enabled
     *
     * @param array $values Field values
     * @return bool
     */
    public function is_enabled(array $values): bool
    {
        $values = $this->merge_defaults($values);
        return !empty($values['enabled']);
    }
}
