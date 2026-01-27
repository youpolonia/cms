<?php
namespace Core\TB4\Modules\Fullwidth;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Fullwidth Image Module
 *
 * Full-width image display with optional caption and overlay text.
 * Supports lightbox, hover effects, and flexible height modes.
 */
class FwImageModule extends Module
{
    protected array $content_fields = [];
    protected array $design_fields_custom = [];

    public function __construct()
    {
        $this->name = 'Fullwidth Image';
        $this->slug = 'fw_image';
        $this->icon = 'image';
        $this->category = 'fullwidth';

        $this->elements = [
            'main' => '.tb4-fw-image',
            'wrapper' => '.tb4-fw-image-wrapper',
            'image' => '.tb4-fw-image-img',
            'overlay' => '.tb4-fw-image-overlay',
            'overlay_title' => '.tb4-fw-image-overlay-title',
            'overlay_text' => '.tb4-fw-image-overlay-text',
            'caption' => '.tb4-fw-image-caption'
        ];

        // Content fields
        $this->content_fields = [
            'image_url' => [
                'type' => 'text',
                'label' => 'Image URL',
                'default' => ''
            ],
            'alt_text' => [
                'type' => 'text',
                'label' => 'Alt Text',
                'default' => 'Fullwidth image'
            ],
            'caption' => [
                'type' => 'text',
                'label' => 'Caption',
                'default' => ''
            ],
            'show_caption' => [
                'type' => 'select',
                'label' => 'Show Caption',
                'options' => ['no' => 'No', 'yes' => 'Yes'],
                'default' => 'no'
            ],
            'caption_position' => [
                'type' => 'select',
                'label' => 'Caption Position',
                'options' => ['below' => 'Below Image', 'overlay' => 'Overlay on Image'],
                'default' => 'below'
            ],
            'overlay_title' => [
                'type' => 'text',
                'label' => 'Overlay Title',
                'default' => ''
            ],
            'overlay_text' => [
                'type' => 'textarea',
                'label' => 'Overlay Text',
                'default' => ''
            ],
            'show_overlay' => [
                'type' => 'select',
                'label' => 'Show Overlay Content',
                'options' => ['no' => 'No', 'yes' => 'Yes'],
                'default' => 'no'
            ],
            'link_url' => [
                'type' => 'text',
                'label' => 'Link URL',
                'default' => ''
            ],
            'link_target' => [
                'type' => 'select',
                'label' => 'Link Target',
                'options' => ['_self' => 'Same Window', '_blank' => 'New Window'],
                'default' => '_self'
            ],
            'lightbox' => [
                'type' => 'select',
                'label' => 'Open in Lightbox',
                'options' => ['no' => 'No', 'yes' => 'Yes'],
                'default' => 'no'
            ]
        ];

        // Design fields
        $this->design_fields_custom = [
            'height_mode' => [
                'type' => 'select',
                'label' => 'Height Mode',
                'options' => [
                    'auto' => 'Auto (Image Ratio)',
                    'fixed' => 'Fixed Height',
                    'viewport' => 'Viewport Height'
                ],
                'default' => 'auto'
            ],
            'fixed_height' => [
                'type' => 'text',
                'label' => 'Fixed Height',
                'default' => '500px'
            ],
            'viewport_height' => [
                'type' => 'select',
                'label' => 'Viewport Height',
                'options' => [
                    '50vh' => '50%',
                    '75vh' => '75%',
                    '100vh' => '100%'
                ],
                'default' => '50vh'
            ],
            'object_fit' => [
                'type' => 'select',
                'label' => 'Image Fit',
                'options' => [
                    'cover' => 'Cover',
                    'contain' => 'Contain',
                    'fill' => 'Fill'
                ],
                'default' => 'cover'
            ],
            'object_position' => [
                'type' => 'select',
                'label' => 'Image Position',
                'options' => [
                    'center' => 'Center',
                    'top' => 'Top',
                    'bottom' => 'Bottom',
                    'left' => 'Left',
                    'right' => 'Right'
                ],
                'default' => 'center'
            ],
            'border_radius' => [
                'type' => 'text',
                'label' => 'Border Radius',
                'default' => '0px'
            ],
            'overlay_bg_color' => [
                'type' => 'color',
                'label' => 'Overlay Background',
                'default' => 'rgba(0,0,0,0.5)'
            ],
            'overlay_position' => [
                'type' => 'select',
                'label' => 'Overlay Position',
                'options' => [
                    'center' => 'Center',
                    'bottom-left' => 'Bottom Left',
                    'bottom-center' => 'Bottom Center',
                    'bottom-right' => 'Bottom Right',
                    'top-left' => 'Top Left',
                    'top-center' => 'Top Center',
                    'top-right' => 'Top Right'
                ],
                'default' => 'center'
            ],
            'overlay_padding' => [
                'type' => 'text',
                'label' => 'Overlay Padding',
                'default' => '40px'
            ],
            'overlay_title_color' => [
                'type' => 'color',
                'label' => 'Overlay Title Color',
                'default' => '#ffffff'
            ],
            'overlay_title_size' => [
                'type' => 'text',
                'label' => 'Overlay Title Size',
                'default' => '32px'
            ],
            'overlay_text_color' => [
                'type' => 'color',
                'label' => 'Overlay Text Color',
                'default' => 'rgba(255,255,255,0.9)'
            ],
            'overlay_text_size' => [
                'type' => 'text',
                'label' => 'Overlay Text Size',
                'default' => '16px'
            ],
            'caption_bg_color' => [
                'type' => 'color',
                'label' => 'Caption Background',
                'default' => '#f9fafb'
            ],
            'caption_text_color' => [
                'type' => 'color',
                'label' => 'Caption Text Color',
                'default' => '#6b7280'
            ],
            'caption_font_size' => [
                'type' => 'text',
                'label' => 'Caption Font Size',
                'default' => '14px'
            ],
            'caption_padding' => [
                'type' => 'text',
                'label' => 'Caption Padding',
                'default' => '16px'
            ],
            'caption_align' => [
                'type' => 'select',
                'label' => 'Caption Alignment',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'center'
            ],
            'hover_effect' => [
                'type' => 'select',
                'label' => 'Hover Effect',
                'options' => [
                    'none' => 'None',
                    'zoom' => 'Zoom',
                    'brighten' => 'Brighten',
                    'darken' => 'Darken'
                ],
                'default' => 'none'
            ],
            'box_shadow' => [
                'type' => 'select',
                'label' => 'Box Shadow',
                'options' => [
                    'none' => 'None',
                    'sm' => 'Small',
                    'md' => 'Medium',
                    'lg' => 'Large'
                ],
                'default' => 'none'
            ]
        ];

        // Advanced fields
        $this->advanced_fields = array_merge($this->advanced_fields, [
            'css_id' => [
                'type' => 'text',
                'label' => 'CSS ID',
                'default' => ''
            ],
            'css_class' => [
                'type' => 'text',
                'label' => 'CSS Class',
                'default' => ''
            ],
            'custom_css' => [
                'type' => 'textarea',
                'label' => 'Custom CSS',
                'default' => ''
            ]
        ]);
    }

