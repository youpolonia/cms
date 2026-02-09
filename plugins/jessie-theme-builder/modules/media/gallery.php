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

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'gallery';

    /**
     * Declarative style configuration
     * Maps attribute names to CSS properties and selectors
     * Base styles are in jtb-base-modules.css, this only handles customizations
     */
    protected array $style_config = [
        // Grid/Layout
        'gutter' => [
            'property' => 'gap',
            'selector' => '.jtb-gallery-container',
            'unit' => 'px'
        ],
        'columns' => [
            'property' => '--gallery-columns',
            'selector' => '.jtb-gallery-container',
            'responsive' => true
        ],
        // Image styling
        'image_border_radius' => [
            'property' => 'border-radius',
            'selector' => '.jtb-gallery-image-wrap',
            'unit' => 'px'
        ],
        // Overlay
        'overlay_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-gallery-overlay'
        ],
        'overlay_icon_color' => [
            'property' => 'color',
            'selector' => '.jtb-gallery-icon'
        ],
        // Typography
        'title_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-gallery-title',
            'unit' => 'px'
        ],
        'caption_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-gallery-caption',
            'unit' => 'px'
        ],
        'title_color' => [
            'property' => 'color',
            'selector' => '.jtb-gallery-title'
        ],
        'caption_color' => [
            'property' => 'color',
            'selector' => '.jtb-gallery-caption'
        ]
    ];

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
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

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

    /**
     * Generate CSS for Gallery module
     *
     * Base styles are defined in jtb-base-modules.css using CSS variables.
     * This method only generates CSS for values that differ from defaults.
     *
     * The style_config declarative system handles:
     * - gutter, columns, image_border_radius
     * - overlay_color, overlay_icon_color
     * - title_font_size, caption_font_size, title_color, caption_color
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system for mapped properties
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Handle columns as CSS variable for grid-template-columns
        $columns = $attrs['columns'] ?? 3;
        if ($this->isDifferentFromDefault('gallery_columns', $columns)) {
            $css .= $selector . ' .jtb-gallery-container { --gallery-columns: ' . intval($columns) . '; }' . "\n";
        }

        // Responsive columns (these need special handling for grid)
        if (!empty($attrs['columns__tablet'])) {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-gallery-container { --gallery-columns: ' . intval($attrs['columns__tablet']) . '; } }' . "\n";
        }

        if (!empty($attrs['columns__phone'])) {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-gallery-container { --gallery-columns: ' . intval($attrs['columns__phone']) . '; } }' . "\n";
        }

        // Parent class handles common styles (background, spacing, border, etc.)
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('gallery', JTB_Module_Gallery::class);
