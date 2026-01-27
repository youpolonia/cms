<?php
namespace Core\TB4\Modules;

require_once __DIR__ . '/module.php';

/**
 * TB 4.0 Row Module
 * Horizontal container for columns - provides flexbox layout
 */
class RowModule extends Module {

    public function __construct() {
        $this->name = 'Row';
        $this->slug = "row";
        $this->icon = 'Rows';
        $this->category = 'structure';

        $this->elements = [
            'main' => '.tb4-row'
        ];
    }

    public function get_content_fields(): array {
        return [
            'columns' => [
                'label' => 'Column Layout',
                'type' => 'select',
                'options' => [
                    '1' => '1 Column (100%)',
                    '2' => '2 Columns (50/50)',
                    '3' => '3 Columns (33/33/33)',
                    '4' => '4 Columns (25/25/25/25)',
                    '1_2' => '2 Columns (33/66)',
                    '2_1' => '2 Columns (66/33)',
                    '1_3' => '2 Columns (25/75)',
                    '3_1' => '2 Columns (75/25)'
                ],
                'default' => '2'
            ],
            'gap' => [
                'label' => 'Column Gap',
                'type' => 'text',
                'default' => '24px'
            ],
            'align_items' => [
                'label' => 'Vertical Alignment',
                'type' => 'select',
                'options' => [
                    'stretch' => 'Stretch',
                    'flex-start' => 'Top',
                    'center' => 'Center',
                    'flex-end' => 'Bottom'
                ],
                'default' => 'stretch'
            ],
            'justify_content' => [
                'label' => 'Horizontal Alignment',
                'type' => 'select',
                'options' => [
                    'flex-start' => 'Left',
                    'center' => 'Center',
                    'flex-end' => 'Right',
                    'space-between' => 'Space Between',
                    'space-around' => 'Space Around'
                ],
                'default' => 'flex-start'
            ],
            'wrap' => [
                'label' => 'Wrap Columns',
                'type' => 'toggle',
                'default' => true
            ]
        ];
    }

    public function get_advanced_fields(): array {
        return $this->advanced_fields;
    }

    public function render(array $settings): string {
        $gap = $settings['gap'] ?? '24px';
        $alignItems = $settings['align_items'] ?? 'stretch';
        $justifyContent = $settings['justify_content'] ?? 'flex-start';
        $wrap = $settings['wrap'] ?? true;

        $styles = [
            'display:flex',
            'gap:' . esc_attr($gap),
            'align-items:' . esc_attr($alignItems),
            'justify-content:' . esc_attr($justifyContent),
            'flex-wrap:' . ($wrap ? 'wrap' : 'nowrap')
        ];

        return sprintf(
            '<div class="tb4-row" style="%s">%s</div>',
            implode(';', $styles),
            '<!-- columns -->'
        );
    }
}
