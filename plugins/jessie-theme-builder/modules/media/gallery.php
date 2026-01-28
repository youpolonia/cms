<?php
/**
 * Gallery Module
 * Image gallery with various layouts
 * Supports both custom images and CMS Gallery integration
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Gallery extends JTB_Element
{
    public string $icon = 'gallery';
    public string $category = 'media';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = true;

    public function getSlug(): string
    {
        return 'gallery';
    }

    public function getName(): string
    {
        return 'Gallery';
    }

    public function getFields(): array
    {
        return [
            // === SOURCE SELECTION ===
            'gallery_source' => [
                'label' => 'Gallery Source',
                'type' => 'select',
                'options' => [
                    'custom' => 'Custom Images',
                    'cms_gallery' => 'CMS Gallery'
                ],
                'default' => 'custom',
                'description' => 'Choose between custom uploaded images or an existing CMS gallery'
            ],
            // CMS Gallery selector (shown when source = cms_gallery)
            'cms_gallery_id' => [
                'label' => 'Select Gallery',
                'type' => 'select',
                'options' => [], // Loaded dynamically via API
                'default' => '',
                'description' => 'Choose a gallery from your CMS',
                'show_if' => ['gallery_source' => 'cms_gallery'],
                'dynamic_options' => 'cms-galleries' // Tells JS to fetch from /api/jtb/cms-galleries
            ],
            // Custom images (shown when source = custom)
            'gallery_images' => [
                'label' => 'Gallery Images',
                'type' => 'gallery',
                'description' => 'Select multiple images for the gallery',
                'show_if' => ['gallery_source' => 'custom']
            ],
            // === LAYOUT OPTIONS ===
            'gallery_layout' => [
                'label' => 'Layout',
                'type' => 'select',
                'options' => [
                    'grid' => 'Grid',
                    'masonry' => 'Masonry',
                    'slider' => 'Slider'
                ],
                'default' => 'grid'
            ],
            'columns' => [
                'label' => 'Columns',
                'type' => 'select',
                'options' => [
                    '1' => '1 Column',
                    '2' => '2 Columns',
                    '3' => '3 Columns',
                    '4' => '4 Columns',
                    '5' => '5 Columns',
                    '6' => '6 Columns'
                ],
                'default' => '3',
                'responsive' => true
            ],
            'gutter' => [
                'label' => 'Gutter Width',
                'type' => 'range',
                'min' => 0,
                'max' => 50,
                'unit' => 'px',
                'default' => 10
            ],
            'show_title_caption' => [
                'label' => 'Show Titles & Captions',
                'type' => 'toggle',
                'default' => true
            ],
            'overlay_on_hover' => [
                'label' => 'Overlay on Hover',
                'type' => 'toggle',
                'default' => true
            ],
            'overlay_color' => [
                'label' => 'Overlay Color',
                'type' => 'color',
                'default' => 'rgba(0,0,0,0.5)',
                'show_if' => ['overlay_on_hover' => true]
            ],
            'overlay_icon_color' => [
                'label' => 'Overlay Icon Color',
                'type' => 'color',
                'default' => '#ffffff',
                'show_if' => ['overlay_on_hover' => true]
            ],
            'lightbox' => [
                'label' => 'Enable Lightbox',
                'type' => 'toggle',
                'default' => true
            ],
            'image_border_radius' => [
                'label' => 'Image Border Radius',
                'type' => 'range',
                'min' => 0,
                'max' => 50,
                'unit' => 'px',
                'default' => 0
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'range',
                'min' => 10,
                'max' => 30,
                'unit' => 'px',
                'default' => 14
            ],
            'caption_font_size' => [
                'label' => 'Caption Font Size',
                'type' => 'range',
                'min' => 10,
                'max' => 24,
                'unit' => 'px',
                'default' => 12
            ]
        ];
    }

    /**
     * Fetch images from CMS Gallery
     */
    private function fetchCmsGalleryImages(int $galleryId): array
    {
        if ($galleryId <= 0) {
            return [];
        }

        try {
            $db = \core\Database::connection();

            $stmt = $db->prepare("
                SELECT id, filename, title, caption, sort_order
                FROM gallery_images
                WHERE gallery_id = ?
                ORDER BY sort_order ASC, id ASC
            ");
            $stmt->execute([$galleryId]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return array_map(function($row) {
                return [
                    'url' => '/uploads/media/' . $row['filename'],
                    'title' => $row['title'] ?? '',
                    'caption' => $row['caption'] ?? '',
                    'alt' => $row['title'] ?? 'Gallery image'
                ];
            }, $rows);

        } catch (\Exception $e) {
            // Log error but don't break the page
            error_log('JTB Gallery: Failed to fetch CMS gallery images - ' . $e->getMessage());
            return [];
        }
    }

    public function render(array $attrs, string $content = ''): string
    {
        $source = $attrs['gallery_source'] ?? 'custom';
        $layout = $attrs['gallery_layout'] ?? 'grid';
        $columns = $attrs['columns'] ?? '3';
        $showCaptions = $attrs['show_title_caption'] ?? true;
        $overlay = $attrs['overlay_on_hover'] ?? true;
        $lightbox = $attrs['lightbox'] ?? true;

        // Get images based on source
        if ($source === 'cms_gallery') {
            $galleryId = (int)($attrs['cms_gallery_id'] ?? 0);
            $images = $this->fetchCmsGalleryImages($galleryId);
        } else {
            $images = $attrs['gallery_images'] ?? [];
            if (is_string($images)) {
                $images = json_decode($images, true) ?: [];
            }
        }

        $containerClass = 'jtb-gallery-container jtb-gallery-' . $layout . ' jtb-gallery-cols-' . $columns;
        if ($overlay) {
            $containerClass .= ' jtb-gallery-has-overlay';
        }
        if ($lightbox) {
            $containerClass .= ' jtb-gallery-lightbox';
        }

        $innerHtml = '<div class="' . $containerClass . '">';

        foreach ($images as $index => $image) {
            $src = is_array($image) ? ($image['url'] ?? '') : $image;
            $title = is_array($image) ? ($image['title'] ?? '') : '';
            $caption = is_array($image) ? ($image['caption'] ?? '') : '';
            $alt = is_array($image) ? ($image['alt'] ?? $title) : $title;

            $innerHtml .= '<div class="jtb-gallery-item">';

            if ($lightbox) {
                $innerHtml .= '<a href="' . $this->esc($src) . '" data-lightbox="gallery" data-title="' . $this->esc($title) . '">';
            }

            $innerHtml .= '<div class="jtb-gallery-image-wrap">';
            $innerHtml .= '<img src="' . $this->esc($src) . '" alt="' . $this->esc($alt) . '" loading="lazy" />';

            if ($overlay) {
                $innerHtml .= '<div class="jtb-gallery-overlay">';
                $innerHtml .= '<span class="jtb-gallery-icon">+</span>';
                $innerHtml .= '</div>';
            }

            $innerHtml .= '</div>';

            if ($showCaptions && (!empty($title) || !empty($caption))) {
                $innerHtml .= '<div class="jtb-gallery-meta">';
                if (!empty($title)) {
                    $innerHtml .= '<div class="jtb-gallery-title">' . $this->esc($title) . '</div>';
                }
                if (!empty($caption)) {
                    $innerHtml .= '<div class="jtb-gallery-caption">' . $this->esc($caption) . '</div>';
                }
                $innerHtml .= '</div>';
            }

            if ($lightbox) {
                $innerHtml .= '</a>';
            }

            $innerHtml .= '</div>';
        }

        if (empty($images)) {
            if ($source === 'cms_gallery') {
                $innerHtml .= '<p class="jtb-gallery-empty">No CMS gallery selected or gallery is empty.</p>';
            } else {
                $innerHtml .= '<p class="jtb-gallery-empty">No images selected for this gallery.</p>';
            }
        }

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        $gutter = $attrs['gutter'] ?? 10;
        $columns = $attrs['columns'] ?? 3;
        $borderRadius = $attrs['image_border_radius'] ?? 0;

        // Grid layout
        $css .= $selector . ' .jtb-gallery-grid { ';
        $css .= 'display: grid; ';
        $css .= 'grid-template-columns: repeat(' . $columns . ', 1fr); ';
        $css .= 'gap: ' . $gutter . 'px; ';
        $css .= '}' . "\n";

        // Gallery item
        $css .= $selector . ' .jtb-gallery-item { position: relative; overflow: hidden; }' . "\n";

        // Image wrap
        $css .= $selector . ' .jtb-gallery-image-wrap { ';
        $css .= 'position: relative; ';
        $css .= 'overflow: hidden; ';
        $css .= 'border-radius: ' . $borderRadius . 'px; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-gallery-image-wrap img { ';
        $css .= 'width: 100%; ';
        $css .= 'height: auto; ';
        $css .= 'display: block; ';
        $css .= 'transition: transform 0.3s ease; ';
        $css .= '}' . "\n";

        // Hover zoom
        $css .= $selector . ' .jtb-gallery-item:hover .jtb-gallery-image-wrap img { transform: scale(1.05); }' . "\n";

        // Overlay
        $overlayColor = $attrs['overlay_color'] ?? 'rgba(0,0,0,0.5)';
        $overlayIconColor = $attrs['overlay_icon_color'] ?? '#ffffff';

        $css .= $selector . ' .jtb-gallery-overlay { ';
        $css .= 'position: absolute; ';
        $css .= 'top: 0; ';
        $css .= 'left: 0; ';
        $css .= 'right: 0; ';
        $css .= 'bottom: 0; ';
        $css .= 'background-color: ' . $overlayColor . '; ';
        $css .= 'display: flex; ';
        $css .= 'align-items: center; ';
        $css .= 'justify-content: center; ';
        $css .= 'opacity: 0; ';
        $css .= 'transition: opacity 0.3s ease; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-gallery-item:hover .jtb-gallery-overlay { opacity: 1; }' . "\n";

        $css .= $selector . ' .jtb-gallery-icon { ';
        $css .= 'color: ' . $overlayIconColor . '; ';
        $css .= 'font-size: 32px; ';
        $css .= 'font-weight: bold; ';
        $css .= '}' . "\n";

        // Meta
        $titleSize = $attrs['title_font_size'] ?? 14;
        $captionSize = $attrs['caption_font_size'] ?? 12;

        $css .= $selector . ' .jtb-gallery-meta { padding: 10px 0; }' . "\n";
        $css .= $selector . ' .jtb-gallery-title { font-size: ' . $titleSize . 'px; font-weight: bold; }' . "\n";
        $css .= $selector . ' .jtb-gallery-caption { font-size: ' . $captionSize . 'px; color: #666; }' . "\n";

        // Responsive columns
        if (!empty($attrs['columns__tablet'])) {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-gallery-grid { grid-template-columns: repeat(' . $attrs['columns__tablet'] . ', 1fr); } }' . "\n";
        } else {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-gallery-grid { grid-template-columns: repeat(2, 1fr); } }' . "\n";
        }

        if (!empty($attrs['columns__phone'])) {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-gallery-grid { grid-template-columns: repeat(' . $attrs['columns__phone'] . ', 1fr); } }' . "\n";
        } else {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-gallery-grid { grid-template-columns: 1fr; } }' . "\n";
        }

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('gallery', JTB_Module_Gallery::class);
