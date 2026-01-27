<?php
/**
 * Fullwidth Slider Module
 * Full-width image/content slider
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_FullwidthSlider extends JTB_Element
{
    public string $icon = 'slider-fullwidth';
    public string $category = 'fullwidth';
    public string $child_slug = 'fullwidth_slider_item';

    public bool $use_typography = false;
    public bool $use_background = false;
    public bool $use_spacing = true;
    public bool $use_border = false;
    public bool $use_box_shadow = true;
    public bool $use_animation = false;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    public function getSlug(): string
    {
        return 'fullwidth_slider';
    }

    public function getName(): string
    {
        return 'Fullwidth Slider';
    }

    public function getFields(): array
    {
        return [
            'show_arrows' => [
                'label' => 'Show Arrows',
                'type' => 'toggle',
                'default' => true
            ],
            'show_pagination' => [
                'label' => 'Show Pagination',
                'type' => 'toggle',
                'default' => true
            ],
            'auto_play' => [
                'label' => 'Auto Play',
                'type' => 'toggle',
                'default' => true
            ],
            'auto_speed' => [
                'label' => 'Auto Play Speed',
                'type' => 'range',
                'min' => 1000,
                'max' => 10000,
                'unit' => 'ms',
                'default' => 5000,
                'show_if' => ['auto_play' => true]
            ],
            'loop' => [
                'label' => 'Loop Slides',
                'type' => 'toggle',
                'default' => true
            ],
            'parallax' => [
                'label' => 'Parallax Effect',
                'type' => 'toggle',
                'default' => false
            ],
            'slider_height' => [
                'label' => 'Slider Height',
                'type' => 'select',
                'options' => [
                    'auto' => 'Auto',
                    'fullscreen' => 'Fullscreen',
                    'custom' => 'Custom'
                ],
                'default' => 'auto'
            ],
            'custom_height' => [
                'label' => 'Custom Height',
                'type' => 'range',
                'min' => 200,
                'max' => 1000,
                'unit' => 'px',
                'default' => 500,
                'show_if' => ['slider_height' => 'custom'],
                'responsive' => true
            ],
            // Arrow styling
            'arrows_color' => [
                'label' => 'Arrow Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'arrows_bg_color' => [
                'label' => 'Arrow Background',
                'type' => 'color',
                'default' => 'rgba(0,0,0,0.3)'
            ],
            // Pagination styling
            'dot_color' => [
                'label' => 'Pagination Color',
                'type' => 'color',
                'default' => 'rgba(255,255,255,0.5)'
            ],
            'dot_active_color' => [
                'label' => 'Active Pagination Color',
                'type' => 'color',
                'default' => '#ffffff'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $showArrows = $attrs['show_arrows'] ?? true;
        $showPagination = $attrs['show_pagination'] ?? true;
        $autoPlay = !empty($attrs['auto_play']);
        $autoSpeed = $attrs['auto_speed'] ?? 5000;
        $loop = !empty($attrs['loop']);
        $parallax = !empty($attrs['parallax']);

        $sliderId = 'jtb-fullwidth-slider-' . $this->generateId();

        $sliderData = json_encode([
            'autoplay' => $autoPlay,
            'autoplaySpeed' => $autoSpeed,
            'loop' => $loop,
            'parallax' => $parallax
        ]);

        $innerHtml = '<div class="jtb-fullwidth-slider-container" id="' . $sliderId . '" data-slider="' . $this->esc($sliderData) . '">';

        // Slides wrapper
        $innerHtml .= '<div class="jtb-fullwidth-slider-wrapper">';
        $innerHtml .= '<div class="jtb-fullwidth-slider-track">';

        if (!empty($content)) {
            $innerHtml .= $content;
        } else {
            // Default sample slides
            $innerHtml .= $this->renderDefaultSlides();
        }

        $innerHtml .= '</div>';
        $innerHtml .= '</div>';

        // Arrows
        if ($showArrows) {
            $innerHtml .= '<button class="jtb-fullwidth-slider-arrow jtb-slider-prev" aria-label="Previous slide">‹</button>';
            $innerHtml .= '<button class="jtb-fullwidth-slider-arrow jtb-slider-next" aria-label="Next slide">›</button>';
        }

        // Pagination
        if ($showPagination) {
            $innerHtml .= '<div class="jtb-fullwidth-slider-pagination"></div>';
        }

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    private function renderDefaultSlides(): string
    {
        $slides = [
            [
                'title' => 'Welcome to Our Website',
                'subtitle' => 'We create amazing experiences',
                'button' => 'Learn More',
                'bg' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
            ],
            [
                'title' => 'Professional Solutions',
                'subtitle' => 'Tailored to your needs',
                'button' => 'Get Started',
                'bg' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'
            ],
            [
                'title' => 'Creative Design',
                'subtitle' => 'Stand out from the crowd',
                'button' => 'View Portfolio',
                'bg' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)'
            ]
        ];

        $html = '';
        foreach ($slides as $slide) {
            $html .= '<div class="jtb-fullwidth-slide" style="background: ' . $slide['bg'] . ';">';
            $html .= '<div class="jtb-slide-content">';
            $html .= '<h2 class="jtb-slide-title">' . $slide['title'] . '</h2>';
            $html .= '<p class="jtb-slide-subtitle">' . $slide['subtitle'] . '</p>';
            $html .= '<a href="#" class="jtb-button jtb-slide-button">' . $slide['button'] . '</a>';
            $html .= '</div>';
            $html .= '</div>';
        }
        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        $sliderHeight = $attrs['slider_height'] ?? 'auto';
        $customHeight = $attrs['custom_height'] ?? 500;
        $arrowColor = $attrs['arrows_color'] ?? '#ffffff';
        $arrowBg = $attrs['arrows_bg_color'] ?? 'rgba(0,0,0,0.3)';
        $dotColor = $attrs['dot_color'] ?? 'rgba(255,255,255,0.5)';
        $dotActive = $attrs['dot_active_color'] ?? '#ffffff';

        // Container
        $css .= $selector . ' .jtb-fullwidth-slider-container { position: relative; overflow: hidden; }' . "\n";

        // Height
        if ($sliderHeight === 'fullscreen') {
            $css .= $selector . ' .jtb-fullwidth-slider-container { height: 100vh; }' . "\n";
        } elseif ($sliderHeight === 'custom') {
            $css .= $selector . ' .jtb-fullwidth-slider-container { height: ' . $customHeight . 'px; }' . "\n";
        }

        // Track
        $css .= $selector . ' .jtb-fullwidth-slider-wrapper { height: 100%; }' . "\n";
        $css .= $selector . ' .jtb-fullwidth-slider-track { display: flex; height: 100%; transition: transform 0.5s ease-in-out; }' . "\n";

        // Slides
        $css .= $selector . ' .jtb-fullwidth-slide { '
            . 'flex: 0 0 100%; '
            . 'display: flex; '
            . 'align-items: center; '
            . 'justify-content: center; '
            . 'background-size: cover; '
            . 'background-position: center; '
            . 'position: relative; '
            . 'min-height: 400px; '
            . '}' . "\n";

        // Slide content
        $css .= $selector . ' .jtb-slide-content { '
            . 'text-align: center; '
            . 'color: #ffffff; '
            . 'max-width: 800px; '
            . 'padding: 40px; '
            . 'z-index: 1; '
            . '}' . "\n";

        $css .= $selector . ' .jtb-slide-title { font-size: 48px; margin-bottom: 20px; }' . "\n";
        $css .= $selector . ' .jtb-slide-subtitle { font-size: 20px; margin-bottom: 30px; opacity: 0.9; }' . "\n";
        $css .= $selector . ' .jtb-slide-button { background: #ffffff; color: #333; padding: 15px 35px; text-decoration: none; display: inline-block; }' . "\n";

        // Arrows
        $css .= $selector . ' .jtb-fullwidth-slider-arrow { '
            . 'position: absolute; '
            . 'top: 50%; '
            . 'transform: translateY(-50%); '
            . 'background: ' . $arrowBg . '; '
            . 'color: ' . $arrowColor . '; '
            . 'border: none; '
            . 'width: 50px; '
            . 'height: 50px; '
            . 'font-size: 30px; '
            . 'cursor: pointer; '
            . 'z-index: 10; '
            . 'transition: all 0.3s ease; '
            . '}' . "\n";

        $css .= $selector . ' .jtb-slider-prev { left: 20px; }' . "\n";
        $css .= $selector . ' .jtb-slider-next { right: 20px; }' . "\n";
        $css .= $selector . ' .jtb-fullwidth-slider-arrow:hover { background: rgba(0,0,0,0.6); }' . "\n";

        // Pagination
        $css .= $selector . ' .jtb-fullwidth-slider-pagination { '
            . 'position: absolute; '
            . 'bottom: 30px; '
            . 'left: 50%; '
            . 'transform: translateX(-50%); '
            . 'display: flex; '
            . 'gap: 10px; '
            . 'z-index: 10; '
            . '}' . "\n";

        $css .= $selector . ' .jtb-fullwidth-slider-pagination .jtb-slider-dot { '
            . 'width: 12px; '
            . 'height: 12px; '
            . 'border-radius: 50%; '
            . 'background: ' . $dotColor . '; '
            . 'cursor: pointer; '
            . 'transition: all 0.3s ease; '
            . '}' . "\n";

        $css .= $selector . ' .jtb-fullwidth-slider-pagination .jtb-slider-dot.active { background: ' . $dotActive . '; }' . "\n";

        // Responsive
        $css .= '@media (max-width: 980px) {' . "\n";
        $css .= '  ' . $selector . ' .jtb-slide-title { font-size: 36px; }' . "\n";
        $css .= '  ' . $selector . ' .jtb-slide-subtitle { font-size: 18px; }' . "\n";
        if (!empty($attrs['custom_height__tablet']) && $sliderHeight === 'custom') {
            $css .= '  ' . $selector . ' .jtb-fullwidth-slider-container { height: ' . $attrs['custom_height__tablet'] . 'px; }' . "\n";
        }
        $css .= '}' . "\n";

        $css .= '@media (max-width: 767px) {' . "\n";
        $css .= '  ' . $selector . ' .jtb-slide-title { font-size: 28px; }' . "\n";
        $css .= '  ' . $selector . ' .jtb-slide-subtitle { font-size: 16px; }' . "\n";
        $css .= '  ' . $selector . ' .jtb-fullwidth-slider-arrow { width: 40px; height: 40px; font-size: 24px; }' . "\n";
        if (!empty($attrs['custom_height__phone']) && $sliderHeight === 'custom') {
            $css .= '  ' . $selector . ' .jtb-fullwidth-slider-container { height: ' . $attrs['custom_height__phone'] . 'px; }' . "\n";
        }
        $css .= '}' . "\n";

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('fullwidth_slider', JTB_Module_FullwidthSlider::class);
