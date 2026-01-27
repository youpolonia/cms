<?php
namespace Core\TB4\Modules;

require_once __DIR__ . '/module.php';

/**
 * TB 4.0 Text Module
 * Rich text content with heading and paragraph support
 */
class TextModule extends Module {

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
            'text_color' => [
                'label' => 'Text Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'heading_color' => [
                'label' => 'Heading Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'font_size' => [
                'label' => 'Font Size',
                'type' => 'text',
                'default' => '16px'
            ],
            'line_height' => [
                'label' => 'Line Height',
                'type' => 'text',
                'default' => '1.6'
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
        $textColor = $settings['text_color'] ?? '#111827';
        $headingColor = $settings['heading_color'] ?? '#111827';
        $fontSize = $settings['font_size'] ?? '16px';
        $lineHeight = $settings['line_height'] ?? '1.6';
        $textAlign = $settings['text_align'] ?? 'left';

        $mainStyles = [
            'color:' . esc_attr($textColor),
            'font-size:' . esc_attr($fontSize),
            'line-height:' . esc_attr($lineHeight),
            'text-align:' . esc_attr($textAlign)
        ];

        $html = '<div class="tb4-text" style="' . implode(';', $mainStyles) . '">';

        if ($heading) {
            $headingStyle = 'color:' . esc_attr($headingColor) . ';margin-bottom:0.5em';
            $html .= sprintf(
                '<%s class="tb4-text__heading" style="%s">%s</%s>',
                $headingLevel,
                $headingStyle,
                esc_html($heading),
                $headingLevel
            );
        }

        $html .= '<div class="tb4-text__content">' . $content . '</div>';
        $html .= '</div>';

        return $html;
    }
}
