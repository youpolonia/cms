<?php
/**
 * Featured Image Module
 * Displays the post/page featured image
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Featured_Image extends JTB_Element
{
    public string $slug = 'featured_image';
    public string $name = 'Featured Image';
    public string $icon = 'image';
    public string $category = 'dynamic';

    public bool $use_background = false;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = true;

    protected string $module_prefix = 'featured_image';

    protected array $style_config = [
        'max_height' => [
            'property' => 'max-height',
            'selector' => '.jtb-featured-img',
            'unit' => 'px',
            'responsive' => true
        ],
        'border_radius' => [
            'property' => 'border-radius',
            'selector' => '.jtb-featured-img',
            'unit' => 'px'
        ],
        'caption_color' => [
            'property' => 'color',
            'selector' => '.jtb-image-caption'
        ],
        'caption_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-image-caption',
            'unit' => 'px'
        ]
    ];

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFields(): array
    {
        return [
            'image_size' => [
                'label' => 'Image Size',
                'type' => 'select',
                'options' => [
                    'full' => 'Full Size',
                    'large' => 'Large (1024px)',
                    'medium' => 'Medium (512px)',
                    'thumbnail' => 'Thumbnail (150px)'
                ],
                'default' => 'full'
            ],
            'image_style' => [
                'label' => 'Image Style',
                'type' => 'select',
                'options' => [
                    'default' => 'Default',
                    'rounded' => 'Rounded Corners',
                    'circle' => 'Circle (for square images)',
                    'shadow' => 'With Shadow'
                ],
                'default' => 'default'
            ],
            'border_radius' => [
                'label' => 'Border Radius',
                'type' => 'range',
                'min' => 0,
                'max' => 50,
                'step' => 1,
                'default' => 0,
                'unit' => 'px'
            ],
            'max_height' => [
                'label' => 'Max Height',
                'type' => 'range',
                'min' => 100,
                'max' => 800,
                'step' => 10,
                'default' => 400,
                'unit' => 'px',
                'responsive' => true
            ],
            'object_fit' => [
                'label' => 'Image Fit',
                'type' => 'select',
                'options' => [
                    'cover' => 'Cover (crop to fit)',
                    'contain' => 'Contain (show all)',
                    'fill' => 'Fill (stretch)'
                ],
                'default' => 'cover'
            ],
            'alignment' => [
                'label' => 'Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'center',
                'responsive' => true
            ],
            'link_to_post' => [
                'label' => 'Link to Post',
                'type' => 'toggle',
                'default' => false,
                'description' => 'Make image clickable linking to the post'
            ],
            'show_caption' => [
                'label' => 'Show Caption',
                'type' => 'toggle',
                'default' => false,
                'description' => 'Display image caption if available'
            ],
            'caption_color' => [
                'label' => 'Caption Color',
                'type' => 'color',
                'default' => '#666666'
            ],
            'caption_font_size' => [
                'label' => 'Caption Font Size',
                'type' => 'range',
                'min' => 10,
                'max' => 18,
                'step' => 1,
                'default' => 14,
                'unit' => 'px'
            ],
            'hover_effect' => [
                'label' => 'Hover Effect',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'zoom' => 'Zoom In',
                    'brighten' => 'Brighten',
                    'darken' => 'Darken',
                    'grayscale' => 'Grayscale to Color'
                ],
                'default' => 'none'
            ],
            'fallback_image' => [
                'label' => 'Fallback Image',
                'type' => 'upload',
                'default' => '',
                'description' => 'Image to show when no featured image is set'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $id = $attrs['id'] ?? 'featured_image_' . uniqid();
        $imageStyle = $attrs['image_style'] ?? 'default';
        $alignment = $attrs['alignment'] ?? 'center';
        $fallback = $attrs['fallback_image'] ?? '';
        $showCaption = $attrs['show_caption'] ?? false;
        $hoverEffect = $attrs['hover_effect'] ?? 'none';
        $linkToPost = $attrs['link_to_post'] ?? false;

        // Get dynamic content
        $isPreview = JTB_Dynamic_Context::isPreviewMode();
        $featuredImage = JTB_Dynamic_Context::getFeaturedImage();
        $postTitle = JTB_Dynamic_Context::getPostTitle();
        $postUrl = JTB_Dynamic_Context::getPostUrl();

        $classes = ['jtb-featured-image', 'jtb-align-' . $this->esc($alignment)];
        if ($imageStyle !== 'default') {
            $classes[] = 'jtb-image-' . $this->esc($imageStyle);
        }
        if ($hoverEffect !== 'none') {
            $classes[] = 'jtb-hover-' . $this->esc($hoverEffect);
        }

        // Placeholder SVG
        $placeholderSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="400" viewBox="0 0 800 400">'
            . '<rect fill="#e2e8f0" width="800" height="400"/>'
            . '<g fill="#94a3b8" transform="translate(350, 150)">'
            . '<rect x="3" y="3" width="94" height="94" rx="2" ry="2" fill="none" stroke="#94a3b8" stroke-width="6"/>'
            . '<circle cx="35" cy="35" r="10"/>'
            . '<polyline points="94 70 70 45 30 85" fill="none" stroke="#94a3b8" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>'
            . '</g>'
            . '<text fill="#64748b" font-family="sans-serif" font-size="20" x="50%" y="320" text-anchor="middle">Featured Image</text>'
            . '</svg>';

        // Use actual featured image or fallback
        $imageUrl = '';
        if (!empty($featuredImage) && !$isPreview) {
            $imageUrl = $featuredImage;
        } elseif (!empty($fallback)) {
            $imageUrl = $fallback;
        } else {
            $imageUrl = 'data:image/svg+xml,' . rawurlencode($placeholderSvg);
        }

        $altText = !empty($postTitle) ? $postTitle : 'Featured Image';

        $html = '<figure id="' . $this->esc($id) . '" class="' . implode(' ', $classes) . '">';

        // Wrap in link if requested
        if ($linkToPost && !empty($postUrl) && !$isPreview) {
            $html .= '<a href="' . $this->esc($postUrl) . '" class="jtb-image-link">';
        }

        $html .= '<img src="' . $this->esc($imageUrl) . '" alt="' . $this->esc($altText) . '" class="jtb-featured-img" />';

        if ($linkToPost && !empty($postUrl) && !$isPreview) {
            $html .= '</a>';
        }

        if ($showCaption) {
            $caption = !empty($postTitle) && !$isPreview ? $postTitle : 'Image caption will appear here';
            $html .= '<figcaption class="jtb-image-caption">' . $this->esc($caption) . '</figcaption>';
        }

        $html .= '</figure>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $alignment = $attrs['alignment'] ?? 'center';
        $maxHeight = $attrs['max_height'] ?? 400;
        $objectFit = $attrs['object_fit'] ?? 'cover';
        $borderRadius = $attrs['border_radius'] ?? 0;
        $imageStyle = $attrs['image_style'] ?? 'default';
        $hoverEffect = $attrs['hover_effect'] ?? 'none';
        $captionColor = $attrs['caption_color'] ?? '#666666';
        $captionSize = $attrs['caption_font_size'] ?? 14;

        // Container alignment
        $css .= $selector . ' { text-align: ' . $alignment . '; margin: 0; }' . "\n";

        // Image styling
        $css .= $selector . ' .jtb-featured-img { ';
        $css .= 'display: inline-block; ';
        $css .= 'width: 100%; ';
        $css .= 'max-height: ' . intval($maxHeight) . 'px; ';
        $css .= 'object-fit: ' . $objectFit . '; ';
        $css .= 'transition: all 0.4s ease; ';

        if ($borderRadius > 0) {
            $css .= 'border-radius: ' . intval($borderRadius) . 'px; ';
        }
        if ($imageStyle === 'rounded') {
            $css .= 'border-radius: 12px; ';
        }
        if ($imageStyle === 'circle') {
            $css .= 'border-radius: 50%; ';
        }
        if ($imageStyle === 'shadow') {
            $css .= 'box-shadow: 0 4px 20px rgba(0,0,0,0.15); ';
        }
        if ($hoverEffect === 'grayscale') {
            $css .= 'filter: grayscale(100%); ';
        }
        $css .= '}' . "\n";

        // Hover effects
        if ($hoverEffect === 'zoom') {
            $css .= $selector . ':hover .jtb-featured-img { transform: scale(1.05); }' . "\n";
        } elseif ($hoverEffect === 'brighten') {
            $css .= $selector . ':hover .jtb-featured-img { filter: brightness(1.1); }' . "\n";
        } elseif ($hoverEffect === 'darken') {
            $css .= $selector . ':hover .jtb-featured-img { filter: brightness(0.85); }' . "\n";
        } elseif ($hoverEffect === 'grayscale') {
            $css .= $selector . ':hover .jtb-featured-img { filter: grayscale(0%); }' . "\n";
        }

        // Caption styling
        $css .= $selector . ' .jtb-image-caption { ';
        $css .= 'margin-top: 12px; ';
        $css .= 'font-size: ' . intval($captionSize) . 'px; ';
        $css .= 'color: ' . $captionColor . '; ';
        $css .= 'font-style: italic; ';
        $css .= '}' . "\n";

        // Responsive
        if (!empty($attrs['max_height__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-featured-img { max-height: ' . intval($attrs['max_height__tablet']) . 'px; }';
            $css .= ' }' . "\n";
        }
        if (!empty($attrs['max_height__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-featured-img { max-height: ' . intval($attrs['max_height__phone']) . 'px; }';
            $css .= ' }' . "\n";
        }

        // Responsive alignment
        if (!empty($attrs['alignment__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' { text-align: ' . $attrs['alignment__tablet'] . '; }';
            $css .= ' }' . "\n";
        }
        if (!empty($attrs['alignment__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' { text-align: ' . $attrs['alignment__phone'] . '; }';
            $css .= ' }' . "\n";
        }

        return $css;
    }
}

JTB_Registry::register('featured_image', JTB_Module_Featured_Image::class);
