<?php
namespace Core\TB4\Modules\Fullwidth;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Fullwidth Slider Module
 *
 * Full-width image/content slider with navigation arrows and dots.
 * Supports multiple slides with titles, subtitles, and call-to-action buttons.
 */
class FwSliderModule extends Module
{
    protected array $content_fields = [];
    protected array $design_fields_custom = [];

    public function __construct()
    {
        $this->name = 'Fullwidth Slider';
        $this->slug = 'fw_slider';
        $this->icon = 'gallery-horizontal';
        $this->category = 'fullwidth';

        $this->elements = [
            'main' => '.tb4-fw-slider',
            'container' => '.tb4-fw-slider-container',
            'track' => '.tb4-fw-slider-track',
            'slide' => '.tb4-fw-slider-slide',
            'bg' => '.tb4-fw-slider-bg',
            'overlay' => '.tb4-fw-slider-overlay',
            'content' => '.tb4-fw-slider-content',
            'title' => '.tb4-fw-slider-title',
            'subtitle' => '.tb4-fw-slider-subtitle',
            'button' => '.tb4-fw-slider-btn',
            'arrows' => '.tb4-fw-slider-arrows',
            'arrow' => '.tb4-fw-slider-arrow',
            'dots' => '.tb4-fw-slider-dots',
            'dot' => '.tb4-fw-slider-dot'
        ];

        // Content fields
        $this->content_fields = [
            'slide1_image' => [
                'type' => 'text',
                'label' => 'Slide 1 Image URL',
                'default' => ''
            ],
            'slide1_title' => [
                'type' => 'text',
                'label' => 'Slide 1 Title',
                'default' => 'Welcome to Our Website'
            ],
            'slide1_subtitle' => [
                'type' => 'textarea',
                'label' => 'Slide 1 Subtitle',
                'default' => 'Creating amazing digital experiences for modern businesses.'
            ],
            'slide1_button_text' => [
                'type' => 'text',
                'label' => 'Slide 1 Button Text',
                'default' => 'Learn More'
            ],
            'slide1_button_url' => [
                'type' => 'text',
                'label' => 'Slide 1 Button URL',
                'default' => '#'
            ],
            'slide2_image' => [
                'type' => 'text',
                'label' => 'Slide 2 Image URL',
                'default' => ''
            ],
            'slide2_title' => [
                'type' => 'text',
                'label' => 'Slide 2 Title',
                'default' => 'Professional Services'
            ],
            'slide2_subtitle' => [
                'type' => 'textarea',
                'label' => 'Slide 2 Subtitle',
                'default' => 'Expert solutions tailored to your unique needs.'
            ],
            'slide2_button_text' => [
                'type' => 'text',
                'label' => 'Slide 2 Button Text',
                'default' => 'Get Started'
            ],
            'slide2_button_url' => [
                'type' => 'text',
                'label' => 'Slide 2 Button URL',
                'default' => '#'
            ],
            'slide3_image' => [
                'type' => 'text',
                'label' => 'Slide 3 Image URL',
                'default' => ''
            ],
            'slide3_title' => [
                'type' => 'text',
                'label' => 'Slide 3 Title',
                'default' => 'Contact Us Today'
            ],
            'slide3_subtitle' => [
                'type' => 'textarea',
                'label' => 'Slide 3 Subtitle',
                'default' => 'Let us help you achieve your goals.'
            ],
            'slide3_button_text' => [
                'type' => 'text',
                'label' => 'Slide 3 Button Text',
                'default' => 'Contact Us'
            ],
            'slide3_button_url' => [
                'type' => 'text',
                'label' => 'Slide 3 Button URL',
                'default' => '#'
            ],
            'show_arrows' => [
                'type' => 'select',
                'label' => 'Show Arrows',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'default' => 'yes'
            ],
            'show_dots' => [
                'type' => 'select',
                'label' => 'Show Dots',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'default' => 'yes'
            ],
            'autoplay' => [
                'type' => 'select',
                'label' => 'Autoplay',
                'options' => ['no' => 'No', 'yes' => 'Yes'],
                'default' => 'no'
            ],
            'autoplay_speed' => [
                'type' => 'select',
                'label' => 'Autoplay Speed',
                'options' => [
                    '3000' => '3 seconds',
                    '5000' => '5 seconds',
                    '7000' => '7 seconds',
                    '10000' => '10 seconds'
                ],
                'default' => '5000'
            ],
            'transition_effect' => [
                'type' => 'select',
                'label' => 'Transition Effect',
                'options' => ['slide' => 'Slide', 'fade' => 'Fade'],
                'default' => 'slide'
            ],
            'loop' => [
                'type' => 'select',
                'label' => 'Loop',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'default' => 'yes'
            ]
        ];

        // Design fields
        $this->design_fields_custom = [
            'slider_height' => [
                'type' => 'text',
                'label' => 'Slider Height',
                'default' => '100vh'
            ],
            'content_width' => [
                'type' => 'text',
                'label' => 'Content Max Width',
                'default' => '800px'
            ],
            'content_alignment' => [
                'type' => 'select',
                'label' => 'Content Alignment',
                'options' => ['center' => 'Center', 'left' => 'Left', 'right' => 'Right'],
                'default' => 'center'
            ],
            'vertical_alignment' => [
                'type' => 'select',
                'label' => 'Vertical Alignment',
                'options' => ['center' => 'Center', 'top' => 'Top', 'bottom' => 'Bottom'],
                'default' => 'center'
            ],
            'overlay_color' => [
                'type' => 'color',
                'label' => 'Overlay Color',
                'default' => 'rgba(0,0,0,0.4)'
            ],
            'title_color' => [
                'type' => 'color',
                'label' => 'Title Color',
                'default' => '#ffffff'
            ],
            'title_font_size' => [
                'type' => 'text',
                'label' => 'Title Font Size',
                'default' => '56px'
            ],
            'title_font_weight' => [
                'type' => 'select',
                'label' => 'Title Font Weight',
                'options' => [
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi Bold',
                    '700' => 'Bold',
                    '800' => 'Extra Bold'
                ],
                'default' => '700'
            ],
            'subtitle_color' => [
                'type' => 'color',
                'label' => 'Subtitle Color',
                'default' => 'rgba(255,255,255,0.9)'
            ],
            'subtitle_font_size' => [
                'type' => 'text',
                'label' => 'Subtitle Font Size',
                'default' => '20px'
            ],
            'button_bg_color' => [
                'type' => 'color',
                'label' => 'Button Background',
                'default' => '#2563eb'
            ],
            'button_text_color' => [
                'type' => 'color',
                'label' => 'Button Text Color',
                'default' => '#ffffff'
            ],
            'button_border_radius' => [
                'type' => 'text',
                'label' => 'Button Border Radius',
                'default' => '8px'
            ],
            'button_padding' => [
                'type' => 'text',
                'label' => 'Button Padding',
                'default' => '16px 32px'
            ],
            'arrow_color' => [
                'type' => 'color',
                'label' => 'Arrow Color',
                'default' => '#ffffff'
            ],
            'arrow_bg_color' => [
                'type' => 'color',
                'label' => 'Arrow Background',
                'default' => 'rgba(0,0,0,0.3)'
            ],
            'arrow_size' => [
                'type' => 'text',
                'label' => 'Arrow Size',
                'default' => '48px'
            ],
            'dot_color' => [
                'type' => 'color',
                'label' => 'Dot Color',
                'default' => 'rgba(255,255,255,0.5)'
            ],
            'dot_active_color' => [
                'type' => 'color',
                'label' => 'Active Dot Color',
                'default' => '#ffffff'
            ],
            'dot_size' => [
                'type' => 'text',
                'label' => 'Dot Size',
                'default' => '12px'
            ],
            'content_padding' => [
                'type' => 'text',
                'label' => 'Content Padding',
                'default' => '60px'
            ]
        ];

        // Advanced fields
        $this->advanced_fields = array_merge($this->advanced_fields, [
            'css_id' => [
                'type' => 'text',
                'label' => 'CSS ID',
                'default' => ''
            ],
            'css_class' => [
                'type' => 'text',
                'label' => 'CSS Class',
                'default' => ''
            ],
            'custom_css' => [
                'type' => 'textarea',
                'label' => 'Custom CSS',
                'default' => ''
            ]
        ]);
    }