    public function get_content_fields(): array
    {
        return $this->content_fields;
    }

    public function get_design_fields(): array
    {
        return array_merge(parent::get_design_fields(), $this->design_fields_custom);
    }

    public function render(array $attrs): string
    {
        // Content fields
        $imageUrl = $attrs['image_url'] ?? '';
        $altText = $attrs['alt_text'] ?? 'Fullwidth image';
        $caption = $attrs['caption'] ?? '';
        $showCaption = ($attrs['show_caption'] ?? 'no') === 'yes';
        $captionPosition = $attrs['caption_position'] ?? 'below';
        $overlayTitle = $attrs['overlay_title'] ?? '';
        $overlayText = $attrs['overlay_text'] ?? '';
        $showOverlay = ($attrs['show_overlay'] ?? 'no') === 'yes';
        $linkUrl = $attrs['link_url'] ?? '';
        $linkTarget = $attrs['link_target'] ?? '_self';
        $lightbox = ($attrs['lightbox'] ?? 'no') === 'yes';

        // Design fields
        $heightMode = $attrs['height_mode'] ?? 'auto';
        $fixedHeight = $attrs['fixed_height'] ?? '500px';
        $viewportHeight = $attrs['viewport_height'] ?? '50vh';
        $objectFit = $attrs['object_fit'] ?? 'cover';
        $objectPosition = $attrs['object_position'] ?? 'center';
        $borderRadius = $attrs['border_radius'] ?? '0px';
        $overlayBgColor = $attrs['overlay_bg_color'] ?? 'rgba(0,0,0,0.5)';
        $overlayPosition = $attrs['overlay_position'] ?? 'center';
        $overlayPadding = $attrs['overlay_padding'] ?? '40px';
        $overlayTitleColor = $attrs['overlay_title_color'] ?? '#ffffff';
        $overlayTitleSize = $attrs['overlay_title_size'] ?? '32px';
        $overlayTextColor = $attrs['overlay_text_color'] ?? 'rgba(255,255,255,0.9)';
        $overlayTextSize = $attrs['overlay_text_size'] ?? '16px';
        $captionBgColor = $attrs['caption_bg_color'] ?? '#f9fafb';
        $captionTextColor = $attrs['caption_text_color'] ?? '#6b7280';
        $captionFontSize = $attrs['caption_font_size'] ?? '14px';
        $captionPadding = $attrs['caption_padding'] ?? '16px';
        $captionAlign = $attrs['caption_align'] ?? 'center';
        $hoverEffect = $attrs['hover_effect'] ?? 'none';
        $boxShadow = $attrs['box_shadow'] ?? 'none';

        // Advanced fields
        $cssId = $attrs['css_id'] ?? '';
        $cssClass = $attrs['css_class'] ?? '';

        // Calculate height
        $height = 'auto';
        if ($heightMode === 'fixed') {
            $height = $fixedHeight;
        } elseif ($heightMode === 'viewport') {
            $height = $viewportHeight;
        }

        // Shadow mapping
        $shadowMap = [
            'none' => 'none',
            'sm' => '0 1px 3px rgba(0,0,0,0.1)',
            'md' => '0 4px 6px rgba(0,0,0,0.1)',
            'lg' => '0 10px 25px rgba(0,0,0,0.15)'
        ];
        $shadow = $shadowMap[$boxShadow] ?? 'none';

        // Hover effect class
        $hoverClass = $hoverEffect !== 'none' ? ' tb4-fw-image-hover-' . esc_attr($hoverEffect) : '';

        // Overlay position alignment
        $overlayAlignItems = 'center';
        $overlayJustify = 'center';
        $overlayTextAlign = 'center';

        if (strpos($overlayPosition, 'left') !== false) {
            $overlayAlignItems = 'flex-start';
            $overlayTextAlign = 'left';
        } elseif (strpos($overlayPosition, 'right') !== false) {
            $overlayAlignItems = 'flex-end';
            $overlayTextAlign = 'right';
        }

        if (strpos($overlayPosition, 'top') !== false) {
            $overlayJustify = 'flex-start';
        } elseif (strpos($overlayPosition, 'bottom') !== false) {
            $overlayJustify = 'flex-end';
        }

        // Container ID/Class
        $idAttr = $cssId ? ' id="' . esc_attr($cssId) . '"' : '';
        $classAttr = 'tb4-fw-image' . ($cssClass ? ' ' . esc_attr($cssClass) : '');

        // Build HTML
        $html = '<div' . $idAttr . ' class="' . $classAttr . '" style="width:100%;">';

        // Wrapper with hover effect class
        $wrapperStyle = 'position:relative;width:100%;overflow:hidden;border-radius:' . esc_attr($borderRadius) . ';';
        if ($shadow !== 'none') {
            $wrapperStyle .= 'box-shadow:' . $shadow . ';';
        }
        $html .= '<div class="tb4-fw-image-wrapper' . $hoverClass . '" style="' . $wrapperStyle . '">';

        // Link wrapper if URL provided
        $hasLink = $linkUrl !== '' && !$lightbox;
        if ($hasLink) {
            $html .= '<a href="' . esc_attr($linkUrl) . '" target="' . esc_attr($linkTarget) . '" style="display:block;">';
        }

        // Image or placeholder
        if ($imageUrl) {
            $imgStyle = 'width:100%;display:block;object-fit:' . esc_attr($objectFit) . ';object-position:' . esc_attr($objectPosition) . ';transition:all 0.4s ease;';
            if ($height !== 'auto') {
                $imgStyle .= 'height:' . esc_attr($height) . ';';
            }
            $html .= '<img class="tb4-fw-image-img" src="' . esc_attr($imageUrl) . '" alt="' . esc_attr($altText) . '" style="' . $imgStyle . '">';
        } else {
            $placeholderHeight = $height === 'auto' ? '400px' : $height;
            $html .= '<div class="tb4-fw-image-placeholder" style="width:100%;height:' . esc_attr($placeholderHeight) . ';background:linear-gradient(135deg,#e5e7eb 0%,#f3f4f6 100%);display:flex;align-items:center;justify-content:center;color:#9ca3af;transition:all 0.4s ease;">';
            $html .= '<svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>';
            $html .= '</div>';
        }

        // Overlay content
        if ($showOverlay && ($overlayTitle || $overlayText)) {
            $overlayStyle = 'position:absolute;inset:0;background:' . esc_attr($overlayBgColor) . ';display:flex;flex-direction:column;padding:' . esc_attr($overlayPadding) . ';align-items:' . $overlayAlignItems . ';justify-content:' . $overlayJustify . ';text-align:' . $overlayTextAlign . ';';
            $html .= '<div class="tb4-fw-image-overlay" style="' . $overlayStyle . '">';

            if ($overlayTitle) {
                $html .= '<h2 class="tb4-fw-image-overlay-title" style="font-size:' . esc_attr($overlayTitleSize) . ';font-weight:700;color:' . esc_attr($overlayTitleColor) . ';margin:0 0 12px 0;">' . esc_html($overlayTitle) . '</h2>';
            }
            if ($overlayText) {
                $html .= '<p class="tb4-fw-image-overlay-text" style="font-size:' . esc_attr($overlayTextSize) . ';color:' . esc_attr($overlayTextColor) . ';margin:0;line-height:1.6;">' . esc_html($overlayText) . '</p>';
            }

            $html .= '</div>';
        }

        // Lightbox icon
        if ($lightbox && $imageUrl) {
            $html .= '<div class="tb4-fw-image-lightbox-icon" style="position:absolute;top:16px;right:16px;width:40px;height:40px;background:rgba(0,0,0,0.5);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;cursor:pointer;" data-lightbox="' . esc_attr($imageUrl) . '">';
            $html .= '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>';
            $html .= '</div>';
        }

        if ($hasLink) {
            $html .= '</a>';
        }

        $html .= '</div>'; // Close wrapper

        // Caption below
        if ($showCaption && $caption && $captionPosition === 'below') {
            $captionStyle = 'padding:' . esc_attr($captionPadding) . ';background:' . esc_attr($captionBgColor) . ';font-size:' . esc_attr($captionFontSize) . ';color:' . esc_attr($captionTextColor) . ';text-align:' . esc_attr($captionAlign) . ';';
            $html .= '<div class="tb4-fw-image-caption" style="' . $captionStyle . '">' . esc_html($caption) . '</div>';
        }

        $html .= '</div>'; // Close main container

        // Hover effect styles
        if ($hoverEffect !== 'none') {
            $html .= '<style>';
            if ($hoverEffect === 'zoom') {
                $html .= '.tb4-fw-image-hover-zoom:hover .tb4-fw-image-img,.tb4-fw-image-hover-zoom:hover .tb4-fw-image-placeholder{transform:scale(1.05);}';
            } elseif ($hoverEffect === 'brighten') {
                $html .= '.tb4-fw-image-hover-brighten:hover .tb4-fw-image-img{filter:brightness(1.1);}';
            } elseif ($hoverEffect === 'darken') {
                $html .= '.tb4-fw-image-hover-darken:hover .tb4-fw-image-img{filter:brightness(0.8);}';
            }
            $html .= '</style>';
        }

        return $html;
    }
}
