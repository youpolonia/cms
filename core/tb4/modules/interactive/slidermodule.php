<?php
namespace Core\TB4\Modules\Interactive;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;

/**
 * TB 4.0 Slider Module
 * Image/content carousel with navigation arrows and dots
 */
class SliderModule extends Module
{
    public function __construct()
    {
        $this->name = 'Slider';
        $this->slug = 'slider';
        $this->icon = 'images';
        $this->category = 'interactive';
        $this->type = 'parent';
        $this->child_slug = 'slider_item';

        $this->elements = [
            'main' => '.tb4-slider',
            'track' => '.tb4-slider-track',
            'slide' => '.tb4-slide',
            'arrows' => '.tb4-slider-arrows',
            'dots' => '.tb4-slider-dots'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'slide1_image' => [
                'label' => 'Slide 1 Image URL',
                'type' => 'text',
                'default' => ''
            ],
            'slide1_title' => [
                'label' => 'Slide 1 Title',
                'type' => 'text',
                'default' => 'Welcome to Our Website'
            ],
            'slide1_text' => [
                'label' => 'Slide 1 Text',
                'type' => 'textarea',
                'default' => 'Create stunning websites with our powerful page builder.'
            ],
            'slide1_button_text' => [
                'label' => 'Slide 1 Button Text',
                'type' => 'text',
                'default' => 'Learn More'
            ],
            'slide1_button_url' => [
                'label' => 'Slide 1 Button URL',
                'type' => 'text',
                'default' => '#'
            ],
            'slide2_image' => [
                'label' => 'Slide 2 Image URL',
                'type' => 'text',
                'default' => ''
            ],
            'slide2_title' => [
                'label' => 'Slide 2 Title',
                'type' => 'text',
                'default' => 'Professional Design'
            ],
            'slide2_text' => [
                'label' => 'Slide 2 Text',
                'type' => 'textarea',
                'default' => 'Beautiful templates designed by professionals.'
            ],
            'slide2_button_text' => [
                'label' => 'Slide 2 Button Text',
                'type' => 'text',
                'default' => 'View Templates'
            ],
            'slide2_button_url' => [
                'label' => 'Slide 2 Button URL',
                'type' => 'text',
                'default' => '#'
            ],
            'slide3_image' => [
                'label' => 'Slide 3 Image URL',
                'type' => 'text',
                'default' => ''
            ],
            'slide3_title' => [
                'label' => 'Slide 3 Title',
                'type' => 'text',
                'default' => 'Get Started Today'
            ],
            'slide3_text' => [
                'label' => 'Slide 3 Text',
                'type' => 'textarea',
                'default' => 'Join thousands of satisfied customers worldwide.'
            ],
            'slide3_button_text' => [
                'label' => 'Slide 3 Button Text',
                'type' => 'text',
                'default' => 'Sign Up'
            ],
            'slide3_button_url' => [
                'label' => 'Slide 3 Button URL',
                'type' => 'text',
                'default' => '#'
            ],
            'show_arrows' => [
                'label' => 'Show Arrows',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_dots' => [
                'label' => 'Show Dots',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'autoplay' => [
                'label' => 'Autoplay',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'no'
            ],
            'autoplay_speed' => [
                'label' => 'Autoplay Speed',
                'type' => 'select',
                'options' => [
                    '3000' => '3 seconds',
                    '5000' => '5 seconds',
                    '7000' => '7 seconds',
                    '10000' => '10 seconds'
                ],
                'default' => '5000'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'slider_height' => [
                'label' => 'Slider Height',
                'type' => 'text',
                'default' => '400px'
            ],
            'slide_bg_color' => [
                'label' => 'Slide Background',
                'type' => 'color',
                'default' => '#1f2937'
            ],
            'overlay_color' => [
                'label' => 'Overlay Color',
                'type' => 'color',
                'default' => 'rgba(0,0,0,0.4)'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'text',
                'default' => '36px'
            ],
            'text_color' => [
                'label' => 'Text Color',
                'type' => 'color',
                'default' => '#e5e7eb'
            ],
            'text_font_size' => [
                'label' => 'Text Font Size',
                'type' => 'text',
                'default' => '16px'
            ],
            'button_bg_color' => [
                'label' => 'Button Background',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'button_text_color' => [
                'label' => 'Button Text Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'button_border_radius' => [
                'label' => 'Button Border Radius',
                'type' => 'text',
                'default' => '8px'
            ],
            'arrow_color' => [
                'label' => 'Arrow Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'arrow_bg_color' => [
                'label' => 'Arrow Background',
                'type' => 'color',
                'default' => 'rgba(0,0,0,0.3)'
            ],
            'dot_color' => [
                'label' => 'Dot Color',
                'type' => 'color',
                'default' => 'rgba(255,255,255,0.5)'
            ],
            'dot_active_color' => [
                'label' => 'Active Dot Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'content_alignment' => [
                'label' => 'Content Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'center'
            ],
            'border_radius' => [
                'label' => 'Border Radius',
                'type' => 'text',
                'default' => '0px'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    public function render(array $attrs): string
    {
        // Collect slides from individual fields
        $slides = [];
        for ($i = 1; $i <= 3; $i++) {
            $title = $attrs['slide' . $i . '_title'] ?? '';
            $text = $attrs['slide' . $i . '_text'] ?? '';
            $image = $attrs['slide' . $i . '_image'] ?? '';
            $buttonText = $attrs['slide' . $i . '_button_text'] ?? '';
            $buttonUrl = $attrs['slide' . $i . '_button_url'] ?? '#';

            if (!empty(trim($title)) || !empty(trim($text))) {
                $slides[] = [
                    'title' => $title,
                    'text' => $text,
                    'image' => $image,
                    'buttonText' => $buttonText,
                    'buttonUrl' => $buttonUrl
                ];
            }
        }

        // If no slides, use defaults
        if (empty($slides)) {
            $slides = [
                ['title' => 'Welcome to Our Website', 'text' => 'Create stunning websites with our powerful page builder.', 'image' => '', 'buttonText' => 'Learn More', 'buttonUrl' => '#'],
                ['title' => 'Professional Design', 'text' => 'Beautiful templates designed by professionals.', 'image' => '', 'buttonText' => 'View Templates', 'buttonUrl' => '#']
            ];
        }

        // Settings
        $showArrows = ($attrs['show_arrows'] ?? 'yes') !== 'no';
        $showDots = ($attrs['show_dots'] ?? 'yes') !== 'no';
        $autoplay = ($attrs['autoplay'] ?? 'no') === 'yes';
        $autoplaySpeed = $attrs['autoplay_speed'] ?? '5000';

        // Design settings
        $sliderHeight = $attrs['slider_height'] ?? '400px';
        $slideBgColor = $attrs['slide_bg_color'] ?? '#1f2937';
        $overlayColor = $attrs['overlay_color'] ?? 'rgba(0,0,0,0.4)';
        $titleColor = $attrs['title_color'] ?? '#ffffff';
        $titleFontSize = $attrs['title_font_size'] ?? '36px';
        $textColor = $attrs['text_color'] ?? '#e5e7eb';
        $textFontSize = $attrs['text_font_size'] ?? '16px';
        $buttonBgColor = $attrs['button_bg_color'] ?? '#2563eb';
        $buttonTextColor = $attrs['button_text_color'] ?? '#ffffff';
        $buttonBorderRadius = $attrs['button_border_radius'] ?? '8px';
        $arrowColor = $attrs['arrow_color'] ?? '#ffffff';
        $arrowBgColor = $attrs['arrow_bg_color'] ?? 'rgba(0,0,0,0.3)';
        $dotColor = $attrs['dot_color'] ?? 'rgba(255,255,255,0.5)';
        $dotActiveColor = $attrs['dot_active_color'] ?? '#ffffff';
        $contentAlignment = $attrs['content_alignment'] ?? 'center';
        $borderRadius = $attrs['border_radius'] ?? '0px';

        // Build HTML
        $html = '<div class="tb4-slider" data-current="0" style="position:relative;overflow:hidden;border-radius:' . esc_attr($borderRadius) . ';height:' . esc_attr($sliderHeight) . ';">';

        // Track with slides
        $html .= '<div class="tb4-slider-track" style="display:flex;height:100%;transition:transform 0.5s ease;">';

        foreach ($slides as $index => $slide) {
            $bgStyle = !empty($slide['image'])
                ? 'background-image:url(' . esc_attr($slide['image']) . ');background-size:cover;background-position:center;'
                : 'background:' . esc_attr($slideBgColor) . ';';

            $justifyContent = $contentAlignment === 'left' ? 'flex-start' : ($contentAlignment === 'right' ? 'flex-end' : 'center');

            $html .= '<div class="tb4-slide" data-index="' . esc_attr($index) . '" style="min-width:100%;position:relative;display:flex;align-items:center;justify-content:' . $justifyContent . ';' . $bgStyle . '">';
            $html .= '<div class="tb4-slide-overlay" style="position:absolute;inset:0;background:' . esc_attr($overlayColor) . ';"></div>';
            $html .= '<div class="tb4-slide-content" style="position:relative;z-index:2;padding:40px;text-align:' . esc_attr($contentAlignment) . ';max-width:800px;">';

            if (!empty($slide['title'])) {
                $html .= '<h2 class="tb4-slide-title" style="font-size:' . esc_attr($titleFontSize) . ';font-weight:700;color:' . esc_attr($titleColor) . ';margin:0 0 16px 0;">' . esc_html($slide['title']) . '</h2>';
            }

            if (!empty($slide['text'])) {
                $html .= '<p class="tb4-slide-text" style="font-size:' . esc_attr($textFontSize) . ';color:' . esc_attr($textColor) . ';margin:0 0 24px 0;line-height:1.6;">' . nl2br(esc_html($slide['text'])) . '</p>';
            }

            if (!empty($slide['buttonText'])) {
                $html .= '<a href="' . esc_attr($slide['buttonUrl']) . '" class="tb4-slide-button" style="display:inline-block;padding:12px 28px;background:' . esc_attr($buttonBgColor) . ';color:' . esc_attr($buttonTextColor) . ';text-decoration:none;border-radius:' . esc_attr($buttonBorderRadius) . ';font-weight:600;">' . esc_html($slide['buttonText']) . '</a>';
            }

            $html .= '</div></div>';
        }

        $html .= '</div>';

        // Arrows
        if ($showArrows && count($slides) > 1) {
            $html .= '<div class="tb4-slider-arrows" style="position:absolute;top:50%;left:0;right:0;transform:translateY(-50%);display:flex;justify-content:space-between;padding:0 16px;pointer-events:none;">';
            $html .= '<button class="tb4-slider-arrow tb4-slider-prev" style="width:44px;height:44px;border-radius:50%;background:' . esc_attr($arrowBgColor) . ';color:' . esc_attr($arrowColor) . ';border:none;cursor:pointer;pointer-events:auto;font-size:20px;">&lsaquo;</button>';
            $html .= '<button class="tb4-slider-arrow tb4-slider-next" style="width:44px;height:44px;border-radius:50%;background:' . esc_attr($arrowBgColor) . ';color:' . esc_attr($arrowColor) . ';border:none;cursor:pointer;pointer-events:auto;font-size:20px;">&rsaquo;</button>';
            $html .= '</div>';
        }

        // Dots
        if ($showDots && count($slides) > 1) {
            $html .= '<div class="tb4-slider-dots" style="position:absolute;bottom:20px;left:0;right:0;display:flex;justify-content:center;gap:8px;">';
            foreach ($slides as $index => $slide) {
                $dotBg = $index === 0 ? $dotActiveColor : $dotColor;
                $html .= '<button class="tb4-slider-dot' . ($index === 0 ? ' active' : '') . '" data-slide="' . esc_attr($index) . '" style="width:10px;height:10px;border-radius:50%;background:' . esc_attr($dotBg) . ';border:none;cursor:pointer;"></button>';
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}
