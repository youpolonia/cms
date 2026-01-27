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

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        $height = $attrs['slider_height'] ?? 500;

        // Container
        $css .= $selector . ' .jtb-slider-container { position: relative; overflow: hidden; }' . "\n";

        // Track
        $css .= $selector . ' .jtb-slider-track { ';
        $css .= 'display: flex; ';
        $css .= 'transition: transform 0.5s ease; ';
        $css .= 'height: ' . $height . 'px; ';
        $css .= '}' . "\n";

        // Slides
        $css .= $selector . ' .jtb-slider-slide { ';
        $css .= 'flex: 0 0 100%; ';
        $css .= 'width: 100%; ';
        $css .= 'position: relative; ';
        $css .= '}' . "\n";

        // Arrows
        $arrowColor = $attrs['arrow_color'] ?? '#ffffff';
        $arrowBg = $attrs['arrow_bg_color'] ?? 'rgba(0,0,0,0.3)';

        $css .= $selector . ' .jtb-slider-arrow { ';
        $css .= 'position: absolute; ';
        $css .= 'top: 50%; ';
        $css .= 'transform: translateY(-50%); ';
        $css .= 'background: ' . $arrowBg . '; ';
        $css .= 'color: ' . $arrowColor . '; ';
        $css .= 'border: none; ';
        $css .= 'width: 50px; ';
        $css .= 'height: 50px; ';
        $css .= 'font-size: 30px; ';
        $css .= 'cursor: pointer; ';
        $css .= 'z-index: 10; ';
        $css .= 'transition: all 0.3s ease; ';
        $css .= 'display: flex; ';
        $css .= 'align-items: center; ';
        $css .= 'justify-content: center; ';
        $css .= 'border-radius: 50%; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-slider-prev { left: 20px; }' . "\n";
        $css .= $selector . ' .jtb-slider-next { right: 20px; }' . "\n";

        // Arrow hover
        if (!empty($attrs['arrow_color__hover'])) {
            $css .= $selector . ' .jtb-slider-arrow:hover { color: ' . $attrs['arrow_color__hover'] . '; }' . "\n";
        }
        if (!empty($attrs['arrow_bg_color__hover'])) {
            $css .= $selector . ' .jtb-slider-arrow:hover { background: ' . $attrs['arrow_bg_color__hover'] . '; }' . "\n";
        }

        // Dots
        $dotColor = $attrs['dot_color'] ?? 'rgba(255,255,255,0.5)';
        $dotActiveColor = $attrs['dot_active_color'] ?? '#ffffff';

        $css .= $selector . ' .jtb-slider-dots { ';
        $css .= 'position: absolute; ';
        $css .= 'bottom: 20px; ';
        $css .= 'left: 50%; ';
        $css .= 'transform: translateX(-50%); ';
        $css .= 'display: flex; ';
        $css .= 'gap: 10px; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-slider-dot { ';
        $css .= 'width: 12px; ';
        $css .= 'height: 12px; ';
        $css .= 'border-radius: 50%; ';
        $css .= 'background: ' . $dotColor . '; ';
        $css .= 'cursor: pointer; ';
        $css .= 'transition: background 0.3s ease; ';
        $css .= 'border: none; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-slider-dot.jtb-active { background: ' . $dotActiveColor . '; }' . "\n";

        // Responsive height
        if (!empty($attrs['slider_height__tablet'])) {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-slider-track { height: ' . $attrs['slider_height__tablet'] . 'px; } }' . "\n";
        }
        if (!empty($attrs['slider_height__phone'])) {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-slider-track { height: ' . $attrs['slider_height__phone'] . 'px; } }' . "\n";
        }

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('slider', JTB_Module_Slider::class);