    public function get_content_fields(): array
    {
        return $this->content_fields;
    }

    public function get_design_fields(): array
    {
        return array_merge(parent::get_design_fields(), $this->design_fields_custom);
    }

    public function render(array $attrs): string
    {
        // Content fields
        $slide1Image = $attrs['slide1_image'] ?? '';
        $slide1Title = $attrs['slide1_title'] ?? 'Welcome to Our Website';
        $slide1Subtitle = $attrs['slide1_subtitle'] ?? 'Creating amazing digital experiences for modern businesses.';
        $slide1ButtonText = $attrs['slide1_button_text'] ?? 'Learn More';
        $slide1ButtonUrl = $attrs['slide1_button_url'] ?? '#';

        $slide2Image = $attrs['slide2_image'] ?? '';
        $slide2Title = $attrs['slide2_title'] ?? 'Professional Services';
        $slide2Subtitle = $attrs['slide2_subtitle'] ?? 'Expert solutions tailored to your unique needs.';
        $slide2ButtonText = $attrs['slide2_button_text'] ?? 'Get Started';
        $slide2ButtonUrl = $attrs['slide2_button_url'] ?? '#';

        $slide3Image = $attrs['slide3_image'] ?? '';
        $slide3Title = $attrs['slide3_title'] ?? 'Contact Us Today';
        $slide3Subtitle = $attrs['slide3_subtitle'] ?? 'Let us help you achieve your goals.';
        $slide3ButtonText = $attrs['slide3_button_text'] ?? 'Contact Us';
        $slide3ButtonUrl = $attrs['slide3_button_url'] ?? '#';

        $showArrows = ($attrs['show_arrows'] ?? 'yes') === 'yes';
        $showDots = ($attrs['show_dots'] ?? 'yes') === 'yes';
        $autoplay = ($attrs['autoplay'] ?? 'no') === 'yes';
        $autoplaySpeed = $attrs['autoplay_speed'] ?? '5000';
        $transitionEffect = $attrs['transition_effect'] ?? 'slide';
        $loop = ($attrs['loop'] ?? 'yes') === 'yes';

        // Design fields
        $sliderHeight = $attrs['slider_height'] ?? '100vh';
        $contentWidth = $attrs['content_width'] ?? '800px';
        $contentAlignment = $attrs['content_alignment'] ?? 'center';
        $verticalAlignment = $attrs['vertical_alignment'] ?? 'center';
        $overlayColor = $attrs['overlay_color'] ?? 'rgba(0,0,0,0.4)';
        $titleColor = $attrs['title_color'] ?? '#ffffff';
        $titleFontSize = $attrs['title_font_size'] ?? '56px';
        $titleFontWeight = $attrs['title_font_weight'] ?? '700';
        $subtitleColor = $attrs['subtitle_color'] ?? 'rgba(255,255,255,0.9)';
        $subtitleFontSize = $attrs['subtitle_font_size'] ?? '20px';
        $buttonBgColor = $attrs['button_bg_color'] ?? '#2563eb';
        $buttonTextColor = $attrs['button_text_color'] ?? '#ffffff';
        $buttonBorderRadius = $attrs['button_border_radius'] ?? '8px';
        $buttonPadding = $attrs['button_padding'] ?? '16px 32px';
        $arrowColor = $attrs['arrow_color'] ?? '#ffffff';
        $arrowBgColor = $attrs['arrow_bg_color'] ?? 'rgba(0,0,0,0.3)';
        $arrowSize = $attrs['arrow_size'] ?? '48px';
        $dotColor = $attrs['dot_color'] ?? 'rgba(255,255,255,0.5)';
        $dotActiveColor = $attrs['dot_active_color'] ?? '#ffffff';
        $dotSize = $attrs['dot_size'] ?? '12px';
        $contentPadding = $attrs['content_padding'] ?? '60px';

        // Advanced fields
        $cssId = $attrs['css_id'] ?? '';
        $cssClass = $attrs['css_class'] ?? '';

        // Build slides array
        $slides = [];
        if ($slide1Title || $slide1Image) {
            $slides[] = [
                'image' => $slide1Image,
                'title' => $slide1Title,
                'subtitle' => $slide1Subtitle,
                'button_text' => $slide1ButtonText,
                'button_url' => $slide1ButtonUrl
            ];
        }
        if ($slide2Title || $slide2Image) {
            $slides[] = [
                'image' => $slide2Image,
                'title' => $slide2Title,
                'subtitle' => $slide2Subtitle,
                'button_text' => $slide2ButtonText,
                'button_url' => $slide2ButtonUrl
            ];
        }
        if ($slide3Title || $slide3Image) {
            $slides[] = [
                'image' => $slide3Image,
                'title' => $slide3Title,
                'subtitle' => $slide3Subtitle,
                'button_text' => $slide3ButtonText,
                'button_url' => $slide3ButtonUrl
            ];
        }

        // Default slide if none configured
        if (empty($slides)) {
            $slides[] = [
                'image' => '',
                'title' => 'Welcome to Our Website',
                'subtitle' => 'Creating amazing digital experiences.',
                'button_text' => 'Learn More',
                'button_url' => '#'
            ];
        }

        // Gradient backgrounds for placeholder slides
        $gradients = [
            'linear-gradient(135deg, #1e3a8a 0%, #7c3aed 100%)',
            'linear-gradient(135deg, #065f46 0%, #10b981 100%)',
            'linear-gradient(135deg, #9f1239 0%, #f43f5e 100%)'
        ];

        // Alignment styles
        $justifyContent = $verticalAlignment === 'top' ? 'flex-start' : ($verticalAlignment === 'bottom' ? 'flex-end' : 'center');
        $alignItems = $contentAlignment === 'left' ? 'flex-start' : ($contentAlignment === 'right' ? 'flex-end' : 'center');
        $textAlign = $contentAlignment;

        // Container ID/Class
        $idAttr = $cssId ? ' id="' . esc_attr($cssId) . '"' : '';
        $classAttr = 'tb4-fw-slider' . ($cssClass ? ' ' . esc_attr($cssClass) : '');

        // Unique ID for this slider instance
        $sliderId = 'fw-slider-' . uniqid();

        // Build HTML
        $html = '<div' . $idAttr . ' class="' . $classAttr . '" data-slider-id="' . $sliderId . '" data-autoplay="' . ($autoplay ? 'true' : 'false') . '" data-speed="' . esc_attr($autoplaySpeed) . '" data-loop="' . ($loop ? 'true' : 'false') . '" data-effect="' . esc_attr($transitionEffect) . '">';
        $html .= '<div class="tb4-fw-slider-container" style="position:relative;width:100%;overflow:hidden;">';
        $html .= '<div class="tb4-fw-slider-track" style="display:flex;transition:transform 0.5s ease;">';

        foreach ($slides as $index => $slide) {
            $activeClass = $index === 0 ? ' active' : '';
            $bgStyle = $slide['image']
                ? 'background-image:url(' . esc_attr($slide['image']) . ');background-size:cover;background-position:center;'
                : 'background:' . $gradients[$index % count($gradients)] . ';';

            $html .= '<div class="tb4-fw-slider-slide' . $activeClass . '" data-slide-index="' . $index . '" style="flex:0 0 100%;position:relative;min-height:' . esc_attr($sliderHeight) . ';display:flex;align-items:' . $justifyContent . ';justify-content:center;">';

            // Background
            $html .= '<div class="tb4-fw-slider-bg" style="position:absolute;inset:0;' . $bgStyle . '"></div>';

            // Overlay
            $html .= '<div class="tb4-fw-slider-overlay" style="position:absolute;inset:0;background:' . esc_attr($overlayColor) . ';"></div>';

            // Content
            $html .= '<div class="tb4-fw-slider-content" style="position:relative;z-index:2;text-align:' . esc_attr($textAlign) . ';padding:' . esc_attr($contentPadding) . ';max-width:' . esc_attr($contentWidth) . ';width:100%;display:flex;flex-direction:column;align-items:' . $alignItems . ';">';

            $html .= '<h2 class="tb4-fw-slider-title" style="font-size:' . esc_attr($titleFontSize) . ';font-weight:' . esc_attr($titleFontWeight) . ';color:' . esc_attr($titleColor) . ';margin:0 0 20px 0;line-height:1.2;">' . esc_html($slide['title']) . '</h2>';

            $html .= '<p class="tb4-fw-slider-subtitle" style="font-size:' . esc_attr($subtitleFontSize) . ';color:' . esc_attr($subtitleColor) . ';margin:0 0 32px 0;line-height:1.6;">' . esc_html($slide['subtitle']) . '</p>';

            if ($slide['button_text']) {
                $html .= '<a href="' . esc_attr($slide['button_url']) . '" class="tb4-fw-slider-btn" style="display:inline-block;padding:' . esc_attr($buttonPadding) . ';background:' . esc_attr($buttonBgColor) . ';color:' . esc_attr($buttonTextColor) . ';text-decoration:none;border-radius:' . esc_attr($buttonBorderRadius) . ';font-size:16px;font-weight:600;transition:all 0.2s;">' . esc_html($slide['button_text']) . '</a>';
            }

            $html .= '</div></div>';
        }

        $html .= '</div>'; // end track

        // Navigation arrows
        if ($showArrows) {
            $html .= '<div class="tb4-fw-slider-arrows" style="position:absolute;top:50%;left:0;right:0;transform:translateY(-50%);display:flex;justify-content:space-between;padding:0 20px;pointer-events:none;z-index:10;">';
            $html .= '<button class="tb4-fw-slider-arrow tb4-fw-slider-prev" data-dir="prev" style="width:' . esc_attr($arrowSize) . ';height:' . esc_attr($arrowSize) . ';border-radius:50%;background:' . esc_attr($arrowBgColor) . ';border:none;color:' . esc_attr($arrowColor) . ';cursor:pointer;display:flex;align-items:center;justify-content:center;pointer-events:auto;transition:all 0.2s;"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg></button>';
            $html .= '<button class="tb4-fw-slider-arrow tb4-fw-slider-next" data-dir="next" style="width:' . esc_attr($arrowSize) . ';height:' . esc_attr($arrowSize) . ';border-radius:50%;background:' . esc_attr($arrowBgColor) . ';border:none;color:' . esc_attr($arrowColor) . ';cursor:pointer;display:flex;align-items:center;justify-content:center;pointer-events:auto;transition:all 0.2s;"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg></button>';
            $html .= '</div>';
        }

        // Dots
        if ($showDots) {
            $html .= '<div class="tb4-fw-slider-dots" style="position:absolute;bottom:24px;left:50%;transform:translateX(-50%);display:flex;gap:10px;z-index:10;">';
            foreach ($slides as $index => $slide) {
                $dotBg = $index === 0 ? $dotActiveColor : $dotColor;
                $html .= '<button class="tb4-fw-slider-dot' . ($index === 0 ? ' active' : '') . '" data-slide="' . $index . '" style="width:' . esc_attr($dotSize) . ';height:' . esc_attr($dotSize) . ';border-radius:50%;background:' . esc_attr($dotBg) . ';border:none;cursor:pointer;transition:all 0.2s;"></button>';
            }
            $html .= '</div>';
        }

        $html .= '</div></div>'; // end container and main

        return $html;
    }
}
