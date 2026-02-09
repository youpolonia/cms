<?php
/**
 * CTA (Call to Action) Module
 * Call to action box with title, description and button
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Cta extends JTB_Element
{
    public string $icon = 'megaphone';
    public string $category = 'content';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = true;
    public bool $use_position = false;
    public bool $use_filters = false;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'cta';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        // Text alignment
        'text_orientation' => [
            'property' => 'text-align',
            'selector' => '.jtb-cta-container',
            'responsive' => true
        ],
        // Background
        'promo_color' => [
            'property' => 'background-color',
            'selector' => '',  // applies to module root
            'hover' => true
        ],
        // Button
        'button_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-cta-button',
            'hover' => true
        ],
        'button_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-cta-button',
            'hover' => true
        ],
        'button_border_color' => [
            'property' => 'border-color',
            'selector' => '.jtb-cta-button',
            'hover' => true
        ],
        'button_border_width' => [
            'property' => 'border-width',
            'selector' => '.jtb-cta-button',
            'unit' => 'px'
        ],
        'button_border_radius' => [
            'property' => 'border-radius',
            'selector' => '.jtb-cta-button',
            'unit' => 'px'
        ],
        // Title
        'title_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-cta-title',
            'unit' => 'px',
            'responsive' => true
        ],
        'title_color' => [
            'property' => 'color',
            'selector' => '.jtb-cta-title'
        ],
        // Content
        'content_color' => [
            'property' => 'color',
            'selector' => '.jtb-cta-content'
        ]
    ];

    public function getSlug(): string
    {
        return 'cta';
    }

    public function getName(): string
    {
        return 'Call To Action';
    }

    public function getFields(): array
    {
        return [
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Call To Action'
            ],
            'content' => [
                'label' => 'Body',
                'type' => 'richtext',
                'default' => '<p>Your content goes here. Edit or remove this text inline.</p>'
            ],
            'button_text' => [
                'label' => 'Button Text',
                'type' => 'text',
                'default' => 'Click Here'
            ],
            'link_url' => [
                'label' => 'Button Link URL',
                'type' => 'text'
            ],
            'link_target' => [
                'label' => 'Open in New Tab',
                'type' => 'toggle',
                'default' => false
            ],
            'header_level' => [
                'label' => 'Title Heading Level',
                'type' => 'select',
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6'
                ],
                'default' => 'h2'
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
            // Button styling
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
            'button_border_width' => [
                'label' => 'Button Border Width',
                'type' => 'range',
                'min' => 0,
                'max' => 10,
                'unit' => 'px',
                'default' => 2
            ],
            'button_border_color' => [
                'label' => 'Button Border Color',
                'type' => 'color',
                'default' => '#2ea3f2',
                'hover' => true
            ],
            'button_border_radius' => [
                'label' => 'Button Border Radius',
                'type' => 'range',
                'min' => 0,
                'max' => 50,
                'unit' => 'px',
                'default' => 3
            ],
            'button_icon' => [
                'label' => 'Button Icon',
                'type' => 'icon_select'
            ],
            'button_icon_placement' => [
                'label' => 'Icon Placement',
                'type' => 'select',
                'options' => [
                    'right' => 'Right',
                    'left' => 'Left'
                ],
                'default' => 'right'
            ],
            'use_background_color' => [
                'label' => 'Use Background Color',
                'type' => 'toggle',
                'default' => true
            ],
            'promo_color' => [
                'label' => 'Background Color',
                'type' => 'color',
                'default' => '#7ebec5',
                'show_if' => ['use_background_color' => true],
                'hover' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $title = $this->esc($attrs['title'] ?? 'Call To Action');
        $bodyContent = $attrs['content'] ?? '';
        $buttonText = $this->esc($attrs['button_text'] ?? 'Click Here');
        $buttonUrl = $attrs['link_url'] ?? '#';
        $newWindow = !empty($attrs['link_target']) ? ' target="_blank" rel="noopener"' : '';
        $headerLevel = $attrs['header_level'] ?? 'h2';
        $buttonIcon = $attrs['button_icon'] ?? '';
        $iconPlacement = $attrs['button_icon_placement'] ?? 'right';

        // Button icon
        $iconHtml = '';
        if (!empty($buttonIcon)) {
            $iconHtml = '<span class="jtb-button-icon jtb-icon-' . $this->esc($buttonIcon) . '"></span>';
        }

        // Button HTML
        $buttonHtml = '<a class="jtb-cta-button jtb-button" href="' . $this->esc($buttonUrl) . '"' . $newWindow . '>';
        if ($iconPlacement === 'left' && !empty($iconHtml)) {
            $buttonHtml .= $iconHtml;
        }
        $buttonHtml .= '<span class="jtb-button-text">' . $buttonText . '</span>';
        if ($iconPlacement === 'right' && !empty($iconHtml)) {
            $buttonHtml .= $iconHtml;
        }
        $buttonHtml .= '</a>';

        // Build HTML
        $innerHtml = '<div class="jtb-cta-container">';
        $innerHtml .= '<' . $headerLevel . ' class="jtb-cta-title">' . $title . '</' . $headerLevel . '>';
        $innerHtml .= '<div class="jtb-cta-content">' . $bodyContent . '</div>';
        $innerHtml .= $buttonHtml;
        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for CTA module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Background color only if use_background_color is enabled
        if (!empty($attrs['use_background_color']) && !empty($attrs['promo_color'])) {
            if ($this->isDifferentFromDefault('cta_background', $attrs['promo_color'])) {
                $css .= $selector . ' { background-color: ' . $attrs['promo_color'] . '; }' . "\n";
            }
        }

        if (!empty($attrs['promo_color__hover'])) {
            $css .= $selector . ':hover { background-color: ' . $attrs['promo_color__hover'] . '; }' . "\n";
        }

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('cta', JTB_Module_Cta::class);
