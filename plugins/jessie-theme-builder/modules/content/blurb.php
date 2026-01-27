<?php
/**
 * Blurb Module
 * Content box with icon/image, title and description
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Blurb extends JTB_Element
{
    public string $icon = 'card';
    public string $category = 'content';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = true;
    public bool $use_position = false;
    public bool $use_filters = true;

    public function getSlug(): string
    {
        return 'blurb';
    }

    public function getName(): string
    {
        return 'Blurb';
    }

    public function getFields(): array
    {
        return [
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Your Title Here'
            ],
            'content' => [
                'label' => 'Content',
                'type' => 'richtext',
                'default' => '<p>Your content goes here.</p>'
            ],
            'use_icon' => [
                'label' => 'Use Icon',
                'type' => 'toggle',
                'default' => false
            ],
            'font_icon' => [
                'label' => 'Icon',
                'type' => 'icon_select',
                'show_if' => ['use_icon' => true]
            ],
            'icon_color' => [
                'label' => 'Icon Color',
                'type' => 'color',
                'default' => '#2ea3f2',
                'show_if' => ['use_icon' => true],
                'hover' => true
            ],
            'use_circle' => [
                'label' => 'Circle Icon',
                'type' => 'toggle',
                'default' => false,
                'show_if' => ['use_icon' => true]
            ],
            'circle_color' => [
                'label' => 'Circle Color',
                'type' => 'color',
                'default' => '#2ea3f2',
                'show_if' => ['use_circle' => true],
                'hover' => true
            ],
            'circle_border_color' => [
                'label' => 'Circle Border Color',
                'type' => 'color',
                'show_if' => ['use_circle' => true],
                'hover' => true
            ],
            'image' => [
                'label' => 'Image',
                'type' => 'upload',
                'show_if' => ['use_icon' => false]
            ],
            'alt' => [
                'label' => 'Image Alt Text',
                'type' => 'text',
                'show_if' => ['use_icon' => false]
            ],
            'image_max_width' => [
                'label' => 'Image Max Width',
                'type' => 'range',
                'min' => 0,
                'max' => 500,
                'unit' => 'px',
                'show_if' => ['use_icon' => false],
                'responsive' => true
            ],
            'icon_font_size' => [
                'label' => 'Icon Font Size',
                'type' => 'range',
                'min' => 16,
                'max' => 200,
                'unit' => 'px',
                'default' => 96,
                'show_if' => ['use_icon' => true],
                'responsive' => true
            ],
            'image_placement' => [
                'label' => 'Image/Icon Placement',
                'type' => 'select',
                'options' => [
                    'top' => 'Top',
                    'left' => 'Left'
                ],
                'default' => 'top'
            ],
            'url' => [
                'label' => 'Link URL',
                'type' => 'text'
            ],
            'url_new_window' => [
                'label' => 'Open in New Tab',
                'type' => 'toggle',
                'default' => false
            ],
            'header_level' => [
                'label' => 'Title Heading Level',
                'type' => 'select',
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6'
                ],
                'default' => 'h4'
            ],
            'content_max_width' => [
                'label' => 'Content Max Width',
                'type' => 'range',
                'min' => 0,
                'max' => 800,
                'unit' => 'px',
                'responsive' => true
            ],
            'text_orientation' => [
                'label' => 'Text Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'center',
                'responsive' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $title = $this->esc($attrs['title'] ?? 'Your Title Here');
        $bodyContent = $attrs['content'] ?? '<p>Your content goes here.</p>';
        $useIcon = !empty($attrs['use_icon']);
        $image = $attrs['image'] ?? '';
        $icon = $attrs['font_icon'] ?? '';
        $url = $attrs['url'] ?? '';
        $newWindow = !empty($attrs['url_new_window']) ? ' target="_blank" rel="noopener"' : '';
        $headerLevel = $attrs['header_level'] ?? 'h4';
        $placement = $attrs['image_placement'] ?? 'top';
        $useCircle = !empty($attrs['use_circle']);

        // Image/Icon HTML
        $mediaHtml = '';
        if ($useIcon && !empty($icon)) {
            $circleClass = $useCircle ? ' jtb-blurb-icon-circle' : '';
            $iconSize = (int)($attrs['icon_font_size'] ?? 96);
            $iconSvg = JTB_Icons::get($icon, $iconSize, 2);
            $mediaHtml = '<div class="jtb-blurb-image' . $circleClass . '"><span class="jtb-icon">' . $iconSvg . '</span></div>';
        } elseif (!empty($image)) {
            $alt = $this->esc($attrs['alt'] ?? '');
            $mediaHtml = '<div class="jtb-blurb-image"><img src="' . $this->esc($image) . '" alt="' . $alt . '" /></div>';
        }

        // Wrap media with link if URL exists
        if (!empty($url) && !empty($mediaHtml)) {
            $mediaHtml = '<a href="' . $this->esc($url) . '"' . $newWindow . '>' . $mediaHtml . '</a>';
        }

        // Title HTML
        $titleHtml = '<' . $headerLevel . ' class="jtb-blurb-title">';
        if (!empty($url)) {
            $titleHtml .= '<a href="' . $this->esc($url) . '"' . $newWindow . '>' . $title . '</a>';
        } else {
            $titleHtml .= $title;
        }
        $titleHtml .= '</' . $headerLevel . '>';

        // Build container
        $placementClass = 'jtb-blurb-position-' . $placement;
        $innerHtml = '<div class="jtb-blurb-container ' . $placementClass . '">';
        $innerHtml .= $mediaHtml;
        $innerHtml .= '<div class="jtb-blurb-content">';
        $innerHtml .= $titleHtml;
        $innerHtml .= '<div class="jtb-blurb-description">' . $bodyContent . '</div>';
        $innerHtml .= '</div>';
        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Text alignment
        if (!empty($attrs['text_orientation'])) {
            $css .= $selector . ' .jtb-blurb-container { text-align: ' . $attrs['text_orientation'] . '; }' . "\n";
        }

        // Icon color (supports both font icons with color and SVG icons with stroke)
        if (!empty($attrs['icon_color'])) {
            $css .= $selector . ' .jtb-blurb-image .jtb-icon { color: ' . $attrs['icon_color'] . '; }' . "\n";
            $css .= $selector . ' .jtb-blurb-image .jtb-icon svg { stroke: ' . $attrs['icon_color'] . '; }' . "\n";
        }

        // Icon size
        if (!empty($attrs['icon_font_size'])) {
            $css .= $selector . ' .jtb-blurb-image .jtb-icon { font-size: ' . $attrs['icon_font_size'] . 'px; }' . "\n";
        }

        // Circle styles
        if (!empty($attrs['use_circle'])) {
            $circleColor = $attrs['circle_color'] ?? '#2ea3f2';
            $css .= $selector . ' .jtb-blurb-icon-circle { background-color: ' . $circleColor . '; border-radius: 50%; padding: 20px; display: inline-flex; align-items: center; justify-content: center; }' . "\n";

            if (!empty($attrs['circle_border_color'])) {
                $css .= $selector . ' .jtb-blurb-icon-circle { border: 2px solid ' . $attrs['circle_border_color'] . '; }' . "\n";
            }
        }

        // Image max width
        if (!empty($attrs['image_max_width'])) {
            $css .= $selector . ' .jtb-blurb-image img { max-width: ' . $attrs['image_max_width'] . 'px; }' . "\n";
        }

        // Content max width
        if (!empty($attrs['content_max_width'])) {
            $css .= $selector . ' .jtb-blurb-content { max-width: ' . $attrs['content_max_width'] . 'px; }' . "\n";
        }

        // Left placement layout
        if (($attrs['image_placement'] ?? 'top') === 'left') {
            $css .= $selector . ' .jtb-blurb-position-left { display: flex; align-items: flex-start; }' . "\n";
            $css .= $selector . ' .jtb-blurb-position-left .jtb-blurb-image { margin-right: 20px; flex-shrink: 0; }' . "\n";
        }

        // Hover states
        if (!empty($attrs['icon_color__hover'])) {
            $css .= $selector . ':hover .jtb-blurb-image .jtb-icon { color: ' . $attrs['icon_color__hover'] . '; }' . "\n";
            $css .= $selector . ':hover .jtb-blurb-image .jtb-icon svg { stroke: ' . $attrs['icon_color__hover'] . '; }' . "\n";
        }

        if (!empty($attrs['circle_color__hover'])) {
            $css .= $selector . ':hover .jtb-blurb-icon-circle { background-color: ' . $attrs['circle_color__hover'] . '; }' . "\n";
        }

        // Responsive
        if (!empty($attrs['text_orientation__tablet'])) {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-blurb-container { text-align: ' . $attrs['text_orientation__tablet'] . '; } }' . "\n";
        }
        if (!empty($attrs['text_orientation__phone'])) {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-blurb-container { text-align: ' . $attrs['text_orientation__phone'] . '; } }' . "\n";
        }

        if (!empty($attrs['icon_font_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-blurb-image .jtb-icon { font-size: ' . $attrs['icon_font_size__tablet'] . 'px; } }' . "\n";
        }
        if (!empty($attrs['icon_font_size__phone'])) {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-blurb-image .jtb-icon { font-size: ' . $attrs['icon_font_size__phone'] . 'px; } }' . "\n";
        }

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('blurb', JTB_Module_Blurb::class);
