<?php
namespace Core\TB4\Modules;

require_once __DIR__ . '/module.php';

/**
 * TB 4.0 Button Module
 * Call-to-action buttons with various styles
 */
class ButtonModule extends Module {

    /**
     * Typography fields configuration for button text
     */
    protected array $typography_fields = [
        'button' => [
            'label' => 'Button Typography',
            'selector' => '.tb4-button__btn',
            'defaults' => [
                'font_size' => ['desktop' => '15px', 'tablet' => '14px', 'mobile' => '14px'],
                'font_weight' => '500',
                'letter_spacing' => ['desktop' => '0.5px'],
                'text_transform' => 'none'
            ]
        ]
    ];

    /**
     * Custom CSS fields for per-element CSS targeting
     */
    protected array $custom_css_fields = [
        'button_wrapper' => [
            'label' => 'Button Wrapper',
            'selector' => '.tb4-button',
            'description' => 'Outer container for button alignment'
        ],
        'button_element' => [
            'label' => 'Button Element',
            'selector' => '.tb4-button__btn',
            'description' => 'The clickable button/link element'
        ],
        'button_icon' => [
            'label' => 'Button Icon',
            'selector' => '.tb4-button__icon',
            'description' => 'Icon element within the button'
        ]
    ];

    public function __construct() {
        $this->name = 'Button';
        $this->slug = "button";
        $this->icon = 'RectangleHorizontal';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-button',
            'btn' => '.tb4-button__btn'
        ];
    }

    public function get_content_fields(): array {
        return [
            'text' => [
                'label' => 'Button Text',
                'type' => 'text',
                'default' => 'Click Here'
            ],
            'url' => [
                'label' => 'URL',
                'type' => 'text',
                'default' => '#'
            ],
            'target' => [
                'label' => 'Open In',
                'type' => 'select',
                'options' => [
                    '_self' => 'Same Window',
                    '_blank' => 'New Window'
                ],
                'default' => '_self'
            ],
            'style' => [
                'label' => 'Style',
                'type' => 'select',
                'options' => [
                    'filled' => 'Filled',
                    'outline' => 'Outline',
                    'text' => 'Text Only'
                ],
                'default' => 'filled'
            ],
            'size' => [
                'label' => 'Size',
                'type' => 'select',
                'options' => [
                    'small' => 'Small',
                    'medium' => 'Medium',
                    'large' => 'Large'
                ],
                'default' => 'medium'
            ],
            'background_color' => [
                'label' => 'Background Color',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'text_color' => [
                'label' => 'Text Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'border_radius' => [
                'label' => 'Border Radius',
                'type' => 'text',
                'default' => '6px'
            ],
            'alignment' => [
                'label' => 'Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right',
                    'full' => 'Full Width'
                ],
                'default' => 'left'
            ],
            'icon' => [
                'label' => 'Icon',
                'type' => 'icon',
                'default' => ''
            ],
            'icon_position' => [
                'label' => 'Icon Position',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'right' => 'Right'
                ],
                'default' => 'left'
            ]
        ];
    }

    public function get_advanced_fields(): array {
        return $this->advanced_fields;
    }

    public function render(array $settings): string {
        $text = $settings['text'] ?? 'Click Here';
        $url = $settings['url'] ?? '#';
        $target = $settings['target'] ?? '_self';
        $style = $settings['style'] ?? 'filled';
        $size = $settings['size'] ?? 'medium';
        $bgColor = $settings['background_color'] ?? '#2563eb';
        $textColor = $settings['text_color'] ?? '#ffffff';
        $borderRadius = $settings['border_radius'] ?? '6px';
        $alignment = $settings['alignment'] ?? 'left';
        $icon = $settings['icon'] ?? '';
        $iconPosition = $settings['icon_position'] ?? 'left';

        // Size mapping
        $sizeMap = [
            'small' => 'padding:8px 16px;font-size:13px',
            'medium' => 'padding:12px 24px;font-size:15px',
            'large' => 'padding:16px 32px;font-size:17px'
        ];

        // Style mapping
        $styleMap = [
            'filled' => 'background-color:' . esc_attr($bgColor) . ';color:' . esc_attr($textColor) . ';border:none',
            'outline' => 'background-color:transparent;color:' . esc_attr($bgColor) . ';border:2px solid ' . esc_attr($bgColor),
            'text' => 'background-color:transparent;color:' . esc_attr($bgColor) . ';border:none'
        ];

        // Alignment mapping
        $alignMap = [
            'left' => 'justify-content:flex-start',
            'center' => 'justify-content:center',
            'right' => 'justify-content:flex-end',
            'full' => 'justify-content:stretch'
        ];

        $wrapperStyle = 'display:flex;' . ($alignMap[$alignment] ?? 'justify-content:flex-start');
        $btnStyle = implode(';', [
            $sizeMap[$size] ?? $sizeMap['medium'],
            $styleMap[$style] ?? $styleMap['filled'],
            'border-radius:' . esc_attr($borderRadius),
            'cursor:pointer',
            'display:inline-flex',
            'align-items:center',
            'gap:8px',
            'text-decoration:none',
            'font-weight:500',
            'transition:all 0.2s'
        ]);

        if ($alignment === 'full') {
            $btnStyle .= ';width:100%;justify-content:center';
        }

        $iconHtml = '';
        if ($icon) {
            $iconHtml = '<span class="tb4-button__icon" data-icon="' . esc_attr($icon) . '"></span>';
        }

        $content = $iconPosition === 'left'
            ? $iconHtml . esc_html($text)
            : esc_html($text) . $iconHtml;

        return sprintf(
            '<div class="tb4-button" style="%s"><a href="%s" target="%s" class="tb4-button__btn" style="%s">%s</a></div>',
            $wrapperStyle,
            esc_attr($url),
            esc_attr($target),
            $btnStyle,
            $content
        );
    }
}