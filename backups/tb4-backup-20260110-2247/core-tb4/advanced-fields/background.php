<?php
/**
 * TB4 Background Advanced Field
 *
 * Handles background settings including colors, gradients, images, and video backgrounds
 *
 * @package TB4\AdvancedFields
 * @since 1.0.0
 */

namespace TB4\AdvancedFields;

require_once __DIR__ . '/advancedfield.php';

class Background extends AdvancedField
{
    /**
     * Available background types
     * @var array
     */
    protected array $background_types = [
        ''         => 'None',
        'color'    => 'Solid Color',
        'gradient' => 'Gradient',
        'image'    => 'Image',
        'video'    => 'Video',
    ];

    /**
     * Available gradient types
     * @var array
     */
    protected array $gradient_types = [
        'linear' => 'Linear',
        'radial' => 'Radial',
        'conic'  => 'Conic',
    ];

    /**
     * Available gradient directions (for linear)
     * @var array
     */
    protected array $gradient_directions = [
        'to bottom'       => 'Top to Bottom',
        'to top'          => 'Bottom to Top',
        'to right'        => 'Left to Right',
        'to left'         => 'Right to Left',
        'to bottom right' => 'Top-Left to Bottom-Right',
        'to bottom left'  => 'Top-Right to Bottom-Left',
        'to top right'    => 'Bottom-Left to Top-Right',
        'to top left'     => 'Bottom-Right to Top-Left',
    ];

    /**
     * Available background sizes
     * @var array
     */
    protected array $background_sizes = [
        ''        => 'Default',
        'auto'    => 'Auto',
        'cover'   => 'Cover',
        'contain' => 'Contain',
        'custom'  => 'Custom',
    ];

    /**
     * Available background positions
     * @var array
     */
    protected array $background_positions = [
        ''              => 'Default',
        'center center' => 'Center',
        'top center'    => 'Top',
        'bottom center' => 'Bottom',
        'center left'   => 'Left',
        'center right'  => 'Right',
        'top left'      => 'Top Left',
        'top right'     => 'Top Right',
        'bottom left'   => 'Bottom Left',
        'bottom right'  => 'Bottom Right',
        'custom'        => 'Custom',
    ];

    /**
     * Available background repeat options
     * @var array
     */
    protected array $background_repeats = [
        ''          => 'Default',
        'no-repeat' => 'No Repeat',
        'repeat'    => 'Repeat',
        'repeat-x'  => 'Repeat X',
        'repeat-y'  => 'Repeat Y',
    ];

    /**
     * Available background attachment options
     * @var array
     */
    protected array $background_attachments = [
        ''       => 'Default',
        'scroll' => 'Scroll',
        'fixed'  => 'Fixed (Parallax)',
        'local'  => 'Local',
    ];

    /**
     * Get default background values
     *
     * @return array
     */
    public function get_defaults(): array
    {
        return [
            'type'     => '',
            'color'    => '',
            'gradient' => [
                'type'      => 'linear',
                'direction' => 'to bottom',
                'angle'     => '180',
                'stops'     => [
                    ['color' => '#ffffff', 'position' => '0'],
                    ['color' => '#000000', 'position' => '100'],
                ],
            ],
            'image'    => [
                'url'        => '',
                'size'       => 'cover',
                'size_custom'=> '',
                'position'   => 'center center',
                'position_x' => '50',
                'position_y' => '50',
                'repeat'     => 'no-repeat',
                'attachment' => 'scroll',
            ],
            'video'    => [
                'url'      => '',
                'poster'   => '',
                'loop'     => true,
                'muted'    => true,
                'autoplay' => true,
            ],
            'overlay'  => [
                'enabled' => false,
                'color'   => 'rgba(0,0,0,0.5)',
            ],
        ];
    }

