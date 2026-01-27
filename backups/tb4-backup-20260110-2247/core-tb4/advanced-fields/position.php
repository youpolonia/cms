<?php
/**
 * TB4 Position Advanced Field
 *
 * Handles CSS position settings (static, relative, absolute, fixed, sticky)
 * with responsive offset controls (top, right, bottom, left) and z-index
 *
 * @package TB4\AdvancedFields
 * @since 1.0.0
 */

namespace TB4\AdvancedFields;

require_once __DIR__ . '/advancedfield.php';

class Position extends AdvancedField
{
    /**
     * Available position types
     * @var array
     */
    protected array $position_options = [
        ''         => 'Default',
        'static'   => 'Static',
        'relative' => 'Relative',
        'absolute' => 'Absolute',
        'fixed'    => 'Fixed',
        'sticky'   => 'Sticky',
    ];

    /**
     * Get default position values
     *
     * @return array
     */
    public function get_defaults(): array
    {
        return [
            'position' => '',
            'top' => [
                'desktop' => '',
                'tablet'  => '',
                'mobile'  => '',
            ],
            'right' => [
                'desktop' => '',
                'tablet'  => '',
                'mobile'  => '',
            ],
            'bottom' => [
                'desktop' => '',
                'tablet'  => '',
                'mobile'  => '',
            ],
            'left' => [
                'desktop' => '',
                'tablet'  => '',
                'mobile'  => '',
            ],
            'z_index' => '',
        ];
    }

    /**
     * Render position control panel
     *
     * @param string $prefix Input name prefix
     * @param array $values Current values
     * @return string HTML
     */
    public function render_controls(string $prefix, array $values): string
    {
        $values = $this->merge_defaults($values);

        $html = '<div class="tb4-position-panel tb4-advanced-panel">';
        $html .= '<div class="tb4-panel-header">Position</div>';
        $html .= '<div class="tb4-panel-content">';

        // Position Type Select
        $html .= $this->render_select(
            "{$prefix}[position]",
            $values['position'],
            'Position Type',
            $this->position_options
        );

        // Z-Index Input
        $html .= $this->render_text_input(
            "{$prefix}[z_index]",
            $values['z_index'],
            'Z-Index',
            'auto'
        );

        // Responsive Offset Section
        $html .= '<div class="tb4-position-offsets">';
        $html .= '<div class="tb4-section-label">Offset Values</div>';

        // Responsive Tabs
        $html .= $this->render_responsive_tabs($prefix);

        // Responsive Panels for offsets
        foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
            $active = $breakpoint === 'desktop' ? ' tb4-active' : '';
            $html .= sprintf(
                '<div class="tb4-position-breakpoint%s" data-breakpoint="%s">',
                $active,
                $breakpoint
            );

            // Visual Position Box
            $html .= $this->render_position_box($prefix, $breakpoint, $values);

            $html .= '</div>';
        }

        $html .= '</div>'; // tb4-position-offsets
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

