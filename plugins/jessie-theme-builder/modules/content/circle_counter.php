<?php
/**
 * Circle Counter Module
 * Animated circular progress counter
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_CircleCounter extends JTB_Element
{
    public string $icon = 'circle-progress';
    public string $category = 'content';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = false;
    public bool $use_box_shadow = false;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'circle_counter';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'text_orientation' => [
            'property' => 'text-align',
            'selector' => '.jtb-circle-counter-container',
            'responsive' => true
        ],
        'bar_bg_color' => [
            'property' => 'stroke',
            'selector' => '.jtb-circle-bg'
        ],
        'circle_color' => [
            'property' => 'stroke',
            'selector' => '.jtb-circle-progress'
        ],
        'number_color' => [
            'property' => 'color',
            'selector' => '.jtb-circle-counter-number-wrap'
        ],
        'number_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-circle-counter-number-wrap',
            'unit' => 'px',
            'responsive' => true
        ],
        'title_color' => [
            'property' => 'color',
            'selector' => '.jtb-circle-counter-title'
        ]
    ];

    public function getSlug(): string
    {
        return 'circle_counter';
    }

    public function getName(): string
    {
        return 'Circle Counter';
    }

    public function getFields(): array
    {
        return [
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Circle Counter'
            ],
            'number' => [
                'label' => 'Number',
                'type' => 'range',
                'min' => 0,
                'max' => 100,
                'unit' => '%',
                'default' => 50
            ],
            'bar_bg_color' => [
                'label' => 'Circle Background Color',
                'type' => 'color',
                'default' => '#dddddd'
            ],
            'circle_color' => [
                'label' => 'Circle Color',
                'type' => 'color',
                'default' => '#2ea3f2'
            ],
            'circle_color_alpha' => [
                'label' => 'Circle Opacity',
                'type' => 'range',
                'min' => 0,
                'max' => 100,
                'unit' => '%',
                'default' => 100
            ],
            'number_color' => [
                'label' => 'Number Color',
                'type' => 'color',
                'default' => '#000000'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color'
            ],
            'circle_size' => [
                'label' => 'Circle Size',
                'type' => 'range',
                'min' => 50,
                'max' => 400,
                'unit' => 'px',
                'default' => 200,
                'responsive' => true
            ],
            'circle_stroke_width' => [
                'label' => 'Stroke Width',
                'type' => 'range',
                'min' => 1,
                'max' => 50,
                'unit' => 'px',
                'default' => 10
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
            'number_font_size' => [
                'label' => 'Number Font Size',
                'type' => 'range',
                'min' => 10,
                'max' => 100,
                'unit' => 'px',
                'default' => 46,
                'responsive' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $title = $this->esc($attrs['title'] ?? 'Circle Counter');
        $number = intval($attrs['number'] ?? 50);
        $size = $attrs['circle_size'] ?? 200;
        $strokeWidth = $attrs['circle_stroke_width'] ?? 10;

        $radius = ($size - $strokeWidth) / 2;
        $circumference = 2 * pi() * $radius;
        $offset = $circumference - ($number / 100) * $circumference;

        $innerHtml = '<div class="jtb-circle-counter-container">';
        $innerHtml .= '<div class="jtb-circle-counter-wrap" data-percent="' . $number . '">';
        $innerHtml .= '<svg class="jtb-circle-counter-svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">';
        $innerHtml .= '<circle class="jtb-circle-bg" cx="' . ($size/2) . '" cy="' . ($size/2) . '" r="' . $radius . '" stroke-width="' . $strokeWidth . '" fill="none" />';
        $innerHtml .= '<circle class="jtb-circle-progress" cx="' . ($size/2) . '" cy="' . ($size/2) . '" r="' . $radius . '" stroke-width="' . $strokeWidth . '" fill="none" stroke-dasharray="' . $circumference . '" stroke-dashoffset="' . $circumference . '" data-target-offset="' . $offset . '" transform="rotate(-90 ' . ($size/2) . ' ' . ($size/2) . ')" />';
        $innerHtml .= '</svg>';
        $innerHtml .= '<div class="jtb-circle-counter-number-wrap">';
        $innerHtml .= '<span class="jtb-circle-counter-number" data-target="' . $number . '">0</span>';
        $innerHtml .= '<span class="jtb-circle-counter-percent">%</span>';
        $innerHtml .= '</div>';
        $innerHtml .= '</div>';
        $innerHtml .= '<div class="jtb-circle-counter-title">' . $title . '</div>';
        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for Circle Counter module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Circle wrap positioning (base styles)
        $css .= $selector . ' .jtb-circle-counter-wrap { position: relative; display: inline-block; }' . "\n";

        // Number positioning
        $css .= $selector . ' .jtb-circle-counter-number-wrap { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-weight: bold; }' . "\n";

        // Circle progress special handling (opacity + transition)
        if (!empty($attrs['circle_color'])) {
            $opacity = ($attrs['circle_color_alpha'] ?? 100) / 100;
            $css .= $selector . ' .jtb-circle-progress { stroke-opacity: ' . $opacity . '; stroke-linecap: round; transition: stroke-dashoffset 1.5s ease-in-out; }' . "\n";
        }

        // Title margin
        $css .= $selector . ' .jtb-circle-counter-title { margin-top: 15px; }' . "\n";

        // Responsive circle size
        if (!empty($attrs['circle_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-circle-counter-svg { width: ' . $attrs['circle_size__tablet'] . 'px; height: ' . $attrs['circle_size__tablet'] . 'px; } }' . "\n";
        }
        if (!empty($attrs['circle_size__phone'])) {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-circle-counter-svg { width: ' . $attrs['circle_size__phone'] . 'px; height: ' . $attrs['circle_size__phone'] . 'px; } }' . "\n";
        }

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('circle_counter', JTB_Module_CircleCounter::class);
