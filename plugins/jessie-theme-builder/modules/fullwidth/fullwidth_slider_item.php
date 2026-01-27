<?php
/**
 * Fullwidth Slider Item Module
 * Individual slide for fullwidth slider
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_FullwidthSliderItem extends JTB_Element
{
    public string $icon = 'slide';
    public string $category = 'fullwidth';
    public bool $is_child = true;

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = false;
    public bool $use_box_shadow = false;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = true;

    public function getSlug(): string
    {
        return 'fullwidth_slider_item';
    }

    public function getName(): string
    {
        return 'Fullwidth Slide';
    }

    public function getFields(): array
    {
        return [
            'heading' => [
                'label' => 'Heading',
                'type' => 'text',
                'default' => 'Your Slide Title'
            ],
            'subheading' => [
                'label' => 'Subheading',
                'type' => 'text'
            ],
            'content' => [
                'label' => 'Content',
                'type' => 'richtext'
            ],
            'button_text' => [
                'label' => 'Button Text',
                'type' => 'text',
                'default' => 'Learn More'
            ],
            'button_url' => [
                'label' => 'Button URL',
                'type' => 'text'
            ],
            'button_two_text' => [
                'label' => 'Second Button Text',
                'type' => 'text'
            ],
            'button_two_url' => [
                'label' => 'Second Button URL',
                'type' => 'text'
            ],
            'bg_image' => [
                'label' => 'Background Image',
                'type' => 'upload'
            ],
            'bg_overlay_color' => [
                'label' => 'Overlay Color',
                'type' => 'color',
                'default' => 'rgba(0,0,0,0.3)'
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
            'vertical_alignment' => [
                'label' => 'Vertical Alignment',
                'type' => 'select',
                'options' => [
                    'top' => 'Top',
                    'center' => 'Center',
                    'bottom' => 'Bottom'
                ],
                'default' => 'center'
            ],
            // Text colors
            'heading_color' => [
                'label' => 'Heading Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'subheading_color' => [
                'label' => 'Subheading Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'content_color' => [
                'label' => 'Content Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            // Heading size
            'heading_font_size' => [
                'label' => 'Heading Size',
                'type' => 'range',
                'min' => 20,
                'max' => 100,
                'unit' => 'px',
                'default' => 48,
                'responsive' => true
            ],
            // Buttons
            'button_bg_color' => [
                'label' => 'Button Background',
                'type' => 'color',
                'default' => '#2ea3f2',
                'hover' => true
            ],
            'button_text_color' => [
                'label' => 'Button Text Color',
                'type' => 'color',
                'default' => '#ffffff',
                'hover' => true
            ],
            'button_two_bg_color' => [
                'label' => 'Button 2 Background',
                'type' => 'color',
                'default' => 'transparent',
                'hover' => true
            ],
            'button_two_text_color' => [
                'label' => 'Button 2 Text Color',
                'type' => 'color',
                'default' => '#ffffff',
                'hover' => true
            ],
            'button_two_border_color' => [
                'label' => 'Button 2 Border',
                'type' => 'color',
                'default' => '#ffffff',
                'hover' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $heading = $this->esc($attrs['heading'] ?? 'Your Slide Title');
        $subheading = $this->esc($attrs['subheading'] ?? '');
        $bodyContent = $attrs['content'] ?? '';
        $btnText = $this->esc($attrs['button_text'] ?? '');
        $btnUrl = $attrs['button_url'] ?? '';
        $btn2Text = $this->esc($attrs['button_two_text'] ?? '');
        $btn2Url = $attrs['button_two_url'] ?? '';
        $bgImage = $attrs['bg_image'] ?? '';
        $alignment = $attrs['content_alignment'] ?? 'center';
        $vAlign = $attrs['vertical_alignment'] ?? 'center';

        $style = '';
        if (!empty($bgImage)) {
            $style = ' style="background-image: url(' . $this->esc($bgImage) . ');"';
        }

        $slideClass = 'jtb-fullwidth-slide jtb-slide-align-' . $alignment . ' jtb-slide-valign-' . $vAlign;

        $innerHtml = '<div class="' . $slideClass . '"' . $style . '>';
        $innerHtml .= '<div class="jtb-slide-overlay"></div>';
        $innerHtml .= '<div class="jtb-slide-content">';

        if (!empty($subheading)) {
            $innerHtml .= '<div class="jtb-slide-subheading">' . $subheading . '</div>';
        }

        if (!empty($heading)) {
            $innerHtml .= '<h2 class="jtb-slide-title">' . $heading . '</h2>';
        }

        if (!empty($bodyContent)) {
            $innerHtml .= '<div class="jtb-slide-description">' . $bodyContent . '</div>';
        }

        if (!empty($btnText) || !empty($btn2Text)) {
            $innerHtml .= '<div class="jtb-slide-buttons">';

            if (!empty($btnText) && !empty($btnUrl)) {
                $innerHtml .= '<a href="' . $this->esc($btnUrl) . '" class="jtb-button jtb-slide-button-one">' . $btnText . '</a>';
            }

            if (!empty($btn2Text) && !empty($btn2Url)) {
                $innerHtml .= '<a href="' . $this->esc($btn2Url) . '" class="jtb-button jtb-slide-button-two">' . $btn2Text . '</a>';
            }

            $innerHtml .= '</div>';
        }

        $innerHtml .= '</div>';
        $innerHtml .= '</div>';

        return $innerHtml;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        $overlayColor = $attrs['bg_overlay_color'] ?? 'rgba(0,0,0,0.3)';
        $headingColor = $attrs['heading_color'] ?? '#ffffff';
        $subheadingColor = $attrs['subheading_color'] ?? '#ffffff';
        $contentColor = $attrs['content_color'] ?? '#ffffff';
        $headingSize = $attrs['heading_font_size'] ?? 48;

        // Slide
        $css .= $selector . '.jtb-fullwidth-slide { '
            . 'flex: 0 0 100%; '
            . 'display: flex; '
            . 'background-size: cover; '
            . 'background-position: center; '
            . 'position: relative; '
            . 'min-height: 500px; '
            . '}' . "\n";

        // Overlay
        $css .= $selector . ' .jtb-slide-overlay { '
            . 'position: absolute; '
            . 'top: 0; left: 0; right: 0; bottom: 0; '
            . 'background: ' . $overlayColor . '; '
            . '}' . "\n";

        // Alignment
        $css .= $selector . '.jtb-slide-align-left { justify-content: flex-start; }' . "\n";
        $css .= $selector . '.jtb-slide-align-center { justify-content: center; }' . "\n";
        $css .= $selector . '.jtb-slide-align-right { justify-content: flex-end; }' . "\n";
        $css .= $selector . '.jtb-slide-valign-top { align-items: flex-start; }' . "\n";
        $css .= $selector . '.jtb-slide-valign-center { align-items: center; }' . "\n";
        $css .= $selector . '.jtb-slide-valign-bottom { align-items: flex-end; }' . "\n";

        // Content
        $css .= $selector . ' .jtb-slide-content { '
            . 'position: relative; '
            . 'z-index: 1; '
            . 'max-width: 800px; '
            . 'padding: 60px; '
            . '}' . "\n";

        $css .= $selector . '.jtb-slide-align-left .jtb-slide-content { text-align: left; }' . "\n";
        $css .= $selector . '.jtb-slide-align-center .jtb-slide-content { text-align: center; }' . "\n";
        $css .= $selector . '.jtb-slide-align-right .jtb-slide-content { text-align: right; }' . "\n";

        // Title
        $css .= $selector . ' .jtb-slide-title { '
            . 'color: ' . $headingColor . '; '
            . 'font-size: ' . $headingSize . 'px; '
            . 'margin: 0 0 20px; '
            . '}' . "\n";

        // Subheading
        $css .= $selector . ' .jtb-slide-subheading { '
            . 'color: ' . $subheadingColor . '; '
            . 'font-size: 18px; '
            . 'text-transform: uppercase; '
            . 'letter-spacing: 2px; '
            . 'margin-bottom: 15px; '
            . 'opacity: 0.9; '
            . '}' . "\n";

        // Description
        $css .= $selector . ' .jtb-slide-description { '
            . 'color: ' . $contentColor . '; '
            . 'font-size: 18px; '
            . 'margin-bottom: 30px; '
            . '}' . "\n";

        // Buttons
        $css .= $selector . ' .jtb-slide-buttons { display: flex; gap: 15px; flex-wrap: wrap; }' . "\n";
        $css .= $selector . '.jtb-slide-align-center .jtb-slide-buttons { justify-content: center; }' . "\n";
        $css .= $selector . '.jtb-slide-align-right .jtb-slide-buttons { justify-content: flex-end; }' . "\n";

        // Button one
        $btn1Bg = $attrs['button_bg_color'] ?? '#2ea3f2';
        $btn1Text = $attrs['button_text_color'] ?? '#ffffff';
        $css .= $selector . ' .jtb-slide-button-one { '
            . 'background: ' . $btn1Bg . '; '
            . 'color: ' . $btn1Text . '; '
            . 'padding: 15px 35px; '
            . 'text-decoration: none; '
            . 'transition: all 0.3s ease; '
            . '}' . "\n";

        if (!empty($attrs['button_bg_color__hover'])) {
            $css .= $selector . ' .jtb-slide-button-one:hover { background: ' . $attrs['button_bg_color__hover'] . '; }' . "\n";
        }

        // Button two
        $btn2Bg = $attrs['button_two_bg_color'] ?? 'transparent';
        $btn2Text = $attrs['button_two_text_color'] ?? '#ffffff';
        $btn2Border = $attrs['button_two_border_color'] ?? '#ffffff';
        $css .= $selector . ' .jtb-slide-button-two { '
            . 'background: ' . $btn2Bg . '; '
            . 'color: ' . $btn2Text . '; '
            . 'border: 2px solid ' . $btn2Border . '; '
            . 'padding: 13px 33px; '
            . 'text-decoration: none; '
            . 'transition: all 0.3s ease; '
            . '}' . "\n";

        if (!empty($attrs['button_two_bg_color__hover'])) {
            $css .= $selector . ' .jtb-slide-button-two:hover { background: ' . $attrs['button_two_bg_color__hover'] . '; }' . "\n";
        }

        // Responsive
        if (!empty($attrs['heading_font_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-slide-title { font-size: ' . $attrs['heading_font_size__tablet'] . 'px; } }' . "\n";
        }
        if (!empty($attrs['heading_font_size__phone'])) {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-slide-title { font-size: ' . $attrs['heading_font_size__phone'] . 'px; } }' . "\n";
        }

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('fullwidth_slider_item', JTB_Module_FullwidthSliderItem::class);
