<?php
/**
 * Fullwidth Image Module
 * Full-width image with optional overlay
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_FullwidthImage extends JTB_Element
{
    public string $icon = 'image-fullwidth';
    public string $category = 'fullwidth';

    public bool $use_typography = false;
    public bool $use_background = false;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = true;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'fullwidth_image';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'overlay_color' => [
            'property' => 'background',
            'selector' => '.jtb-image-overlay'
        ],
        'overlay_icon_color' => [
            'property' => 'color',
            'selector' => '.jtb-overlay-icon'
        ]
    ];

    public function getSlug(): string
    {
        return 'fullwidth_image';
    }

    public function getName(): string
    {
        return 'Fullwidth Image';
    }

    public function getFields(): array
    {
        return [
            'src' => [
                'label' => 'Image',
                'type' => 'upload'
            ],
            'alt' => [
                'label' => 'Image Alt Text',
                'type' => 'text'
            ],
            'title_text' => [
                'label' => 'Image Title',
                'type' => 'text'
            ],
            'link_url' => [
                'label' => 'Link URL',
                'type' => 'text'
            ],
            'link_target' => [
                'label' => 'Open in New Tab',
                'type' => 'toggle',
                'default' => false
            ],
            'show_in_lightbox' => [
                'label' => 'Open in Lightbox',
                'type' => 'toggle',
                'default' => false
            ],
            'use_overlay' => [
                'label' => 'Use Overlay',
                'type' => 'toggle',
                'default' => false
            ],
            'overlay_color' => [
                'label' => 'Overlay Color',
                'type' => 'color',
                'default' => 'rgba(0,0,0,0.3)',
                'show_if' => ['use_overlay' => true]
            ],
            'overlay_on_hover' => [
                'label' => 'Overlay on Hover Only',
                'type' => 'toggle',
                'default' => true,
                'show_if' => ['use_overlay' => true]
            ],
            'overlay_icon_color' => [
                'label' => 'Overlay Icon Color',
                'type' => 'color',
                'default' => '#ffffff',
                'show_if' => ['use_overlay' => true]
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $src = $attrs['src'] ?? '';
        $alt = $this->esc($attrs['alt'] ?? '');
        $titleText = $this->esc($attrs['title_text'] ?? '');
        $url = $attrs['link_url'] ?? '';
        $newWindow = !empty($attrs['link_target']) ? ' target="_blank" rel="noopener"' : '';
        $lightbox = !empty($attrs['show_in_lightbox']);
        $useOverlay = !empty($attrs['use_overlay']);

        $innerHtml = '<div class="jtb-fullwidth-image-container">';

        if (empty($src)) {
            $innerHtml .= '<div class="jtb-image-placeholder">No image selected</div>';
        } else {
            // Start link/lightbox wrapper
            if ($lightbox) {
                $innerHtml .= '<a href="' . $this->esc($src) . '" data-lightbox="fullwidth-image" class="jtb-fullwidth-image-link">';
            } elseif (!empty($url)) {
                $innerHtml .= '<a href="' . $this->esc($url) . '"' . $newWindow . ' class="jtb-fullwidth-image-link">';
            }

            $innerHtml .= '<div class="jtb-fullwidth-image-wrap">';
            $innerHtml .= '<img src="' . $this->esc($src) . '" alt="' . $alt . '"' . (!empty($titleText) ? ' title="' . $titleText . '"' : '') . ' />';

            if ($useOverlay) {
                $innerHtml .= '<div class="jtb-image-overlay">';
                $innerHtml .= '<span class="jtb-overlay-icon">+</span>';
                $innerHtml .= '</div>';
            }

            $innerHtml .= '</div>';

            // End link/lightbox wrapper
            if ($lightbox || !empty($url)) {
                $innerHtml .= '</a>';
            }
        }

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $useOverlay = !empty($attrs['use_overlay']);
        $overlayColor = $attrs['overlay_color'] ?? 'rgba(0,0,0,0.3)';
        $overlayOnHover = $attrs['overlay_on_hover'] ?? true;
        $iconColor = $attrs['overlay_icon_color'] ?? '#ffffff';

        // Container
        $css .= $selector . ' .jtb-fullwidth-image-container { line-height: 0; }' . "\n";
        $css .= $selector . ' .jtb-fullwidth-image-link { display: block; }' . "\n";

        // Image wrap
        $css .= $selector . ' .jtb-fullwidth-image-wrap { position: relative; overflow: hidden; }' . "\n";
        $css .= $selector . ' .jtb-fullwidth-image-wrap img { width: 100%; height: auto; display: block; transition: transform 0.3s ease; }' . "\n";

        // Hover zoom
        $css .= $selector . ' .jtb-fullwidth-image-link:hover img { transform: scale(1.02); }' . "\n";

        // Placeholder
        $css .= $selector . ' .jtb-image-placeholder { background: #f0f0f0; padding: 100px; text-align: center; color: #999; }' . "\n";

        // Overlay
        if ($useOverlay) {
            $css .= $selector . ' .jtb-image-overlay { ';
            $css .= 'position: absolute; top: 0; left: 0; right: 0; bottom: 0; ';
            $css .= 'background: ' . $overlayColor . '; ';
            $css .= 'display: flex; align-items: center; justify-content: center; ';
            $css .= 'transition: opacity 0.3s ease; ';
            if ($overlayOnHover) {
                $css .= 'opacity: 0; ';
            }
            $css .= '}' . "\n";

            if ($overlayOnHover) {
                $css .= $selector . ' .jtb-fullwidth-image-wrap:hover .jtb-image-overlay { opacity: 1; }' . "\n";
            }

            $css .= $selector . ' .jtb-overlay-icon { ';
            $css .= 'color: ' . $iconColor . '; ';
            $css .= 'font-size: 48px; ';
            $css .= 'font-weight: bold; ';
            $css .= '}' . "\n";
        }

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('fullwidth_image', JTB_Module_FullwidthImage::class);
