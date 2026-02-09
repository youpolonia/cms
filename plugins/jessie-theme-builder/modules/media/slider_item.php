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

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'slider_item';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'background_color' => [
            'property' => 'background-color',
            'selector' => ''
        ],
        'background_overlay' => [
            'property' => 'background-color',
            'selector' => '.jtb-slide-overlay'
        ],
        'text_color' => [
            'property' => 'color',
            'selector' => ''
        ],
        'heading_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-slide-heading',
            'unit' => 'px',
            'responsive' => true
        ],
        'button_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-slide-button',
            'hover' => true
        ],
        'button_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-slide-button',
            'hover' => true
        ],
        'button_border_color' => [
            'property' => 'border-color',
            'selector' => '.jtb-slide-button',
            'hover' => true
        ]
    ];

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
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

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

    /**
     * Generate CSS for Slider Item module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Slide base styles
        $css .= $selector . ' { background-size: cover; background-position: center; display: flex; padding: 40px; }' . "\n";

        // Overlay
        $css .= $selector . ' .jtb-slide-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; }' . "\n";

        // Content
        $css .= $selector . ' .jtb-slide-content { position: relative; z-index: 1; max-width: 800px; margin: auto; }' . "\n";

        // Alignment
        $css .= $selector . '.jtb-slide-align-left .jtb-slide-content { margin-left: 0; text-align: left; }' . "\n";
        $css .= $selector . '.jtb-slide-align-center .jtb-slide-content { text-align: center; }' . "\n";
        $css .= $selector . '.jtb-slide-align-right .jtb-slide-content { margin-right: 0; text-align: right; }' . "\n";

        // Vertical alignment
        $css .= $selector . '.jtb-slide-valign-top { align-items: flex-start; }' . "\n";
        $css .= $selector . '.jtb-slide-valign-center { align-items: center; }' . "\n";
        $css .= $selector . '.jtb-slide-valign-bottom { align-items: flex-end; }' . "\n";

        // Heading
        $css .= $selector . ' .jtb-slide-heading { margin-bottom: 20px; }' . "\n";

        // Description
        $css .= $selector . ' .jtb-slide-description { margin-bottom: 30px; }' . "\n";

        // Button base styles
        $css .= $selector . ' .jtb-slide-button { display: inline-block; border: 2px solid; padding: 12px 24px; text-decoration: none; transition: all 0.3s ease; }' . "\n";

        return $css;
    }
}

JTB_Registry::register('slider_item', JTB_Module_SliderItem::class);
