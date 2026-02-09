<?php
/**
 * Header Button Module
 * CTA button for header (Get Started, Contact Us, etc.)
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Header_Button extends JTB_Element
{
    public string $slug = 'header_button';
    public string $name = 'Header Button';
    public string $icon = 'link';
    public string $category = 'header';

    public bool $use_background = false;
    public bool $use_spacing = true;
    public bool $use_border = false;
    public bool $use_box_shadow = false;
    public bool $use_animation = true;
    public bool $use_typography = true;

    protected string $module_prefix = 'header_button';

    protected array $style_config = [
        'button_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-header-btn',
            'hover' => true
        ],
        'button_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-header-btn',
            'hover' => true
        ],
        'font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-header-btn',
            'unit' => 'px',
            'responsive' => true
        ]
    ];

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFields(): array
    {
        return [
            'button_text' => [
                'label' => 'Button Text',
                'type' => 'text',
                'default' => 'Get Started'
            ],
            'link_url' => [
                'label' => 'Button URL',
                'type' => 'text',
                'default' => '#'
            ],
            'button_style' => [
                'label' => 'Button Style',
                'type' => 'select',
                'options' => [
                    'filled' => 'Filled',
                    'outline' => 'Outline',
                    'text' => 'Text Only'
                ],
                'default' => 'filled'
            ],
            'button_size' => [
                'label' => 'Button Size',
                'type' => 'select',
                'options' => [
                    'small' => 'Small',
                    'medium' => 'Medium',
                    'large' => 'Large'
                ],
                'default' => 'medium'
            ],
            'button_bg_color' => [
                'label' => 'Background Color',
                'type' => 'color',
                'default' => '#2ea3f2',
                'hover' => true
            ],
            'button_text_color' => [
                'label' => 'Text Color',
                'type' => 'color',
                'default' => '#ffffff',
                'hover' => true
            ],
            'border_color' => [
                'label' => 'Border Color',
                'type' => 'color',
                'default' => '#2ea3f2',
                'hover' => true
            ],
            'border_radius' => [
                'label' => 'Border Radius',
                'type' => 'range',
                'min' => 0,
                'max' => 50,
                'step' => 1,
                'default' => 6,
                'unit' => 'px'
            ],
            'font_size' => [
                'label' => 'Font Size',
                'type' => 'range',
                'min' => 12,
                'max' => 20,
                'step' => 1,
                'default' => 14,
                'unit' => 'px',
                'responsive' => true
            ],
            'font_weight' => [
                'label' => 'Font Weight',
                'type' => 'select',
                'options' => [
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi-Bold',
                    '700' => 'Bold'
                ],
                'default' => '600'
            ],
            'show_icon' => [
                'label' => 'Show Icon',
                'type' => 'toggle',
                'default' => false
            ],
            'icon_position' => [
                'label' => 'Icon Position',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'right' => 'Right'
                ],
                'default' => 'right',
                'condition' => ['show_icon' => true]
            ],
            'open_new_tab' => [
                'label' => 'Open in New Tab',
                'type' => 'toggle',
                'default' => false
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $id = $attrs['id'] ?? 'header_button_' . uniqid();
        $buttonText = $attrs['button_text'] ?? 'Get Started';
        $buttonUrl = $attrs['link_url'] ?? '#';
        $buttonStyle = $attrs['button_style'] ?? 'filled';
        $buttonSize = $attrs['button_size'] ?? 'medium';
        $showIcon = $attrs['show_icon'] ?? false;
        $iconPosition = $attrs['icon_position'] ?? 'right';
        $openNewTab = $attrs['open_new_tab'] ?? false;

        $arrowIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';

        $targetAttr = $openNewTab ? ' target="_blank" rel="noopener noreferrer"' : '';

        $classes = [
            'jtb-header-button',
            'jtb-btn-style-' . $this->esc($buttonStyle),
            'jtb-btn-size-' . $this->esc($buttonSize)
        ];

        $html = '<div id="' . $this->esc($id) . '" class="' . implode(' ', $classes) . '">';
        $html .= '<a href="' . $this->esc($buttonUrl) . '" class="jtb-header-btn"' . $targetAttr . '>';

        if ($showIcon && $iconPosition === 'left') {
            $html .= '<span class="jtb-btn-icon jtb-icon-left">' . $arrowIcon . '</span>';
        }

        $html .= '<span class="jtb-btn-text">' . $this->esc($buttonText) . '</span>';

        if ($showIcon && $iconPosition === 'right') {
            $html .= '<span class="jtb-btn-icon jtb-icon-right">' . $arrowIcon . '</span>';
        }

        $html .= '</a>';
        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $buttonStyle = $attrs['button_style'] ?? 'filled';
        $buttonSize = $attrs['button_size'] ?? 'medium';
        $bgColor = $attrs['button_bg_color'] ?? '#2ea3f2';
        $bgHoverColor = $attrs['button_bg_color__hover'] ?? '#1a8ad4';
        $textColor = $attrs['button_text_color'] ?? '#ffffff';
        $textHoverColor = $attrs['button_text_color__hover'] ?? '#ffffff';
        $borderColor = $attrs['border_color'] ?? '#2ea3f2';
        $borderHoverColor = $attrs['border_color__hover'] ?? '#1a8ad4';
        $borderRadius = $attrs['border_radius'] ?? 6;
        $fontSize = $attrs['font_size'] ?? 14;
        $fontWeight = $attrs['font_weight'] ?? '600';

        // Size variations
        $paddings = [
            'small' => '8px 16px',
            'medium' => '12px 24px',
            'large' => '16px 32px'
        ];
        $padding = $paddings[$buttonSize] ?? $paddings['medium'];

        // Container
        $css .= $selector . ' { display: inline-flex; }' . "\n";

        // Button base
        $css .= $selector . ' .jtb-header-btn { ';
        $css .= 'display: inline-flex; ';
        $css .= 'align-items: center; ';
        $css .= 'justify-content: center; ';
        $css .= 'gap: 8px; ';
        $css .= 'padding: ' . $padding . '; ';
        $css .= 'font-size: ' . intval($fontSize) . 'px; ';
        $css .= 'font-weight: ' . $fontWeight . '; ';
        $css .= 'text-decoration: none; ';
        $css .= 'border-radius: ' . intval($borderRadius) . 'px; ';
        $css .= 'transition: all 0.3s ease; ';
        $css .= 'cursor: pointer; ';
        $css .= '}' . "\n";

        // Style: Filled
        if ($buttonStyle === 'filled') {
            $css .= $selector . ' .jtb-header-btn { ';
            $css .= 'background-color: ' . $bgColor . '; ';
            $css .= 'color: ' . $textColor . '; ';
            $css .= 'border: 2px solid transparent; ';
            $css .= '}' . "\n";

            $css .= $selector . ' .jtb-header-btn:hover { ';
            $css .= 'background-color: ' . $bgHoverColor . '; ';
            $css .= 'color: ' . $textHoverColor . '; ';
            $css .= '}' . "\n";
        }

        // Style: Outline
        if ($buttonStyle === 'outline') {
            $css .= $selector . ' .jtb-header-btn { ';
            $css .= 'background-color: transparent; ';
            $css .= 'color: ' . $borderColor . '; ';
            $css .= 'border: 2px solid ' . $borderColor . '; ';
            $css .= '}' . "\n";

            $css .= $selector . ' .jtb-header-btn:hover { ';
            $css .= 'background-color: ' . $bgColor . '; ';
            $css .= 'color: ' . $textHoverColor . '; ';
            $css .= 'border-color: ' . $bgColor . '; ';
            $css .= '}' . "\n";
        }

        // Style: Text
        if ($buttonStyle === 'text') {
            $css .= $selector . ' .jtb-header-btn { ';
            $css .= 'background-color: transparent; ';
            $css .= 'color: ' . $bgColor . '; ';
            $css .= 'border: none; ';
            $css .= 'padding-left: 0; ';
            $css .= 'padding-right: 0; ';
            $css .= '}' . "\n";

            $css .= $selector . ' .jtb-header-btn:hover { ';
            $css .= 'color: ' . $bgHoverColor . '; ';
            $css .= '}' . "\n";
        }

        // Icon
        $css .= $selector . ' .jtb-btn-icon { ';
        $css .= 'display: inline-flex; ';
        $css .= 'align-items: center; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-btn-icon svg { ';
        $css .= 'width: 1em; ';
        $css .= 'height: 1em; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-header-btn:hover .jtb-icon-right { ';
        $css .= 'transform: translateX(4px); ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-btn-icon { transition: transform 0.3s ease; }' . "\n";

        // Responsive
        if (!empty($attrs['font_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-header-btn { font-size: ' . intval($attrs['font_size__tablet']) . 'px; }';
            $css .= ' }' . "\n";
        }
        if (!empty($attrs['font_size__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-header-btn { font-size: ' . intval($attrs['font_size__phone']) . 'px; }';
            $css .= ' }' . "\n";
        }

        return $css;
    }
}

JTB_Registry::register('header_button', JTB_Module_Header_Button::class);
