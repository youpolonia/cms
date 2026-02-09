<?php
/**
 * Image Module
 * Display images with various options
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Image extends JTB_Element
{
    public string $icon = 'image';
    public string $category = 'media';

    public bool $use_typography = false;
    public bool $use_background = false;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = true;
    public bool $use_position = false;
    public bool $use_filters = true;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'image';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'width' => [
            'property' => 'width',
            'selector' => '.jtb-image-img',
            'unit' => '%',
            'responsive' => true
        ],
        'max_width' => [
            'property' => 'max-width',
            'selector' => '.jtb-image-img',
            'unit' => 'px',
            'responsive' => true
        ]
    ];

    public function getSlug(): string
    {
        return 'image';
    }

    public function getName(): string
    {
        return 'Image';
    }

    public function getFields(): array
    {
        return [
            'src' => [
                'label' => 'Image',
                'type' => 'upload',
                'accept' => 'image/*'
            ],
            'alt' => [
                'label' => 'Alt Text',
                'type' => 'text',
                'description' => 'Describe the image for accessibility'
            ],
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'description' => 'Optional title attribute'
            ],
            'link_url' => [
                'label' => 'Link URL',
                'type' => 'url',
                'description' => 'Optional link when clicking the image'
            ],
            'link_target' => [
                'label' => 'Open in New Tab',
                'type' => 'toggle',
                'default' => false,
                'show_if_not' => ['link_url' => '']
            ],
            'align' => [
                'label' => 'Image Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'responsive' => true
            ],
            'width' => [
                'label' => 'Width',
                'type' => 'range',
                'min' => 0,
                'max' => 100,
                'unit' => '%',
                'responsive' => true,
                'description' => 'Image width as percentage'
            ],
            'max_width' => [
                'label' => 'Max Width',
                'type' => 'range',
                'min' => 0,
                'max' => 2000,
                'unit' => 'px',
                'responsive' => true
            ],
            'force_fullwidth' => [
                'label' => 'Force Fullwidth',
                'type' => 'toggle',
                'default' => false,
                'description' => 'Make image fill container width'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $src = $attrs['src'] ?? '';
        $alt = $attrs['alt'] ?? '';
        $title = $attrs['title'] ?? '';
        $linkUrl = $attrs['link_url'] ?? '';
        $linkTarget = !empty($attrs['link_target']) ? ' target="_blank" rel="noopener noreferrer"' : '';

        // Build image or placeholder
        if (!empty($src)) {
            $titleAttr = !empty($title) ? ' title="' . $this->esc($title) . '"' : '';
            $imageHtml = '<img src="' . $this->esc($src) . '" alt="' . $this->esc($alt) . '"' . $titleAttr . ' class="jtb-image-img">';
        } else {
            $imageHtml = '<div class="jtb-image-placeholder">No image selected</div>';
        }

        // Wrap with link if provided
        if (!empty($linkUrl) && !empty($src)) {
            $imageHtml = '<a href="' . $this->esc($linkUrl) . '"' . $linkTarget . ' class="jtb-image-link">' . $imageHtml . '</a>';
        }

        $innerHtml = '<div class="jtb-image-inner">' . $imageHtml . '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for Image module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Alignment (special handling for text-align mapping)
        $align = $attrs['align'] ?? '';
        if (!empty($align)) {
            $css .= $selector . ' .jtb-image-inner { text-align: ' . $align . '; }' . "\n";
        }

        // Responsive alignment
        if (!empty($attrs['align__tablet'])) {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-image-inner { text-align: ' . $attrs['align__tablet'] . '; } }' . "\n";
        }
        if (!empty($attrs['align__phone'])) {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-image-inner { text-align: ' . $attrs['align__phone'] . '; } }' . "\n";
        }

        // Force fullwidth override
        if (!empty($attrs['force_fullwidth'])) {
            $css .= $selector . ' .jtb-image-img { width: 100%; max-width: none; }' . "\n";
        }

        // FIX 2026-02-03: Apply border-radius directly to <img> element
        // Border-radius on wrapper doesn't affect the image inside
        $borderRadius = $this->extractBorderRadius($attrs);
        if (!empty($borderRadius)) {
            $css .= $selector . ' .jtb-image-img { border-radius: ' . $borderRadius . '; }' . "\n";
            // Also apply overflow:hidden to wrapper if image has link
            $css .= $selector . ' .jtb-image-link { overflow: hidden; border-radius: ' . $borderRadius . '; }' . "\n";
        }

        // FIX 2026-02-03: Apply border styles directly to <img> element
        $borderWidth = $this->extractBorderWidth($attrs);
        $borderStyle = $attrs['border_style'] ?? ($attrs['border']['style'] ?? 'none');
        $borderColor = $attrs['border_color'] ?? ($attrs['border']['color'] ?? '');

        if ($borderStyle !== 'none' && !empty($borderColor)) {
            $borderCss = '';
            if (!empty($borderWidth)) {
                $borderCss .= 'border-width: ' . $borderWidth . '; ';
            }
            $borderCss .= 'border-style: ' . $borderStyle . '; ';
            $borderCss .= 'border-color: ' . $borderColor . ';';
            $css .= $selector . ' .jtb-image-img { ' . $borderCss . ' }' . "\n";
        }

        // Parent class handles common styles (spacing, background, etc.)
        // But we skip border generation for the wrapper since we apply it to img
        $css .= $this->generateBackgroundCss($attrs, $selector);
        $css .= $this->generateSpacingCss($attrs, $selector);
        $css .= $this->generateBoxShadowCss($attrs, $selector);
        $css .= $this->generateFiltersCss($attrs, $selector);
        $css .= $this->generateTransformCss($attrs, $selector);
        $css .= $this->generateCustomCss($attrs, $selector);

        return $css;
    }

    /**
     * Extract border-radius value from various attribute formats
     */
    private function extractBorderRadius(array $attrs): string
    {
        // Check combined border object first
        if (!empty($attrs['border']) && is_array($attrs['border']) && isset($attrs['border']['radius'])) {
            return (int)$attrs['border']['radius'] . 'px';
        }

        // Check separate border_radius field
        if (!empty($attrs['border_radius'])) {
            $radius = $attrs['border_radius'];
            if (is_array($radius)) {
                if (isset($radius['top_left'])) {
                    return ($radius['top_left'] ?? 0) . 'px ' . ($radius['top_right'] ?? 0) . 'px ' . ($radius['bottom_right'] ?? 0) . 'px ' . ($radius['bottom_left'] ?? 0) . 'px';
                }
            } elseif (is_numeric($radius)) {
                return (int)$radius . 'px';
            } elseif (is_string($radius)) {
                return $radius;
            }
        }

        return '';
    }

    /**
     * Extract border-width value from various attribute formats
     */
    private function extractBorderWidth(array $attrs): string
    {
        // Check combined border object first
        if (!empty($attrs['border']) && is_array($attrs['border']) && isset($attrs['border']['width'])) {
            return (int)$attrs['border']['width'] . 'px';
        }

        // Check separate border_width field
        if (!empty($attrs['border_width'])) {
            $width = $attrs['border_width'];
            if (is_array($width)) {
                return ($width['top'] ?? 0) . 'px ' . ($width['right'] ?? 0) . 'px ' . ($width['bottom'] ?? 0) . 'px ' . ($width['left'] ?? 0) . 'px';
            } elseif (is_numeric($width)) {
                return (int)$width . 'px';
            }
        }

        return '';
    }
}

// Register module
JTB_Registry::register('image', JTB_Module_Image::class);
