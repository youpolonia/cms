<?php
namespace Core\TB4\Modules;

require_once __DIR__ . '/module.php';

/**
 * TB 4.0 Column Module
 * Container for content modules within a row
 */
class ColumnModule extends Module {

    public function __construct() {
        $this->name = 'Column';
        $this->slug = "column";
        $this->icon = 'Columns';
        $this->category = 'structure';

        $this->elements = [
            'main' => '.tb4-column'
        ];
    }

    public function get_content_fields(): array {
        return [
            'width' => [
                'label' => 'Width',
                'type' => 'select',
                'options' => [
                    'auto' => 'Auto',
                    '25%' => '25%',
                    '33.333%' => '33%',
                    '50%' => '50%',
                    '66.666%' => '66%',
                    '75%' => '75%',
                    '100%' => '100%'
                ],
                'default' => 'auto'
            ],
            'background_color' => [
                'label' => 'Background Color',
                'type' => 'color',
                'default' => ''
            ],
            'vertical_align' => [
                'label' => 'Content Alignment',
                'type' => 'select',
                'options' => [
                    'flex-start' => 'Top',
                    'center' => 'Center',
                    'flex-end' => 'Bottom'
                ],
                'default' => 'flex-start'
            ],
            'text_align' => [
                'label' => 'Text Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
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
        $width = $settings['width'] ?? 'auto';
        $bgColor = $settings['background_color'] ?? '';
        $verticalAlign = $settings['vertical_align'] ?? 'flex-start';
        $textAlign = $settings['text_align'] ?? 'left';

        $styles = [
            'display:flex',
            'flex-direction:column',
            'justify-content:' . esc_attr($verticalAlign),
            'text-align:' . esc_attr($textAlign)
        ];

        if ($width !== 'auto') {
            $styles[] = 'flex:0 0 ' . esc_attr($width);
        } else {
            $styles[] = 'flex:1';
        }

        if ($bgColor) {
            $styles[] = 'background-color:' . esc_attr($bgColor);
        }

        return sprintf(
            '<div class="tb4-column" style="%s">%s</div>',
            implode(';', $styles),
            '<!-- modules -->'
        );
    }
}
