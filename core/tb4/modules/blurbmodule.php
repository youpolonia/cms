<?php
namespace Core\TB4\Modules;

require_once __DIR__ . '/module.php';

/**
 * TB 4.0 Blurb Module
 */
class BlurbModule extends Module {

    /**
     * Typography fields configuration for title and content elements
     */
    protected array $typography_fields = [
        'title' => [
            'label' => 'Title Typography',
            'selector' => '.tb4-blurb__title',
            'defaults' => [
                'font_size' => ['desktop' => '24px', 'tablet' => '20px', 'mobile' => '18px'],
                'font_weight' => '600',
                'color' => '#1f2937'
            ]
        ],
        'content' => [
            'label' => 'Content Typography',
            'selector' => '.tb4-blurb__content',
            'defaults' => [
                'font_size' => ['desktop' => '16px', 'tablet' => '15px', 'mobile' => '14px'],
                'line_height' => ['desktop' => '1.6'],
                'color' => '#6b7280'
            ]
        ]
    ];

    /**
     * Custom CSS fields for per-element CSS targeting
     */
    protected array $custom_css_fields = [
        'blurb_container' => [
            'label' => 'Blurb Container',
            'selector' => '.tb4-blurb',
            'description' => 'Main blurb wrapper element'
        ],
        'blurb_icon' => [
            'label' => 'Blurb Icon',
            'selector' => '.tb4-blurb__icon',
            'description' => 'Icon element within the blurb'
        ],
        'blurb_title' => [
            'label' => 'Blurb Title',
            'selector' => '.tb4-blurb__title',
            'description' => 'Title heading element'
        ],
        'blurb_content' => [
            'label' => 'Blurb Content',
            'selector' => '.tb4-blurb__content',
            'description' => 'Content/description text area'
        ]
    ];

    public function __construct() {
        $this->name = 'Blurb';
        $this->slug = "blurb";
        $this->icon = 'MessageSquare';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-blurb',
            'icon' => '.tb4-blurb__icon',
            'title' => '.tb4-blurb__title',
            'content' => '.tb4-blurb__content'
        ];
    }

    public function get_content_fields(): array {
        return [
            'icon' => [
                'label' => 'Icon',
                'type' => 'icon',
                'default' => 'Star'
            ],
            'icon_color' => [
                'label' => 'Icon Color',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Feature Title'
            ],
            'content' => [
                'label' => 'Content',
                'type' => 'textarea',
                'default' => 'Feature description goes here.'
            ],
            'text_align' => [
                'label' => 'Text Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'center'
            ]
        ];
    }

    public function get_advanced_fields(): array {
        return $this->advanced_fields;
    }

    public function render(array $settings): string {
        $icon = $settings['icon'] ?? 'Star';
        $iconColor = $settings['icon_color'] ?? '#2563eb';
        $title = $settings['title'] ?? 'Feature Title';
        $content = $settings['content'] ?? 'Feature description.';
        $textAlign = $settings['text_align'] ?? 'center';

        return sprintf(
            '<div class="tb4-blurb" style="display:flex;flex-direction:column;align-items:center;text-align:%s"><div class="tb4-blurb__icon" style="width:48px;height:48px;color:%s" data-icon="%s"></div><h3 class="tb4-blurb__title">%s</h3><div class="tb4-blurb__content">%s</div></div>',
            $textAlign,
            $iconColor,
            esc_attr($icon),
            esc_html($title),
            esc_html($content)
        );
    }
}