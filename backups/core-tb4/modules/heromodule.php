<?php
namespace Core\TB4\Modules;

require_once __DIR__ . '/module.php';

/**
 * TB 4.0 Hero Module
 * Full-width hero sections with background, title, subtitle, and CTAs
 */
class HeroModule extends Module {

    public function __construct() {
        $this->name = 'Hero';
        $this->slug = "hero";
        $this->icon = 'Image';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-hero',
            'overlay' => '.tb4-hero__overlay',
            'container' => '.tb4-hero__container',
            'title' => '.tb4-hero__title',
            'subtitle' => '.tb4-hero__subtitle',
            'description' => '.tb4-hero__description',
            'buttons' => '.tb4-hero__buttons',
            'primary_button' => '.tb4-hero__btn--primary',
            'secondary_button' => '.tb4-hero__btn--secondary'
        ];
    }

    public function get_content_fields(): array {
        return [
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Welcome to Our Site'
            ],
            'subtitle' => [
                'label' => 'Subtitle',
                'type' => 'text',
                'default' => 'Discover amazing features'
            ],
            'description' => [
                'label' => 'Description',
                'type' => 'textarea',
                'default' => 'Add a compelling description that engages your visitors and encourages them to take action.'
            ],
            'button_text' => [
                'label' => 'Primary Button Text',
                'type' => 'text',
                'default' => 'Get Started'
            ],
            'button_url' => [
                'label' => 'Primary Button URL',
                'type' => 'text',
                'default' => '#'
            ],
            'button_target' => [
                'label' => 'Primary Button Target',
                'type' => 'select',
                'options' => [
                    '_self' => 'Same Window',
                    '_blank' => 'New Window'
                ],
                'default' => '_self'
            ],
            'secondary_button_text' => [
                'label' => 'Secondary Button Text',
                'type' => 'text',
                'default' => ''
            ],
            'secondary_button_url' => [
                'label' => 'Secondary Button URL',
                'type' => 'text',
                'default' => '#'
            ],
            'secondary_button_target' => [
                'label' => 'Secondary Button Target',
                'type' => 'select',
                'options' => [
                    '_self' => 'Same Window',
                    '_blank' => 'New Window'
                ],
                'default' => '_self'
            ],
            'background_image' => [
                'label' => 'Background Image',
                'type' => 'image',
                'default' => ''
            ],
            'background_position' => [
                'label' => 'Background Position',
                'type' => 'select',
                'options' => [
                    'center center' => 'Center',
                    'top center' => 'Top',
                    'bottom center' => 'Bottom',
                    'center left' => 'Left',
                    'center right' => 'Right'
                ],
                'default' => 'center center'
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
            'background_color' => [
                'label' => 'Background Color',
                'type' => 'color',
                'default' => '#1e3a5f'
            ],
            'overlay_color' => [
                'label' => 'Overlay Color',
                'type' => 'color',
                'default' => 'rgba(0, 0, 0, 0.5)'
            ],
            'overlay_enabled' => [
                'label' => 'Enable Overlay',
                'type' => 'toggle',
                'default' => true
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
            ],
            'vertical_align' => [
                'label' => 'Vertical Alignment',
                'type' => 'select',
                'options' => [
                    'flex-start' => 'Top',
                    'center' => 'Center',
                    'flex-end' => 'Bottom'
                ],
                'default' => 'center'
            ],
            'min_height' => [
                'label' => 'Minimum Height',
                'type' => 'text',
                'default' => '500px'
            ],
            'content_max_width' => [
                'label' => 'Content Max Width',
                'type' => 'text',
                'default' => '800px'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'text',
                'default' => '48px'
            ],
            'title_font_weight' => [
                'label' => 'Title Font Weight',
                'type' => 'select',
                'options' => [
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi-Bold',
                    '700' => 'Bold',
                    '800' => 'Extra Bold'
                ],
                'default' => '700'
            ],
            'subtitle_color' => [
                'label' => 'Subtitle Color',
                'type' => 'color',
                'default' => '#e2e8f0'
            ],
            'subtitle_font_size' => [
                'label' => 'Subtitle Font Size',
                'type' => 'text',
                'default' => '24px'
            ],
            'description_color' => [
                'label' => 'Description Color',
                'type' => 'color',
                'default' => '#cbd5e1'
            ],
            'description_font_size' => [
                'label' => 'Description Font Size',
                'type' => 'text',
                'default' => '18px'
            ],
            'primary_button_bg' => [
                'label' => 'Primary Button Background',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'primary_button_color' => [
                'label' => 'Primary Button Text Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'secondary_button_bg' => [
                'label' => 'Secondary Button Background',
                'type' => 'color',
                'default' => 'transparent'
            ],
            'secondary_button_color' => [
                'label' => 'Secondary Button Text Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'secondary_button_border' => [
                'label' => 'Secondary Button Border Color',
                'type' => 'color',
                'default' => '#ffffff'
            ]
        ];
    }

    public function get_advanced_fields(): array {
        return $this->advanced_fields;
    }

    public function render(array $settings): string {
        // Content fields
        $title = $settings['title'] ?? 'Welcome to Our Site';
        $subtitle = $settings['subtitle'] ?? 'Discover amazing features';
        $description = $settings['description'] ?? '';
        $buttonText = $settings['button_text'] ?? 'Get Started';
        $buttonUrl = $settings['button_url'] ?? '#';
        $buttonTarget = $settings['button_target'] ?? '_self';
        $secondaryButtonText = $settings['secondary_button_text'] ?? '';
        $secondaryButtonUrl = $settings['secondary_button_url'] ?? '#';
        $secondaryButtonTarget = $settings['secondary_button_target'] ?? '_self';

        // Background fields
        $backgroundImage = $settings['background_image'] ?? '';
        $backgroundPosition = $settings['background_position'] ?? 'center center';
        $backgroundSize = $settings['background_size'] ?? 'cover';
        $backgroundColor = $settings['background_color'] ?? '#1e3a5f';
        $overlayColor = $settings['overlay_color'] ?? 'rgba(0, 0, 0, 0.5)';
        $overlayEnabled = $settings['overlay_enabled'] ?? true;

        // Layout fields
        $textAlign = $settings['text_align'] ?? 'center';
        $verticalAlign = $settings['vertical_align'] ?? 'center';
        $minHeight = $settings['min_height'] ?? '500px';
        $contentMaxWidth = $settings['content_max_width'] ?? '800px';

        // Typography fields
        $titleColor = $settings['title_color'] ?? '#ffffff';
        $titleFontSize = $settings['title_font_size'] ?? '48px';
        $titleFontWeight = $settings['title_font_weight'] ?? '700';
        $subtitleColor = $settings['subtitle_color'] ?? '#e2e8f0';
        $subtitleFontSize = $settings['subtitle_font_size'] ?? '24px';
        $descriptionColor = $settings['description_color'] ?? '#cbd5e1';
        $descriptionFontSize = $settings['description_font_size'] ?? '18px';

        // Button styling
        $primaryBtnBg = $settings['primary_button_bg'] ?? '#2563eb';
        $primaryBtnColor = $settings['primary_button_color'] ?? '#ffffff';
        $secondaryBtnBg = $settings['secondary_button_bg'] ?? 'transparent';
        $secondaryBtnColor = $settings['secondary_button_color'] ?? '#ffffff';
        $secondaryBtnBorder = $settings['secondary_button_border'] ?? '#ffffff';

        // Build hero wrapper styles
        $heroStyles = [
            'position: relative',
            'display: flex',
            'align-items: ' . esc_attr($verticalAlign),
            'justify-content: center',
            'min-height: ' . esc_attr($minHeight),
            'width: 100%',
            'background-color: ' . esc_attr($backgroundColor)
        ];

        if ($backgroundImage) {
            $heroStyles[] = 'background-image: url(\'' . esc_attr($backgroundImage) . '\')';
            $heroStyles[] = 'background-position: ' . esc_attr($backgroundPosition);
            $heroStyles[] = 'background-size: ' . esc_attr($backgroundSize);
            $heroStyles[] = 'background-repeat: no-repeat';
        }

        $heroStyle = implode('; ', $heroStyles);

        // Build overlay
        $overlayHtml = '';
        if ($overlayEnabled && $backgroundImage) {
            $overlayHtml = sprintf(
                '<div class="tb4-hero__overlay" style="position:absolute;top:0;left:0;right:0;bottom:0;background:%s;pointer-events:none"></div>',
                esc_attr($overlayColor)
            );
        }

        // Build container styles
        $containerStyles = [
            'position: relative',
            'z-index: 1',
            'width: 100%',
            'max-width: ' . esc_attr($contentMaxWidth),
            'text-align: ' . esc_attr($textAlign),
            'padding: 40px 20px'
        ];
        $containerStyle = implode('; ', $containerStyles);

        // Build title
        $titleStyle = sprintf(
            'color:%s;font-size:%s;font-weight:%s;margin:0 0 16px 0;line-height:1.2',
            esc_attr($titleColor),
            esc_attr($titleFontSize),
            esc_attr($titleFontWeight)
        );

        // Build subtitle
        $subtitleStyle = sprintf(
            'color:%s;font-size:%s;margin:0 0 24px 0;line-height:1.4',
            esc_attr($subtitleColor),
            esc_attr($subtitleFontSize)
        );

        // Build description
        $descriptionStyle = sprintf(
            'color:%s;font-size:%s;margin:0 0 32px 0;line-height:1.6',
            esc_attr($descriptionColor),
            esc_attr($descriptionFontSize)
        );

        // Build primary button
        $primaryBtnStyle = sprintf(
            'display:inline-flex;align-items:center;justify-content:center;padding:14px 28px;background:%s;color:%s;border:none;border-radius:6px;font-size:16px;font-weight:500;text-decoration:none;cursor:pointer;transition:all 0.2s',
            esc_attr($primaryBtnBg),
            esc_attr($primaryBtnColor)
        );

        // Build secondary button
        $secondaryBtnStyle = sprintf(
            'display:inline-flex;align-items:center;justify-content:center;padding:14px 28px;background:%s;color:%s;border:2px solid %s;border-radius:6px;font-size:16px;font-weight:500;text-decoration:none;cursor:pointer;transition:all 0.2s',
            esc_attr($secondaryBtnBg),
            esc_attr($secondaryBtnColor),
            esc_attr($secondaryBtnBorder)
        );

        // Build buttons section
        $buttonsHtml = '';
        if ($buttonText) {
            $justifyButtons = $textAlign === 'center' ? 'center' : ($textAlign === 'right' ? 'flex-end' : 'flex-start');
            $buttonsHtml = sprintf(
                '<div class="tb4-hero__buttons" style="display:flex;flex-wrap:wrap;gap:16px;justify-content:%s;margin-top:8px">',
                $justifyButtons
            );

            $buttonsHtml .= sprintf(
                '<a href="%s" target="%s" class="tb4-hero__btn tb4-hero__btn--primary" style="%s">%s</a>',
                esc_attr($buttonUrl),
                esc_attr($buttonTarget),
                $primaryBtnStyle,
                esc_html($buttonText)
            );

            if ($secondaryButtonText) {
                $buttonsHtml .= sprintf(
                    '<a href="%s" target="%s" class="tb4-hero__btn tb4-hero__btn--secondary" style="%s">%s</a>',
                    esc_attr($secondaryButtonUrl),
                    esc_attr($secondaryButtonTarget),
                    $secondaryBtnStyle,
                    esc_html($secondaryButtonText)
                );
            }

            $buttonsHtml .= '</div>';
        }

        // Assemble the hero
        $html = sprintf(
            '<div class="tb4-hero" style="%s">%s<div class="tb4-hero__container" style="%s">',
            $heroStyle,
            $overlayHtml,
            $containerStyle
        );

        // Add title
        if ($title) {
            $html .= sprintf(
                '<h1 class="tb4-hero__title" style="%s">%s</h1>',
                $titleStyle,
                esc_html($title)
            );
        }

        // Add subtitle
        if ($subtitle) {
            $html .= sprintf(
                '<p class="tb4-hero__subtitle" style="%s">%s</p>',
                $subtitleStyle,
                esc_html($subtitle)
            );
        }

        // Add description
        if ($description) {
            $html .= sprintf(
                '<div class="tb4-hero__description" style="%s">%s</div>',
                $descriptionStyle,
                esc_html($description)
            );
        }

        // Add buttons
        $html .= $buttonsHtml;

        // Close container and hero
        $html .= '</div></div>';

        return $html;
    }
}
