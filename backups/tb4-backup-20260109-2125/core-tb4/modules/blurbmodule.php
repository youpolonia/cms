<?php
namespace Core\TB4\Modules;

require_once __DIR__ . '/module.php';

/**
 * TB 4.0 Blurb Module
 */
class BlurbModule extends Module {

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