        $html = '<div class="tb4-position-tabs">';
        foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
            $active = $breakpoint === 'desktop' ? ' tb4-active' : '';
            $html .= sprintf(
                '<button type="button" class="tb4-position-tab%s" data-breakpoint="%s" title="%s">%s</button>',
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
     * Render visual position box UI
     *
     * @param string $prefix Input prefix
     * @param string $breakpoint Current breakpoint
     * @param array $values All values
     * @return string HTML
     */
    protected function render_position_box(
        string $prefix,
        string $breakpoint,
        array $values
    ): string {
        $html = '<div class="tb4-position-box">';

        // Top Input
        $html .= $this->render_offset_input(
            "{$prefix}[top][{$breakpoint}]",
            $values['top'][$breakpoint],
            'top'
        );

        // Middle Row (Left - Element - Right)
        $html .= '<div class="tb4-position-middle">';

        // Left Input
        $html .= $this->render_offset_input(
            "{$prefix}[left][{$breakpoint}]",
            $values['left'][$breakpoint],
            'left'
        );

        // Center Element
        $html .= '<div class="tb4-position-element">ELEMENT</div>';

        // Right Input
        $html .= $this->render_offset_input(
            "{$prefix}[right][{$breakpoint}]",
            $values['right'][$breakpoint],
            'right'
        );

        $html .= '</div>'; // tb4-position-middle

        // Bottom Input
        $html .= $this->render_offset_input(
            "{$prefix}[bottom][{$breakpoint}]",
            $values['bottom'][$breakpoint],
            'bottom'
        );

        $html .= '</div>'; // tb4-position-box

        return $html;
    }

    /**
     * Render an offset input field
     *
     * @param string $name Input name
     * @param string $value Current value
     * @param string $position Position (top, right, bottom, left)
     * @return string HTML
     */
    protected function render_offset_input(
        string $name,
        string $value,
        string $position
    ): string {
        return sprintf(
            '<div class="tb4-offset-input tb4-offset-%s">
                <label>%s</label>
                <input type="text"
                    name="%s"
                    value="%s"
                    class="tb4-input tb4-input-small"
                    data-position="%s"
                    placeholder="auto"
                    autocomplete="off">
            </div>',
            $position,
            ucfirst($position),
            $this->esc_attr($name),
            $this->esc_attr($value),
            $position
        );
    }

    /**
     * Generate CSS from position values
     *
     * @param string $selector CSS selector
     * @param array $values Field values
     * @return string CSS
     */
    public function generate_css(string $selector, array $values): string
    {
        $values = $this->merge_defaults($values);
        $css = '';

        // Desktop CSS (base styles, no media query)
        $desktop_rules = [];

        // Position type (not responsive - applies globally)
        if (!empty($values['position'])) {
            $position = $this->sanitize_css_value($values['position']);
            $desktop_rules[] = "position: {$position}";
        }

        // Z-index (not responsive - applies globally)
        if ($values['z_index'] !== '') {
            $z_index = $this->sanitize_css_value($values['z_index']);
            if (is_numeric($z_index) || $z_index === 'auto') {
                $desktop_rules[] = "z-index: {$z_index}";
            }
        }

        // Desktop offsets
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if ($values[$side]['desktop'] !== '') {
                $value = $this->css_unit($values[$side]['desktop']);
                $desktop_rules[] = "{$side}: {$value}";
            }
        }

        if (!empty($desktop_rules)) {
            $css .= "{$selector} {\n    " . implode(";\n    ", $desktop_rules) . ";\n}\n";
        }

        // Tablet and Mobile responsive offsets
        foreach (['tablet', 'mobile'] as $breakpoint) {
            $breakpoint_rules = [];

            foreach (['top', 'right', 'bottom', 'left'] as $side) {
                if ($values[$side][$breakpoint] !== '') {
                    $value = $this->css_unit($values[$side][$breakpoint]);
                    $breakpoint_rules[] = "{$side}: {$value}";
                }
            }

            if (!empty($breakpoint_rules)) {
                $breakpoint_css = "{$selector} {\n    " . implode(";\n    ", $breakpoint_rules) . ";\n}\n";
                $css .= $this->wrap_media_query($breakpoint, $breakpoint_css);
            }
        }

        return $css;
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

        // Position type
        if (!empty($values['position'])) {
            $styles[] = 'position: ' . $this->sanitize_css_value($values['position']);
        }

        // Z-index
        if ($values['z_index'] !== '') {
            $z_index = $this->sanitize_css_value($values['z_index']);
            if (is_numeric($z_index) || $z_index === 'auto') {
                $styles[] = "z-index: {$z_index}";
            }
        }

        // Offsets for specified breakpoint
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if ($values[$side][$breakpoint] !== '') {
                $value = $this->css_unit($values[$side][$breakpoint]);
                $styles[] = "{$side}: {$value}";
            }
        }

        return implode('; ', $styles);
    }

    /**
     * Set position values programmatically
     *
     * @param string $position Position type
     * @param string $z_index Z-index value
     * @return array Partial values structure
     */
    public function set_position(string $position, string $z_index = ''): array
    {
        return [
            'position' => $position,
            'z_index'  => $z_index,
        ];
    }

    /**
     * Set offset values for a specific breakpoint
     *
     * @param string $breakpoint desktop, tablet, or mobile
     * @param string $top Top value
     * @param string $right Right value
     * @param string $bottom Bottom value
     * @param string $left Left value
     * @return array Partial values structure
     */
    public function set_offsets(
        string $breakpoint,
        string $top = '',
        string $right = '',
        string $bottom = '',
        string $left = ''
    ): array {
        return [
            'top'    => [$breakpoint => $top],
            'right'  => [$breakpoint => $right],
            'bottom' => [$breakpoint => $bottom],
            'left'   => [$breakpoint => $left],
        ];
    }

    /**
     * Center an absolutely positioned element
     *
     * @param bool $horizontal Center horizontally
     * @param bool $vertical Center vertically
     * @return array Values to center the element
     */
    public function center_absolute(bool $horizontal = true, bool $vertical = true): array
    {
        $values = [
            'position' => 'absolute',
        ];

        if ($horizontal) {
            $values['left'] = [
                'desktop' => '50%',
                'tablet'  => '50%',
                'mobile'  => '50%',
            ];
        }

        if ($vertical) {
            $values['top'] = [
                'desktop' => '50%',
                'tablet'  => '50%',
                'mobile'  => '50%',
            ];
        }

        return $values;
    }

    /**
     * Check if position is set to a value that supports offsets
     *
     * @param string $position Position value
     * @return bool True if offsets apply
     */
    public function supports_offsets(string $position): bool
    {
        return in_array($position, ['relative', 'absolute', 'fixed', 'sticky'], true);
    }
}
