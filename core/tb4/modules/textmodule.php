<?php
namespace Core\TB4\Modules;

require_once __DIR__ . '/module.php';

/**
 * TB 4.0 Text Module
 * Rich text content with heading and paragraph support
 */
class TextModule extends Module {

    /**
     * Typography fields configuration for heading and content elements
     */
    protected array $typography_fields = [
        'heading' => [
            'label' => 'Heading Typography',
            'selector' => '.tb4-text__heading',
            'defaults' => [
                'font_size' => ['desktop' => '32px', 'tablet' => '28px', 'mobile' => '24px'],
                'font_weight' => '700',
                'line_height' => ['desktop' => '1.3'],
                'color' => '#111827'
            ]
        ],
        'content' => [
            'label' => 'Content Typography',
            'selector' => '.tb4-text__content',
            'defaults' => [
                'font_size' => ['desktop' => '16px', 'tablet' => '15px', 'mobile' => '14px'],
                'line_height' => ['desktop' => '1.6'],
                'color' => '#374151'
            ]
        ]
    ];

    public function __construct() {
        $this->name = 'Text';
        $this->slug = "text";
        $this->icon = 'Type';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-text',
            'heading' => '.tb4-text__heading',
            'content' => '.tb4-text__content'
        ];
    }

    public function get_content_fields(): array {
        return [
            'heading' => [
                'label' => 'Heading',
                'type' => 'text',
                'default' => ''
            ],
            'heading_level' => [
                'label' => 'Heading Level',
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
            'content' => [
                'label' => 'Content',
                'type' => 'wysiwyg',
                'default' => '<p>Your content goes here. Click to edit.</p>'
            ],
            'text_align' => [
                'label' => 'Text Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right',
                    'justify' => 'Justify'
                ],
                'default' => 'left'
            ]
        ];
    }

    public function get_advanced_fields(): array {
        return $this->advanced_fields;
    }

    public function render(array $settings): string {
        $heading = $settings['heading'] ?? '';
        $headingLevel = $settings['heading_level'] ?? 'h2';
        $content = $settings['content'] ?? '<p>Your content goes here.</p>';
        $textAlign = $settings['text_align'] ?? 'left';

        $html = '<div class="tb4-text" style="text-align:' . esc_attr($textAlign) . '">';

        if ($heading) {
            $html .= sprintf(
                '<%s class="tb4-text__heading">%s</%s>',
                $headingLevel,
                esc_html($heading),
                $headingLevel
            );
        }

        $html .= '<div class="tb4-text__content">' . $content . '</div>';
        $html .= '</div>';

        return $html;
    }
}