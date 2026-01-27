<?php
/**
 * TB4 Overflow Advanced Field
 *
 * Controls for overflow, text-overflow, and white-space CSS properties
 *
 * @package TB4\AdvancedFields
 * @since 1.0.0
 */

namespace TB4\AdvancedFields;

require_once __DIR__ . '/advancedfield.php';

class Overflow extends AdvancedField
{
    /**
     * Overflow options
     * @var array
     */
    protected array $overflow_options = [
        ''        => 'Default',
        'visible' => 'Visible',
        'hidden'  => 'Hidden',
        'scroll'  => 'Scroll',
        'auto'    => 'Auto',
        'clip'    => 'Clip'
    ];

    /**
     * Text overflow options
     * @var array
     */
    protected array $text_overflow_options = [
        ''         => 'Default',
        'clip'     => 'Clip',
        'ellipsis' => 'Ellipsis'
    ];

    /**
     * White space options
     * @var array
     */
    protected array $white_space_options = [
        ''         => 'Default',
        'normal'   => 'Normal',
        'nowrap'   => 'No Wrap',
        'pre'      => 'Pre',
        'pre-wrap' => 'Pre Wrap',
        'pre-line' => 'Pre Line'
    ];

    /**
     * Get the default values for overflow fields
     *
     * @return array Default field values
     */
    public function get_defaults(): array
    {
        return [
            'overflow'      => '',
            'overflow_x'    => '',
            'overflow_y'    => '',
            'text_overflow' => '',
            'white_space'   => ''
        ];
    }

    /**
     * Render the control panel HTML for overflow settings
     *
     * @param string $prefix Input name prefix for form fields
     * @param array $values Current field values
     * @return string HTML output for the control panel
     */
    public function render_controls(string $prefix, array $values): string
    {
        $values = $this->merge_defaults($values);

        $html = '<div class="tb4-advanced-field tb4-overflow-field">';
        $html .= '<div class="tb4-field-header">Overflow</div>';
        $html .= '<div class="tb4-field-body">';

        // Overflow shorthand
        $html .= $this->render_select(
            $prefix . '[overflow]',
            $values['overflow'],
            'Overflow',
            $this->overflow_options
        );

        // Individual axis controls
        $html .= '<div class="tb4-field-row tb4-field-row-2">';

        // Overflow X
        $html .= $this->render_select(
            $prefix . '[overflow_x]',
            $values['overflow_x'],
            'Overflow X',
            $this->overflow_options
        );

        // Overflow Y
        $html .= $this->render_select(
            $prefix . '[overflow_y]',
            $values['overflow_y'],
            'Overflow Y',
            $this->overflow_options
        );

        $html .= '</div>'; // .tb4-field-row

        // Text overflow and white space
        $html .= '<div class="tb4-field-row tb4-field-row-2">';

        // Text overflow
        $html .= $this->render_select(
            $prefix . '[text_overflow]',
            $values['text_overflow'],
            'Text Overflow',
            $this->text_overflow_options
        );

        // White space
        $html .= $this->render_select(
            $prefix . '[white_space]',
            $values['white_space'],
            'White Space',
            $this->white_space_options
        );

        $html .= '</div>'; // .tb4-field-row

        $html .= '</div>'; // .tb4-field-body
        $html .= '</div>'; // .tb4-overflow-field

        return $html;
    }

    /**
     * Generate CSS from overflow field values
     *
     * @param string $selector CSS selector to apply styles to
     * @param array $values Field values
     * @return string Generated CSS
     */
    public function generate_css(string $selector, array $values): string
    {
        $values = $this->merge_defaults($values);
        $declarations = [];

        // Handle overflow properties
        $overflow = $this->sanitize_css_value($values['overflow']);
        $overflow_x = $this->sanitize_css_value($values['overflow_x']);
        $overflow_y = $this->sanitize_css_value($values['overflow_y']);

        // If individual X/Y are set, use them; otherwise use shorthand
        if ($overflow_x !== '' || $overflow_y !== '') {
            if ($overflow_x !== '') {
                $declarations[] = "overflow-x: {$overflow_x}";
            }
            if ($overflow_y !== '') {
                $declarations[] = "overflow-y: {$overflow_y}";
            }
        } elseif ($overflow !== '') {
            $declarations[] = "overflow: {$overflow}";
        }

        // Text overflow
        $text_overflow = $this->sanitize_css_value($values['text_overflow']);
        if ($text_overflow !== '') {
            $declarations[] = "text-overflow: {$text_overflow}";
        }

        // White space
        $white_space = $this->sanitize_css_value($values['white_space']);
        if ($white_space !== '') {
            $declarations[] = "white-space: {$white_space}";
        }

        // Return empty if no declarations
        if (empty($declarations)) {
            return '';
        }

        // Build CSS block
        $css = $selector . " {\n";
        foreach ($declarations as $declaration) {
            $css .= "    {$declaration};\n";
        }
        $css .= "}\n";

        return $css;
    }
}