    /**
     * Render background control panel
     *
     * @param string $prefix Input name prefix
     * @param array $values Current values
     * @return string HTML
     */
    public function render_controls(string $prefix, array $values): string
    {
        $values = $this->merge_defaults($values);

        $html = '<div class="tb4-background-panel tb4-advanced-panel">';
        $html .= '<div class="tb4-panel-header">Background</div>';
        $html .= '<div class="tb4-panel-content">';

        // Background Type Selector
        $html .= $this->render_select(
            "{$prefix}[type]",
            $values['type'],
            'Background Type',
            $this->background_types
        );

        // Color Section
        $html .= $this->render_section_color($prefix, $values);

        // Gradient Section
        $html .= $this->render_section_gradient($prefix, $values);

        // Image Section
        $html .= $this->render_section_image($prefix, $values);

        // Video Section
        $html .= $this->render_section_video($prefix, $values);

        // Overlay Section (for image/video)
        $html .= $this->render_section_overlay($prefix, $values);

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

        $html = sprintf('<div class="tb4-bg-section tb4-bg-color-section" data-bg-type="color" %s>', $display);
        $html .= $this->render_color_picker(
            "{$prefix}[color]",
            $values['color'],
            'Background Color'
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

        $html = sprintf('<div class="tb4-bg-section tb4-bg-gradient-section" data-bg-type="gradient" %s>', $display);

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

        // Custom Angle (for linear)
        $html .= $this->render_text_input(
            "{$prefix}[gradient][angle]",
            $gradient['angle'],
            'Custom Angle (deg)',
            '180'
        );

        // Gradient Stops
        $html .= '<div class="tb4-gradient-stops">';
        $html .= '<label>Color Stops</label>';
        $html .= '<div class="tb4-gradient-stops-list">';

        foreach ($gradient['stops'] as $index => $stop) {
            $html .= $this->render_gradient_stop($prefix, $index, $stop);
        }

        $html .= '</div>';
        $html .= '<button type="button" class="tb4-btn tb4-btn-small tb4-add-gradient-stop">+ Add Stop</button>';
        $html .= '</div>';

        // Gradient Preview
        $html .= '<div class="tb4-gradient-preview"></div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Render a single gradient stop
     *
     * @param string $prefix Input prefix
     * @param int $index Stop index
     * @param array $stop Stop data
     * @return string HTML
     */
    protected function render_gradient_stop(string $prefix, int $index, array $stop): string
    {
        $html = '<div class="tb4-gradient-stop" data-index="' . $index . '">';
        $html .= sprintf(
            '<input type="color" name="%s[gradient][stops][%d][color]" value="%s" class="tb4-color-picker">',
            $this->esc_attr($prefix),
            $index,
            $this->esc_attr($stop['color'] ?? '#ffffff')
        );
        $html .= sprintf(
            '<input type="number" name="%s[gradient][stops][%d][position]" value="%s" min="0" max="100" class="tb4-input tb4-input-tiny" placeholder="%%">',
            $this->esc_attr($prefix),
            $index,
            $this->esc_attr($stop['position'] ?? '0')
        );
        $html .= '<span class="tb4-unit">%</span>';
        $html .= '<button type="button" class="tb4-btn-icon tb4-remove-gradient-stop" title="Remove">×</button>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render image section
     *
     * @param string $prefix Input prefix
     * @param array $values Current values
     * @return string HTML
     */
    protected function render_section_image(string $prefix, array $values): string
    {
        $display = $values['type'] === 'image' ? '' : 'style="display:none;"';
        $image = $values['image'];

        $html = sprintf('<div class="tb4-bg-section tb4-bg-image-section" data-bg-type="image" %s>', $display);

        // Image URL with Browse Button
        $html .= '<div class="tb4-field-control tb4-media-control">';
        $html .= '<label>Image</label>';
        $html .= '<div class="tb4-media-input-wrapper">';
        $html .= sprintf(
            '<input type="text" name="%s[image][url]" value="%s" class="tb4-input tb4-media-url" placeholder="Image URL">',
            $this->esc_attr($prefix),
            $this->esc_attr($image['url'])
        );
        $html .= '<button type="button" class="tb4-btn tb4-media-browse">Browse</button>';
        $html .= '</div>';
        $html .= '</div>';

        // Image Preview
        if (!empty($image['url'])) {
            $html .= sprintf(
                '<div class="tb4-image-preview"><img src="%s" alt="Preview"></div>',
                $this->esc_attr($image['url'])
            );
        }

        // Size & Position Row
        $html .= '<div class="tb4-control-row">';
        $html .= $this->render_select(
            "{$prefix}[image][size]",
            $image['size'],
            'Size',
            $this->background_sizes
        );
        $html .= $this->render_select(
            "{$prefix}[image][position]",
            $image['position'],
            'Position',
            $this->background_positions
        );
        $html .= '</div>';

        // Custom Size
        $html .= $this->render_text_input(
            "{$prefix}[image][size_custom]",
            $image['size_custom'],
            'Custom Size',
            '100% auto'
        );

        // Custom Position
        $html .= '<div class="tb4-control-row">';
        $html .= $this->render_text_input(
            "{$prefix}[image][position_x]",
            $image['position_x'],
            'Position X (%)',
            '50'
        );
        $html .= $this->render_text_input(
            "{$prefix}[image][position_y]",
            $image['position_y'],
            'Position Y (%)',
            '50'
        );
        $html .= '</div>';

        // Repeat & Attachment Row
        $html .= '<div class="tb4-control-row">';
        $html .= $this->render_select(
            "{$prefix}[image][repeat]",
            $image['repeat'],
            'Repeat',
            $this->background_repeats
        );
        $html .= $this->render_select(
            "{$prefix}[image][attachment]",
            $image['attachment'],
            'Attachment',
            $this->background_attachments
        );
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Render video section
     *
     * @param string $prefix Input prefix
     * @param array $values Current values
     * @return string HTML
     */
    protected function render_section_video(string $prefix, array $values): string
    {
        $display = $values['type'] === 'video' ? '' : 'style="display:none;"';
        $video = $values['video'];

        $html = sprintf('<div class="tb4-bg-section tb4-bg-video-section" data-bg-type="video" %s>', $display);

        // Video URL
        $html .= '<div class="tb4-field-control tb4-media-control">';
        $html .= '<label>Video URL</label>';
        $html .= '<div class="tb4-media-input-wrapper">';
        $html .= sprintf(
            '<input type="text" name="%s[video][url]" value="%s" class="tb4-input tb4-media-url" placeholder="MP4 or YouTube URL">',
            $this->esc_attr($prefix),
            $this->esc_attr($video['url'])
        );
        $html .= '<button type="button" class="tb4-btn tb4-media-browse">Browse</button>';
        $html .= '</div>';
        $html .= '</div>';

        // Poster Image
        $html .= '<div class="tb4-field-control tb4-media-control">';
        $html .= '<label>Poster Image (Fallback)</label>';
        $html .= '<div class="tb4-media-input-wrapper">';
        $html .= sprintf(
            '<input type="text" name="%s[video][poster]" value="%s" class="tb4-input tb4-media-url" placeholder="Poster image URL">',
            $this->esc_attr($prefix),
            $this->esc_attr($video['poster'])
        );
        $html .= '<button type="button" class="tb4-btn tb4-media-browse">Browse</button>';
        $html .= '</div>';
        $html .= '</div>';

        // Video Options
        $html .= '<div class="tb4-checkbox-group">';
        $html .= $this->render_checkbox("{$prefix}[video][loop]", $video['loop'], 'Loop');
        $html .= $this->render_checkbox("{$prefix}[video][muted]", $video['muted'], 'Muted');
        $html .= $this->render_checkbox("{$prefix}[video][autoplay]", $video['autoplay'], 'Autoplay');
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Render overlay section
     *
     * @param string $prefix Input prefix
     * @param array $values Current values
     * @return string HTML
     */
    protected function render_section_overlay(string $prefix, array $values): string
    {
        $overlay = $values['overlay'];
        $show = in_array($values['type'], ['image', 'video']);
        $display = $show ? '' : 'style="display:none;"';

        $html = sprintf('<div class="tb4-bg-section tb4-bg-overlay-section" data-bg-types="image,video" %s>', $display);
        $html .= '<div class="tb4-section-header">Overlay</div>';

        $html .= $this->render_checkbox("{$prefix}[overlay][enabled]", $overlay['enabled'], 'Enable Overlay');

        $html .= $this->render_color_picker(
            "{$prefix}[overlay][color]",
            $overlay['color'],
            'Overlay Color'
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
     * Generate CSS from background values
     *
     * @param string $selector CSS selector
     * @param array $values Field values
     * @return string CSS
     */
    public function generate_css(string $selector, array $values): string
    {
        $values = $this->merge_defaults($values);
        $css = '';
        $rules = [];

        switch ($values['type']) {
            case 'color':
                if (!empty($values['color'])) {
                    $rules[] = 'background-color: ' . $this->sanitize_css_value($values['color']);
                }
                break;

            case 'gradient':
                $gradient_css = $this->build_gradient_css($values['gradient']);
                if (!empty($gradient_css)) {
                    $rules[] = 'background: ' . $gradient_css;
                }
                break;

            case 'image':
                $image_rules = $this->build_image_css($values['image']);
                $rules = array_merge($rules, $image_rules);
                break;

            case 'video':
                // Video backgrounds are handled via JavaScript/HTML
                // Add poster as fallback
                if (!empty($values['video']['poster'])) {
                    $rules[] = 'background-image: url(' . $this->sanitize_css_value($values['video']['poster']) . ')';
                    $rules[] = 'background-size: cover';
                    $rules[] = 'background-position: center center';
                }
                break;
        }

        if (!empty($rules)) {
            $css .= "{$selector} {\n    " . implode(";\n    ", $rules) . ";\n}\n";
        }

        // Overlay (using ::before pseudo-element)
        if ($values['overlay']['enabled'] && in_array($values['type'], ['image', 'video'])) {
            $overlay_color = $this->sanitize_css_value($values['overlay']['color']);
            $css .= "{$selector} {\n    position: relative;\n}\n";
            $css .= "{$selector}::before {\n";
            $css .= "    content: '';\n";
            $css .= "    position: absolute;\n";
            $css .= "    top: 0;\n";
            $css .= "    left: 0;\n";
            $css .= "    right: 0;\n";
            $css .= "    bottom: 0;\n";
            $css .= "    background: {$overlay_color};\n";
            $css .= "    pointer-events: none;\n";
            $css .= "}\n";
        }

        return $css;
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
        $stops = $gradient['stops'] ?? [];

        if (empty($stops)) {
            return '';
        }

        // Build color stops
        $stop_strings = [];
        foreach ($stops as $stop) {
            $color = $this->sanitize_css_value($stop['color'] ?? '#ffffff');
            $position = $this->sanitize_css_value($stop['position'] ?? '0');
            $stop_strings[] = "{$color} {$position}%";
        }

        $stops_css = implode(', ', $stop_strings);

        switch ($type) {
            case 'linear':
                $direction = $gradient['direction'] ?? 'to bottom';
                // Use custom angle if set
                if (!empty($gradient['angle']) && is_numeric($gradient['angle'])) {
                    $direction = $gradient['angle'] . 'deg';
                }
                return "linear-gradient({$direction}, {$stops_css})";

            case 'radial':
                return "radial-gradient(circle, {$stops_css})";

            case 'conic':
                return "conic-gradient({$stops_css})";

            default:
                return "linear-gradient(to bottom, {$stops_css})";
        }
    }

    /**
     * Build image background CSS rules
     *
     * @param array $image Image settings
     * @return array CSS rules
     */
    protected function build_image_css(array $image): array
    {
        $rules = [];

        if (empty($image['url'])) {
            return $rules;
        }

        $rules[] = 'background-image: url(' . $this->sanitize_css_value($image['url']) . ')';

        // Size
        if (!empty($image['size'])) {
            if ($image['size'] === 'custom' && !empty($image['size_custom'])) {
                $rules[] = 'background-size: ' . $this->sanitize_css_value($image['size_custom']);
            } else {
                $rules[] = 'background-size: ' . $this->sanitize_css_value($image['size']);
            }
        }

        // Position
        if (!empty($image['position'])) {
            if ($image['position'] === 'custom') {
                $x = $this->sanitize_css_value($image['position_x'] ?? '50');
                $y = $this->sanitize_css_value($image['position_y'] ?? '50');
                $rules[] = "background-position: {$x}% {$y}%";
            } else {
                $rules[] = 'background-position: ' . $this->sanitize_css_value($image['position']);
            }
        }

        // Repeat
        if (!empty($image['repeat'])) {
            $rules[] = 'background-repeat: ' . $this->sanitize_css_value($image['repeat']);
        }

        // Attachment
        if (!empty($image['attachment'])) {
            $rules[] = 'background-attachment: ' . $this->sanitize_css_value($image['attachment']);
        }

        return $rules;
    }

    /**
     * Generate video background HTML (for use in templates)
     *
     * @param array $values Field values
     * @return string HTML for video background
     */
    public function generate_video_html(array $values): string
    {
        $values = $this->merge_defaults($values);

        if ($values['type'] !== 'video' || empty($values['video']['url'])) {
            return '';
        }

        $video = $values['video'];
        $attrs = [];

        if ($video['loop']) {
            $attrs[] = 'loop';
        }
        if ($video['muted']) {
            $attrs[] = 'muted';
        }
        if ($video['autoplay']) {
            $attrs[] = 'autoplay';
        }

        $attrs[] = 'playsinline';
        $attrs_str = implode(' ', $attrs);

        $poster_attr = !empty($video['poster'])
            ? ' poster="' . $this->esc_attr($video['poster']) . '"'
            : '';

        return sprintf(
            '<video class="tb4-bg-video" %s%s>
                <source src="%s" type="video/mp4">
            </video>',
            $attrs_str,
            $poster_attr,
            $this->esc_attr($video['url'])
        );
    }
}
