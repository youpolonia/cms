<?php
/**
 * Row Module
 * Container for columns within a section
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Row extends JTB_Element
{
    public string $icon = 'row';
    public string $category = 'structure';

    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_typography = false;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;
    public bool $use_sizing = true;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'row';

    protected array $style_config = [
        'column_gap' => [
            'property' => '--row-column-gap',
            'selector' => '',
            'unit' => 'px',
            'responsive' => true
        ],
        'row_gap' => [
            'property' => '--row-row-gap',
            'selector' => '',
            'unit' => 'px',
            'responsive' => true
        ],
        'max_width' => [
            'property' => 'max-width',
            'selector' => '',
            'unit' => 'px',
            'responsive' => true
        ]
    ];

    public function getSlug(): string
    {
        return 'row';
    }

    public function getName(): string
    {
        return 'Row';
    }

    public function getFields(): array
    {
        return [
            'columns' => [
                'label' => 'Column Structure',
                'type' => 'select',
                'options' => [
                    '1' => '1 Column',
                    '1_2,1_2' => '2 Columns (1/2 + 1/2)',
                    '1_3,1_3,1_3' => '3 Columns (1/3 + 1/3 + 1/3)',
                    '1_4,1_4,1_4,1_4' => '4 Columns (1/4 + 1/4 + 1/4 + 1/4)',
                    '2_3,1_3' => '2 Columns (2/3 + 1/3)',
                    '1_3,2_3' => '2 Columns (1/3 + 2/3)',
                    '1_4,3_4' => '2 Columns (1/4 + 3/4)',
                    '3_4,1_4' => '2 Columns (3/4 + 1/4)',
                    '1_4,1_2,1_4' => '3 Columns (1/4 + 1/2 + 1/4)',
                    '1_5,1_5,1_5,1_5,1_5' => '5 Columns',
                    '1_6,1_6,1_6,1_6,1_6,1_6' => '6 Columns'
                ],
                'default' => '1'
            ],
            'column_gap' => [
                'label' => 'Column Gap',
                'type' => 'range',
                'min' => 0,
                'max' => 100,
                'unit' => 'px',
                'default' => 30,
                'responsive' => true
            ],
            'row_gap' => [
                'label' => 'Row Gap',
                'type' => 'range',
                'min' => 0,
                'max' => 100,
                'unit' => 'px',
                'default' => 30,
                'responsive' => true
            ],
            'equal_heights' => [
                'label' => 'Equalize Column Heights',
                'type' => 'toggle',
                'default' => true
            ],
            'vertical_align' => [
                'label' => 'Vertical Alignment',
                'type' => 'select',
                'options' => [
                    'default' => 'Default',
                    'flex-start' => 'Top',
                    'center' => 'Center',
                    'flex-end' => 'Bottom',
                    'stretch' => 'Stretch'
                ]
            ],
            'horizontal_align' => [
                'label' => 'Horizontal Alignment',
                'type' => 'select',
                'options' => [
                    'default' => 'Default',
                    'flex-start' => 'Left',
                    'center' => 'Center',
                    'flex-end' => 'Right',
                    'space-between' => 'Space Between',
                    'space-around' => 'Space Around'
                ]
            ],
            'max_width' => [
                'label' => 'Maximum Width',
                'type' => 'range',
                'min' => 0,
                'max' => 2000,
                'unit' => 'px',
                'responsive' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $id = $attrs['_id'] ?? $this->generateId();
        $columns = $attrs['columns'] ?? '1';

        // Row classes
        $classes = ['jtb-row'];

        // Column structure class
        $structureClass = 'jtb-row-cols-' . str_replace(',', '-', str_replace('_', '-', $columns));
        $classes[] = $structureClass;

        // Equal heights
        if (!empty($attrs['equal_heights'])) {
            $classes[] = 'jtb-row-equal-heights';
        }

        if (!empty($attrs['css_class'])) {
            $classes[] = $this->esc($attrs['css_class']);
        }

        // Visibility classes
        if (!empty($attrs['disable_on_desktop'])) {
            $classes[] = 'jtb-hide-desktop';
        }
        if (!empty($attrs['disable_on_tablet'])) {
            $classes[] = 'jtb-hide-tablet';
        }
        if (!empty($attrs['disable_on_phone'])) {
            $classes[] = 'jtb-hide-phone';
        }

        // Animation classes
        if (!empty($attrs['animation_style']) && $attrs['animation_style'] !== 'none') {
            $classes[] = 'jtb-animated';
            $classes[] = 'jtb-animation-' . $this->esc($attrs['animation_style']);
        }

        $classStr = implode(' ', $classes);
        $idAttr = !empty($attrs['css_id']) ? $attrs['css_id'] : $id;

        $html = '<div id="' . $this->esc($idAttr) . '" class="' . $classStr . '">';
        $html .= $content;
        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';
        $rules = [];

        // Column gap
        $columnGap = $attrs['column_gap'] ?? 30;
        $rules[] = 'gap: ' . (int) ($attrs['row_gap'] ?? 30) . 'px ' . (int) $columnGap . 'px';

        // Vertical alignment
        if (!empty($attrs['vertical_align']) && $attrs['vertical_align'] !== 'default') {
            $rules[] = 'align-items: ' . $attrs['vertical_align'];
        }

        // Horizontal alignment
        if (!empty($attrs['horizontal_align']) && $attrs['horizontal_align'] !== 'default') {
            $rules[] = 'justify-content: ' . $attrs['horizontal_align'];
        }

        // Max width
        if (!empty($attrs['max_width'])) {
            $rules[] = 'max-width: ' . (int) $attrs['max_width'] . 'px';
            $rules[] = 'margin-left: auto';
            $rules[] = 'margin-right: auto';
        }

        if (!empty($rules)) {
            $css .= $selector . ' { ' . implode('; ', $rules) . '; }' . "\n";
        }

        // Responsive gap
        if (!empty($attrs['column_gap__tablet']) || !empty($attrs['row_gap__tablet'])) {
            $tabletColGap = $attrs['column_gap__tablet'] ?? $columnGap;
            $tabletRowGap = $attrs['row_gap__tablet'] ?? ($attrs['row_gap'] ?? 30);
            $css .= '@media (max-width: 980px) {' . "\n";
            $css .= '  ' . $selector . ' { gap: ' . (int) $tabletRowGap . 'px ' . (int) $tabletColGap . 'px; }' . "\n";
            $css .= '}' . "\n";
        }

        if (!empty($attrs['column_gap__phone']) || !empty($attrs['row_gap__phone'])) {
            $phoneColGap = $attrs['column_gap__phone'] ?? ($attrs['column_gap__tablet'] ?? $columnGap);
            $phoneRowGap = $attrs['row_gap__phone'] ?? ($attrs['row_gap__tablet'] ?? ($attrs['row_gap'] ?? 30));
            $css .= '@media (max-width: 767px) {' . "\n";
            $css .= '  ' . $selector . ' { gap: ' . (int) $phoneRowGap . 'px ' . (int) $phoneColGap . 'px; }' . "\n";
            $css .= '}' . "\n";
        }

        // Responsive max width
        if (!empty($attrs['max_width__tablet'])) {
            $css .= '@media (max-width: 980px) {' . "\n";
            $css .= '  ' . $selector . ' { max-width: ' . (int) $attrs['max_width__tablet'] . 'px; }' . "\n";
            $css .= '}' . "\n";
        }

        if (!empty($attrs['max_width__phone'])) {
            $css .= '@media (max-width: 767px) {' . "\n";
            $css .= '  ' . $selector . ' { max-width: ' . (int) $attrs['max_width__phone'] . 'px; }' . "\n";
            $css .= '}' . "\n";
        }

        // Parent CSS
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

// Register module
JTB_Registry::register('row', JTB_Module_Row::class);
