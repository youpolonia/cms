<?php
namespace Core\TB4\Modules;

require_once __DIR__ . '/module.php';

/**
 * TB 4.0 Image Module
 */
class ImageModule extends Module {

    public function __construct() {
        $this->name = 'Image';
        $this->slug = "image";
        $this->icon = 'Image';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-image',
            'img' => '.tb4-image__img'
        ];
    }

    public function get_content_fields(): array {
        return [
            'src' => [
                'label' => 'Image URL',
                'type' => 'upload',
                'default' => ''
            ],
            'alt' => [
                'label' => 'Alt Text',
                'type' => 'text',
                'default' => ''
            ],
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => ''
            ],
            'link_url' => [
                'label' => 'Link URL',
                'type' => 'text',
                'default' => ''
            ],
            'link_target' => [
                'label' => 'Link Target',
                'type' => 'select',
                'options' => [
                    '_self' => 'Same Window',
                    '_blank' => 'New Window'
                ],
                'default' => '_self'
            ],
            'max_width' => [
                'label' => 'Max Width',
                'type' => 'text',
                'default' => '100%'
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
        $src = $settings['src'] ?? '';
        $alt = $settings['alt'] ?? '';
        $title = $settings['title'] ?? '';
        $linkUrl = $settings['link_url'] ?? '';
        $linkTarget = $settings['link_target'] ?? '_self';
        $maxWidth = $settings['max_width'] ?? '100%';
        $alignment = $settings['alignment'] ?? 'center';

        $alignStyle = 'text-align:' . $alignment;
        $imgStyle = 'max-width:' . $maxWidth . ';height:auto;display:inline-block';

        $img = sprintf(
            '<img src="%s" alt="%s" title="%s" style="%s" class="tb4-image__img">',
            esc_attr($src),
            esc_attr($alt),
            esc_attr($title),
            $imgStyle
        );

        if ($linkUrl) {
            $img = sprintf(
                '<a href="%s" target="%s">%s</a>',
                esc_attr($linkUrl),
                esc_attr($linkTarget),
                $img
            );
        }

        return sprintf(
            '<div class="tb4-image" style="%s">%s</div>',
            $alignStyle,
            $img
        );
    }
}
