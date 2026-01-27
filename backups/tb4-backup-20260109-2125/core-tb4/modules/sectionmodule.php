<?php
namespace Core\TB4\Modules;

require_once __DIR__ . '/module.php';

/**
 * TB 4.0 Section Module
 * Container for rows - provides full-width background sections
 */
class SectionModule extends Module {

    public function __construct() {
        $this->name = 'Section';
        $this->slug = "section";
        $this->icon = 'LayoutTemplate';
        $this->category = 'structure';

        $this->elements = [
            'main' => '.tb4-section',
            'inner' => '.tb4-section__inner'
        ];
    }

    public function get_content_fields(): array {
        return [
            'background_color' => [
                'label' => 'Background Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'background_image' => [
                'label' => 'Background Image',
                'type' => 'upload',
                'default' => ''
            ],
            'background_size' => [
                'label' => 'Background Size',
                'type' => 'select',
                'options' => [
                    'cover' => 'Cover',
                    'contain' => 'Contain',
                    'auto' => 'Auto'
                ],
                'default' => 'cover'
            ],
            'background_position' => [
                'label' => 'Background Position',
                'type' => 'select',
                'options' => [
                    'center center' => 'Center',
                    'top center' => 'Top',
                    'bottom center' => 'Bottom',
                    'left center' => 'Left',
                    'right center' => 'Right'
                ],
                'default' => 'center center'
            ],
            'min_height' => [
                'label' => 'Min Height',
                'type' => 'text',
                'default' => ''
            ],
            'fullwidth' => [
                'label' => 'Full Width',
                'type' => 'toggle',
                'default' => false
            ]
        ];
    }

    public function get_advanced_fields(): array {
        return $this->advanced_fields;
    }

    public function render(array $settings): string {
        $bgColor = $settings['background_color'] ?? '#ffffff';
        $bgImage = $settings['background_image'] ?? '';
        $bgSize = $settings['background_size'] ?? 'cover';
        $bgPosition = $settings['background_position'] ?? 'center center';
        $minHeight = $settings['min_height'] ?? '';
        $fullwidth = $settings['fullwidth'] ?? false;

        $styles = ['background-color:' . esc_attr($bgColor)];
        if ($bgImage) {
            $styles[] = 'background-image:url(' . esc_attr($bgImage) . ')';
            $styles[] = 'background-size:' . esc_attr($bgSize);
            $styles[] = 'background-position:' . esc_attr($bgPosition);
            $styles[] = 'background-repeat:no-repeat';
        }
        if ($minHeight) {
            $styles[] = 'min-height:' . esc_attr($minHeight);
        }

        $innerStyle = $fullwidth ? 'width:100%' : 'max-width:1200px;margin:0 auto';

        return sprintf(
            '<section class="tb4-section" style="%s"><div class="tb4-section__inner" style="%s">%s</div></section>',
            implode(';', $styles),
            $innerStyle,
            '<!-- rows -->'
        );
    }
}
