<?php
/**
 * Slider Item Module (Child)
 * Single slide with content
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_SliderItem extends JTB_Element
{
    public string $icon = 'slide';
    public string $category = 'media';
    public bool $is_child = true;

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = false;
    public bool $use_box_shadow = false;
    public bool $use_animation = false;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    public function getSlug(): string
    {
        return 'slider_item';
    }

    public function getName(): string
    {
        return 'Slide';
    }

    public function getFields(): array
    {
        return [
            'heading' => [
                'label' => 'Heading',
                'type' => 'text',
                'default' => 'Slide Title'
            ],
            'content' => [
                'label' => 'Content',
                'type' => 'richtext',
                'default' => '<p>Your slide content goes here.</p>'
            ],
            'button_text' => [
                'label' => 'Button Text',
                'type' => 'text'
            ],
            'button_link' => [
                'label' => 'Button URL',
                'type' => 'text'
            ],
            'button_link_new_window' => [
                'label' => 'Open in New Tab',
                'type' => 'toggle',
                'default' => false
            ],
            'background_image' => [
                'label' => 'Background Image',
                'type' => 'upload'
            ],
            'background_color' => [
                'label' => 'Background Color',
                'type' => 'color',
                'default' => '#2ea3f2'
            ],
            'background_overlay' => [
                'label' => 'Background Overlay',
                'type' => 'color',
                'default' => 'rgba(0,0,0,0.3)'
            ],
            'text_color' => [
                'label' => 'Text Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'heading_font_size' => [
                'label' => 'Heading Font Size',
                'type' => 'range',
                'min' => 20,
                'max' => 100,
                'unit' => 'px',
                'default' => 46,
                'responsive' => true
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
            'content_vertical_align' => [
                'label' => 'Vertical Position',
                'type' => 'select',
                'options' => [
                    'top' => 'Top',
                    'center' => 'Center',
                    'bottom' => 'Bottom'
                ],
                'default' => 'center'
            ],
            'button_bg_color' => [
                'label' => 'Button Background',
                'type' => 'color',
                'default' => 'transparent',
                'hover' => true
            ],
            'button_text_color' => [
                'label' => 'Button Text Color',
                'type' => 'color',
                'default' => '#ffffff',
                'hover' => true
            ],
            'button_border_color' => [
                'label' => 'Button Border Color',
                'type' => 'color',
                'default' => '#ffffff',
                'hover' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $heading = $this->esc($attrs['heading'] ?? 'Slide Title');
        $bodyContent = $attrs['content'] ?? '';
        $buttonText = $this->esc($attrs['button_text'] ?? '');
        $buttonUrl = $attrs['button_link'] ?? '';
        $newWindow = !empty($attrs['button_link_new_window']) ? ' target="_blank" rel="noopener"' : '';
        $bgImage = $attrs['background_image'] ?? '';
        $alignment = $attrs['content_alignment'] ?? 'center';
        $verticalAlign = $attrs['content_vertical_align'] ?? 'center';

        $style = '';
        if (!empty($bgImage)) {
            $style = ' style="background-image: url(' . $this->esc($bgImage) . ');"';
        }

        $html = '<div class="jtb-slider-slide jtb-slide-align-' . $alignment . ' jtb-slide-valign-' . $verticalAlign . '"' . $style . '>';
        $html .= '<div class="jtb-slide-overlay"></div>';
        $html .= '<div class="jtb-slide-content">';

        if (!empty($heading)) {
            $html .= '<h2 class="jtb-slide-heading">' . $heading . '</h2>';
        }

        if (!empty($bodyContent)) {
            $html .= '<div class="jtb-slide-description">' . $bodyContent . '</div>';
        }

        if (!empty($buttonText) && !empty($buttonUrl)) {
            $html .= '<a class="jtb-slide-button jtb-button" href="' . $this->esc($buttonUrl) . '"' . $newWindow . '>' . $buttonText . '</a>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        $bgColor = $attrs['background_color'] ?? '#2ea3f2';
        $overlayColor = $attrs['background_overlay'] ?? 'rgba(0,0,0,0.3)';
        $textColor = $attrs['text_color'] ?? '#ffffff';
        $headingSize = $attrs['heading_font_size'] ?? 46;

        // Slide background
        $css .= $selector . ' { ';
        $css .= 'background-color: ' . $bgColor . '; ';
        $css .= 'background-size: cover; ';
        $css .= 'background-position: center; ';
        $css .= 'color: ' . $textColor . '; ';
        $css .= 'display: flex; ';
        $css .= 'padding: 40px; ';
        $css .= '}' . "\n";

        // Overlay
        $css .= $selector . ' .jtb-slide-overlay { ';
        $css .= 'position: absolute; ';
        $css .= 'top: 0; left: 0; right: 0; bottom: 0; ';
        $css .= 'background-color: ' . $overlayColor . '; ';
        $css .= '}' . "\n";

        // Content
        $css .= $selector . ' .jtb-slide-content { ';
        $css .= 'position: relative; ';
        $css .= 'z-index: 1; ';
        $css .= 'max-width: 800px; ';
        $css .= 'margin: auto; ';
        $css .= '}' . "\n";

        // Alignment
        $css .= $selector . '.jtb-slide-align-left .jtb-slide-content { margin-left: 0; text-align: left; }' . "\n";
        $css .= $selector . '.jtb-slide-align-center .jtb-slide-content { text-align: center; }' . "\n";
        $css .= $selector . '.jtb-slide-align-right .jtb-slide-content { margin-right: 0; text-align: right; }' . "\n";

        // Vertical alignment
        $css .= $selector . '.jtb-slide-valign-top { align-items: flex-start; }' . "\n";
        $css .= $selector . '.jtb-slide-valign-center { align-items: center; }' . "\n";
        $css .= $selector . '.jtb-slide-valign-bottom { align-items: flex-end; }' . "\n";

        // Heading
        $css .= $selector . ' .jtb-slide-heading { ';
        $css .= 'font-size: ' . $headingSize . 'px; ';
        $css .= 'margin-bottom: 20px; ';
        $css .= '}' . "\n";

        // Description
        $css .= $selector . ' .jtb-slide-description { margin-bottom: 30px; }' . "\n";

        // Button
        $btnBg = $attrs['button_bg_color'] ?? 'transparent';
        $btnText = $attrs['button_text_color'] ?? '#ffffff';
        $btnBorder = $attrs['button_border_color'] ?? '#ffffff';

        $css .= $selector . ' .jtb-slide-button { ';
        $css .= 'display: inline-block; ';
        $css .= 'background-color: ' . $btnBg . '; ';
        $css .= 'color: ' . $btnText . '; ';
        $css .= 'border: 2px solid ' . $btnBorder . '; ';
        $css .= 'padding: 12px 24px; ';
        $css .= 'text-decoration: none; ';
        $css .= 'transition: all 0.3s ease; ';
        $css .= '}' . "\n";

        // Button hover
        if (!empty($attrs['button_bg_color__hover'])) {
            $css .= $selector . ' .jtb-slide-button:hover { background-color: ' . $attrs['button_bg_color__hover'] . '; }' . "\n";
        }
        if (!empty($attrs['button_text_color__hover'])) {
            $css .= $selector . ' .jtb-slide-button:hover { color: ' . $attrs['button_text_color__hover'] . '; }' . "\n";
        }
        if (!empty($attrs['button_border_color__hover'])) {
            $css .= $selector . ' .jtb-slide-button:hover { border-color: ' . $attrs['button_border_color__hover'] . '; }' . "\n";
        }

        // Responsive heading
        if (!empty($attrs['heading_font_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-slide-heading { font-size: ' . $attrs['heading_font_size__tablet'] . 'px; } }' . "\n";
        }
        if (!empty($attrs['heading_font_size__phone'])) {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-slide-heading { font-size: ' . $attrs['heading_font_size__phone'] . 'px; } }' . "\n";
        }

        return $css;
    }
}

JTB_Registry::register('slider_item', JTB_Module_SliderItem::class);
