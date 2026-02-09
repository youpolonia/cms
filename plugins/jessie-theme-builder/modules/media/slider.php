<?php
/**
 * Slider Module (Parent)
 * Content/Image slider carousel
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Slider extends JTB_Element
{
    public string $icon = 'slider';
    public string $category = 'media';
    public string $child_slug = 'slider_item';

    public bool $use_typography = false;
    public bool $use_background = false;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'slider';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'arrow_color' => [
            'property' => 'color',
            'selector' => '.jtb-slider-arrow',
            'hover' => true
        ],
        'arrow_bg_color' => [
            'property' => 'background',
            'selector' => '.jtb-slider-arrow',
            'hover' => true
        ],
        'dot_color' => [
            'property' => 'background',
            'selector' => '.jtb-slider-dot'
        ],
        'dot_active_color' => [
            'property' => 'background',
            'selector' => '.jtb-slider-dot.jtb-active'
        ],
        'slider_height' => [
            'property' => 'height',
            'selector' => '.jtb-slider-track',
            'unit' => 'px',
            'responsive' => true
        ]
    ];

    public function getSlug(): string
    {
        return 'slider';
    }

    public function getName(): string
    {
        return 'Slider';
    }

    public function getFields(): array
    {
        return [
            'show_arrows' => [
                'label' => 'Show Arrows',
                'type' => 'toggle',
                'default' => true
            ],
            'show_dots' => [
                'label' => 'Show Pagination Dots',
                'type' => 'toggle',
                'default' => true
            ],
            'auto' => [
                'label' => 'Auto Rotate',
                'type' => 'toggle',
                'default' => false
            ],
            'auto_speed' => [
                'label' => 'Auto Rotate Speed',
                'type' => 'range',
                'min' => 1000,
                'max' => 10000,
                'unit' => 'ms',
                'default' => 5000,
                'show_if' => ['auto' => true]
            ],
            'auto_ignore_hover' => [
                'label' => 'Continue on Hover',
                'type' => 'toggle',
                'default' => false,
                'show_if' => ['auto' => true]
            ],
            'loop' => [
                'label' => 'Infinite Loop',
                'type' => 'toggle',
                'default' => true
            ],
            'arrow_color' => [
                'label' => 'Arrow Color',
                'type' => 'color',
                'default' => '#ffffff',
                'hover' => true
            ],
            'arrow_bg_color' => [
                'label' => 'Arrow Background',
                'type' => 'color',
                'default' => 'rgba(0,0,0,0.3)',
                'hover' => true
            ],
            'dot_color' => [
                'label' => 'Dot Color (Inactive)',
                'type' => 'color',
                'default' => 'rgba(255,255,255,0.5)'
            ],
            'dot_active_color' => [
                'label' => 'Dot Color (Active)',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'slider_height' => [
                'label' => 'Slider Height',
                'type' => 'range',
                'min' => 100,
                'max' => 1000,
                'unit' => 'px',
                'default' => 500,
                'responsive' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $showArrows = $attrs['show_arrows'] ?? true;
        $showDots = $attrs['show_dots'] ?? true;
        $auto = !empty($attrs['auto']);
        $autoSpeed = $attrs['auto_speed'] ?? 5000;
        $loop = $attrs['loop'] ?? true;
        $ignoreHover = !empty($attrs['auto_ignore_hover']);

        $dataAttrs = ' data-auto="' . ($auto ? 'true' : 'false') . '"';
        $dataAttrs .= ' data-speed="' . $autoSpeed . '"';
        $dataAttrs .= ' data-loop="' . ($loop ? 'true' : 'false') . '"';
        $dataAttrs .= ' data-ignore-hover="' . ($ignoreHover ? 'true' : 'false') . '"';

        $innerHtml = '<div class="jtb-slider-container"' . $dataAttrs . '>';
        $innerHtml .= '<div class="jtb-slider-track">';
        $innerHtml .= $content; // Child slider items
        $innerHtml .= '</div>';

        // Arrows
        if ($showArrows) {
            $innerHtml .= '<button class="jtb-slider-arrow jtb-slider-prev" aria-label="Previous"><span>‹</span></button>';
            $innerHtml .= '<button class="jtb-slider-arrow jtb-slider-next" aria-label="Next"><span>›</span></button>';
        }

        // Dots
        if ($showDots) {
            $innerHtml .= '<div class="jtb-slider-dots"></div>';
        }

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for Slider module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Container
        $css .= $selector . ' .jtb-slider-container { position: relative; overflow: hidden; }' . "\n";

        // Track
        $css .= $selector . ' .jtb-slider-track { display: flex; transition: transform 0.5s ease; }' . "\n";

        // Slides
        $css .= $selector . ' .jtb-slider-slide { flex: 0 0 100%; width: 100%; position: relative; }' . "\n";

        // Arrows base styles
        $css .= $selector . ' .jtb-slider-arrow { ';
        $css .= 'position: absolute; top: 50%; transform: translateY(-50%); ';
        $css .= 'border: none; width: 50px; height: 50px; font-size: 30px; cursor: pointer; ';
        $css .= 'z-index: 10; transition: all 0.3s ease; display: flex; align-items: center; ';
        $css .= 'justify-content: center; border-radius: 50%; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-slider-prev { left: 20px; }' . "\n";
        $css .= $selector . ' .jtb-slider-next { right: 20px; }' . "\n";

        // Dots base styles
        $css .= $selector . ' .jtb-slider-dots { ';
        $css .= 'position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 10px; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-slider-dot { ';
        $css .= 'width: 12px; height: 12px; border-radius: 50%; cursor: pointer; transition: background 0.3s ease; border: none; ';
        $css .= '}' . "\n";

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('slider', JTB_Module_Slider::class);
