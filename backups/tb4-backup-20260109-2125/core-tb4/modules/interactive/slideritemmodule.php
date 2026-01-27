<?php
namespace Core\TB4\Modules\Interactive;

require_once __DIR__ . '/../childmodule.php';

use Core\TB4\Modules\ChildModule;

/**
 * TB 4.0 Slider Item Module (Child Module)
 * Individual slide with background, heading, text, and button.
 * Must be nested inside SliderModule parent.
 */
class SliderItemModule extends ChildModule
{
    protected ?string $parent_slug = 'slider';
    protected ?string $child_title_var = 'heading';

    public function __construct()
    {
        $this->name = 'Slide';
        $this->slug = 'slider_item';
        $this->icon = 'image';
        $this->category = 'interactive';
    }

    public function get_content_fields(): array
    {
        return [
            'heading' => [
                'type' => 'text',
                'label' => 'Heading',
                'default' => 'Slide Title'
            ],
            'subheading' => [
                'type' => 'text',
                'label' => 'Subheading',
                'default' => 'Slide subtitle or description'
            ],
            'content' => [
                'type' => 'textarea',
                'label' => 'Content Text',
                'default' => ''
            ],
            'button_text' => [
                'type' => 'text',
                'label' => 'Button Text',
                'default' => 'Learn More'
            ],
            'button_url' => [
                'type' => 'text',
                'label' => 'Button URL',
                'default' => '#'
            ],
            'button_target' => [
                'type' => 'select',
                'label' => 'Button Target',
                'options' => [
                    '_self' => 'Same Window',
                    '_blank' => 'New Tab'
                ],
                'default' => '_self'
            ],
            'background_image' => [
                'type' => 'image',
                'label' => 'Background Image',
                'default' => ''
            ],
            'background_color' => [
                'type' => 'color',
                'label' => 'Background Color',
                'default' => '#1e3a5f'
            ],
            'overlay_enabled' => [
                'type' => 'toggle',
                'label' => 'Enable Overlay',
                'default' => true
            ],
            'overlay_color' => [
                'type' => 'color',
                'label' => 'Overlay Color',
                'default' => 'rgba(0,0,0,0.4)'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'min_height' => [
                'type' => 'text',
                'label' => 'Minimum Height',
                'default' => '500px'
            ],
            'content_alignment' => [
                'type' => 'select',
                'label' => 'Content Alignment',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'center'
            ],
            'content_vertical' => [
                'type' => 'select',
                'label' => 'Vertical Position',
                'options' => [
                    'top' => 'Top',
                    'center' => 'Center',
                    'bottom' => 'Bottom'
                ],
                'default' => 'center'
            ],
            'content_max_width' => [
                'type' => 'text',
                'label' => 'Content Max Width',
                'default' => '800px'
            ],
            'heading_font_size' => [
                'type' => 'text',
                'label' => 'Heading Font Size',
                'default' => '48px'
            ],
            'heading_font_weight' => [
                'type' => 'select',
                'label' => 'Heading Font Weight',
                'options' => [
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi Bold',
                    '700' => 'Bold',
                    '800' => 'Extra Bold'
                ],
                'default' => '700'
            ],
            'heading_color' => [
                'type' => 'color',
                'label' => 'Heading Color',
                'default' => '#ffffff'
            ],
            'subheading_font_size' => [
                'type' => 'text',
                'label' => 'Subheading Font Size',
                'default' => '20px'
            ],
            'subheading_color' => [
                'type' => 'color',
                'label' => 'Subheading Color',
                'default' => '#e5e7eb'
            ],
            'content_font_size' => [
                'type' => 'text',
                'label' => 'Content Font Size',
                'default' => '16px'
            ],
            'content_color' => [
                'type' => 'color',
                'label' => 'Content Color',
                'default' => '#d1d5db'
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
                'default' => '14px 32px'
            ],
            'padding' => [
                'type' => 'text',
                'label' => 'Content Padding',
                'default' => '60px 40px'
            ]
        ];
    }

    public function render(array $attrs): string
    {
        // Content fields
        $heading = $attrs['heading'] ?? 'Slide Title';
        $subheading = $attrs['subheading'] ?? 'Slide subtitle or description';
        $content = $attrs['content'] ?? '';
        $buttonText = $attrs['button_text'] ?? 'Learn More';
        $buttonUrl = $attrs['button_url'] ?? '#';
        $buttonTarget = $attrs['button_target'] ?? '_self';
        $backgroundImage = $attrs['background_image'] ?? '';
        $backgroundColor = $attrs['background_color'] ?? '#1e3a5f';
        $overlayEnabled = $attrs['overlay_enabled'] ?? true;
        $overlayColor = $attrs['overlay_color'] ?? 'rgba(0,0,0,0.4)';

        // Design fields
        $minHeight = $attrs['min_height'] ?? '500px';
        $contentAlignment = $attrs['content_alignment'] ?? 'center';
        $contentVertical = $attrs['content_vertical'] ?? 'center';
        $contentMaxWidth = $attrs['content_max_width'] ?? '800px';
        $headingFontSize = $attrs['heading_font_size'] ?? '48px';
        $headingFontWeight = $attrs['heading_font_weight'] ?? '700';
        $headingColor = $attrs['heading_color'] ?? '#ffffff';
        $subheadingFontSize = $attrs['subheading_font_size'] ?? '20px';
        $subheadingColor = $attrs['subheading_color'] ?? '#e5e7eb';
        $contentFontSize = $attrs['content_font_size'] ?? '16px';
        $contentColor = $attrs['content_color'] ?? '#d1d5db';
        $buttonBgColor = $attrs['button_bg_color'] ?? '#2563eb';
        $buttonTextColor = $attrs['button_text_color'] ?? '#ffffff';
        $buttonBorderRadius = $attrs['button_border_radius'] ?? '8px';
        $buttonPadding = $attrs['button_padding'] ?? '14px 32px';
        $padding = $attrs['padding'] ?? '60px 40px';

        // Determine if active (first item by default)
        $isActive = !empty($attrs['_is_active']);
        $activeClass = $isActive ? ' active' : '';
        $displayStyle = $isActive ? '' : 'display:none;';

        // Build background style
        $bgStyle = !empty($backgroundImage)
            ? 'background-image:url(' . esc_attr($backgroundImage) . ');background-size:cover;background-position:center;'
            : 'background:' . esc_attr($backgroundColor) . ';';

        // Flex alignment for horizontal
        $justifyContent = $contentAlignment === 'left' ? 'flex-start' : ($contentAlignment === 'right' ? 'flex-end' : 'center');

        // Flex alignment for vertical
        $alignItems = $contentVertical === 'top' ? 'flex-start' : ($contentVertical === 'bottom' ? 'flex-end' : 'center');

        // Build slide wrapper style
        $slideStyle = 'position:relative;min-height:' . esc_attr($minHeight) . ';';
        $slideStyle .= 'display:flex;justify-content:' . $justifyContent . ';align-items:' . $alignItems . ';';
        $slideStyle .= $bgStyle . $displayStyle;

        $html = '<div class="tb4-slider-item' . $activeClass . '" style="' . $slideStyle . '">';

        // Overlay
        if ($overlayEnabled) {
            $html .= '<div class="tb4-slider-item-overlay" style="position:absolute;inset:0;background:' . esc_attr($overlayColor) . ';pointer-events:none;"></div>';
        }

        // Content wrapper
        $contentStyle = 'position:relative;z-index:2;padding:' . esc_attr($padding) . ';';
        $contentStyle .= 'text-align:' . esc_attr($contentAlignment) . ';max-width:' . esc_attr($contentMaxWidth) . ';';
        $html .= '<div class="tb4-slider-item-content" style="' . $contentStyle . '">';

        // Heading
        if (!empty($heading)) {
            $headingStyle = 'font-size:' . esc_attr($headingFontSize) . ';font-weight:' . esc_attr($headingFontWeight) . ';';
            $headingStyle .= 'color:' . esc_attr($headingColor) . ';margin:0 0 16px 0;';
            $html .= '<h2 class="tb4-slider-item-heading" style="' . $headingStyle . '">' . esc_html($heading) . '</h2>';
        }

        // Subheading
        if (!empty($subheading)) {
            $subheadingStyle = 'font-size:' . esc_attr($subheadingFontSize) . ';color:' . esc_attr($subheadingColor) . ';';
            $subheadingStyle .= 'margin:0 0 20px 0;line-height:1.5;';
            $html .= '<p class="tb4-slider-item-subheading" style="' . $subheadingStyle . '">' . esc_html($subheading) . '</p>';
        }

        // Content text
        if (!empty($content)) {
            $textStyle = 'font-size:' . esc_attr($contentFontSize) . ';color:' . esc_attr($contentColor) . ';';
            $textStyle .= 'margin:0 0 24px 0;line-height:1.6;';
            $html .= '<div class="tb4-slider-item-text" style="' . $textStyle . '">' . nl2br(esc_html($content)) . '</div>';
        }

        // Button
        if (!empty($buttonText)) {
            $buttonStyle = 'display:inline-block;padding:' . esc_attr($buttonPadding) . ';';
            $buttonStyle .= 'background:' . esc_attr($buttonBgColor) . ';color:' . esc_attr($buttonTextColor) . ';';
            $buttonStyle .= 'border-radius:' . esc_attr($buttonBorderRadius) . ';text-decoration:none;font-weight:600;';
            $buttonStyle .= 'transition:opacity 0.2s,transform 0.2s;';
            $html .= '<a href="' . esc_attr($buttonUrl) . '" target="' . esc_attr($buttonTarget) . '" class="tb4-slider-item-button" style="' . $buttonStyle . '">' . esc_html($buttonText) . '</a>';
        }

        $html .= '</div>'; // Close content wrapper
        $html .= '</div>'; // Close slide wrapper

        return $html;
    }
}
