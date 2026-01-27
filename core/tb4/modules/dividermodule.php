<?php
namespace Core\TB4\Modules;

require_once __DIR__ . '/module.php';

/**
 * TB 4.0 Divider Module
 */
class DividerModule extends Module {

    public function __construct() {
        $this->name = 'Divider';
        $this->slug = "divider";
        $this->icon = 'Minus';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-divider'
        ];
    }

    public function get_content_fields(): array {
        return [
            'style' => [
                'label' => 'Line Style',
                'type' => 'select',
                'options' => [
                    'solid' => 'Solid',
                    'dashed' => 'Dashed',
                    'dotted' => 'Dotted',
                    'double' => 'Double'
                ],
                'default' => 'solid'
            ],
            'color' => [
                'label' => 'Color',
                'type' => 'color',
                'default' => '#dddddd'
            ],
            'width' => [
                'label' => 'Width',
                'type' => 'text',
                'default' => '100%'
            ],
            'height' => [
                'label' => 'Height',
                'type' => 'text',
                'default' => '1px'
            ],
            'alignment' => [
                'label' => 'Alignment',
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
        $style = $settings['style'] ?? 'solid';
        $color = $settings['color'] ?? '#dddddd';
        $width = $settings['width'] ?? '100%';
        $height = $settings['height'] ?? '1px';
        $alignment = $settings['alignment'] ?? 'center';

        $alignMap = [
            'left' => 'flex-start',
            'center' => 'center',
            'right' => 'flex-end'
        ];
        $justifyContent = $alignMap[$alignment] ?? 'center';

        return sprintf(
            '<div class="tb4-divider" style="display:flex;justify-content:%s;width:100%%;margin:20px 0"><hr style="border:none;border-top:%s %s %s;width:%s;margin:0"></div>',
            $justifyContent,
            esc_attr($height),
            esc_attr($style),
            esc_attr($color),
            esc_attr($width)
        );
    }
}
