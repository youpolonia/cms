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

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Alignment
        $align = $attrs['align'] ?? '';
        if (!empty($align)) {
            if ($align === 'center') {
                $css .= $selector . ' .jtb-image-inner { text-align: center; }' . "\n";
            } elseif ($align === 'right') {
                $css .= $selector . ' .jtb-image-inner { text-align: right; }' . "\n";
            } else {
                $css .= $selector . ' .jtb-image-inner { text-align: left; }' . "\n";
            }
        }

        // Width
        if (!empty($attrs['width'])) {
            $css .= $selector . ' .jtb-image-img { width: ' . (int) $attrs['width'] . '%; }' . "\n";
        }

        // Max width
        if (!empty($attrs['max_width'])) {
            $css .= $selector . ' .jtb-image-img { max-width: ' . (int) $attrs['max_width'] . 'px; }' . "\n";
        }

        // Force fullwidth
        if (!empty($attrs['force_fullwidth'])) {
            $css .= $selector . ' .jtb-image-img { width: 100%; max-width: none; }' . "\n";
        }

        // Responsive alignment
        if (!empty($attrs['align__tablet'])) {
            $alignTablet = $attrs['align__tablet'];
            $textAlign = ($alignTablet === 'center') ? 'center' : (($alignTablet === 'right') ? 'right' : 'left');
            $css .= '@media (max-width: 980px) {' . "\n";
            $css .= '  ' . $selector . ' .jtb-image-inner { text-align: ' . $textAlign . '; }' . "\n";
            $css .= '}' . "\n";
        }

        if (!empty($attrs['align__phone'])) {
            $alignPhone = $attrs['align__phone'];
            $textAlign = ($alignPhone === 'center') ? 'center' : (($alignPhone === 'right') ? 'right' : 'left');
            $css .= '@media (max-width: 767px) {' . "\n";
            $css .= '  ' . $selector . ' .jtb-image-inner { text-align: ' . $textAlign . '; }' . "\n";
            $css .= '}' . "\n";
        }

        // Responsive width
        if (!empty($attrs['width__tablet'])) {
            $css .= '@media (max-width: 980px) {' . "\n";
            $css .= '  ' . $selector . ' .jtb-image-img { width: ' . (int) $attrs['width__tablet'] . '%; }' . "\n";
            $css .= '}' . "\n";
        }

        if (!empty($attrs['width__phone'])) {
            $css .= '@media (max-width: 767px) {' . "\n";
            $css .= '  ' . $selector . ' .jtb-image-img { width: ' . (int) $attrs['width__phone'] . '%; }' . "\n";
            $css .= '}' . "\n";
        }

        // Responsive max width
        if (!empty($attrs['max_width__tablet'])) {
            $css .= '@media (max-width: 980px) {' . "\n";
            $css .= '  ' . $selector . ' .jtb-image-img { max-width: ' . (int) $attrs['max_width__tablet'] . 'px; }' . "\n";
            $css .= '}' . "\n";
        }

        if (!empty($attrs['max_width__phone'])) {
            $css .= '@media (max-width: 767px) {' . "\n";
            $css .= '  ' . $selector . ' .jtb-image-img { max-width: ' . (int) $attrs['max_width__phone'] . 'px; }' . "\n";
            $css .= '}' . "\n";
        }

        // Parent CSS
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

// Register module
JTB_Registry::register('image', JTB_Module_Image::class);
