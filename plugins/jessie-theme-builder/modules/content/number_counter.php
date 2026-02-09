<?php
/**
 * Number Counter Module
 * Animated number counter with title
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_NumberCounter extends JTB_Element
{
    public string $icon = 'counter';
    public string $category = 'content';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'number_counter';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'text_orientation' => [
            'property' => 'text-align',
            'selector' => '.jtb-number-counter-container',
            'responsive' => true
        ],
        'number_color' => [
            'property' => 'color',
            'selector' => '.jtb-number-counter-number-wrap'
        ],
        'number_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-number-counter-number-wrap',
            'unit' => 'px',
            'responsive' => true
        ],
        'title_color' => [
            'property' => 'color',
            'selector' => '.jtb-number-counter-title'
        ],
        'title_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-number-counter-title',
            'unit' => 'px',
            'responsive' => true
        ]
    ];

    public function getSlug(): string
    {
        return 'number_counter';
    }

    public function getName(): string
    {
        return 'Number Counter';
    }

    public function getFields(): array
    {
        return [
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Number Counter'
            ],
            'number' => [
                'label' => 'Number',
                'type' => 'text',
                'default' => '100'
            ],
            'percent_sign' => [
                'label' => 'Percent Sign',
                'type' => 'toggle',
                'default' => false
            ],
            'counter_prefix' => [
                'label' => 'Counter Prefix',
                'type' => 'text',
                'description' => 'Text before the number (e.g., $)'
            ],
            'counter_suffix' => [
                'label' => 'Counter Suffix',
                'type' => 'text',
                'description' => 'Text after the number (e.g., +, k)'
            ],
            'text_orientation' => [
                'label' => 'Text Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'center',
                'responsive' => true
            ],
            'number_color' => [
                'label' => 'Number Color',
                'type' => 'color',
                'default' => '#2ea3f2'
            ],
            'number_font_size' => [
                'label' => 'Number Font Size',
                'type' => 'range',
                'min' => 10,
                'max' => 200,
                'unit' => 'px',
                'default' => 60,
                'responsive' => true
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color'
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'range',
                'min' => 10,
                'max' => 60,
                'unit' => 'px',
                'responsive' => true
            ],
            'animation_duration' => [
                'label' => 'Animation Duration',
                'type' => 'range',
                'min' => 500,
                'max' => 5000,
                'unit' => 'ms',
                'default' => 2000
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $title = $this->esc($attrs['title'] ?? 'Number Counter');
        $number = $this->esc($attrs['number'] ?? '100');
        $percentSign = !empty($attrs['percent_sign']) ? '%' : '';
        $prefix = $this->esc($attrs['counter_prefix'] ?? '');
        $suffix = $this->esc($attrs['counter_suffix'] ?? '');
        $duration = $attrs['animation_duration'] ?? 2000;

        $innerHtml = '<div class="jtb-number-counter-container">';
        $innerHtml .= '<div class="jtb-number-counter-number-wrap">';
        if (!empty($prefix)) {
            $innerHtml .= '<span class="jtb-counter-prefix">' . $prefix . '</span>';
        }
        $innerHtml .= '<span class="jtb-counter-number" data-target="' . $number . '" data-duration="' . $duration . '">0</span>';
        if (!empty($percentSign)) {
            $innerHtml .= '<span class="jtb-counter-percent">' . $percentSign . '</span>';
        }
        if (!empty($suffix)) {
            $innerHtml .= '<span class="jtb-counter-suffix">' . $suffix . '</span>';
        }
        $innerHtml .= '</div>';
        $innerHtml .= '<div class="jtb-number-counter-title">' . $title . '</div>';
        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for Number Counter module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Number font-weight (not in style_config)
        if (!empty($attrs['number_font_size'])) {
            $css .= $selector . ' .jtb-number-counter-number-wrap { font-weight: bold; }' . "\n";
        }

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('number_counter', JTB_Module_NumberCounter::class);
