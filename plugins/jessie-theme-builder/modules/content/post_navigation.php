<?php
/**
 * Post Navigation Module
 * Previous/Next post navigation
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_PostNavigation extends JTB_Element
{
    public string $icon = 'navigation';
    public string $category = 'content';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    public function getSlug(): string
    {
        return 'post_navigation';
    }

    public function getName(): string
    {
        return 'Post Navigation';
    }

    public function getFields(): array
    {
        return [
            'show_featured_image' => [
                'label' => 'Show Featured Image',
                'type' => 'toggle',
                'default' => true
            ],
            'in_same_term' => [
                'label' => 'Same Category Only',
                'type' => 'toggle',
                'default' => false
            ],
            'prev_text' => [
                'label' => 'Previous Label',
                'type' => 'text',
                'default' => 'Previous Post'
            ],
            'next_text' => [
                'label' => 'Next Label',
                'type' => 'text',
                'default' => 'Next Post'
            ],
            // Colors
            'link_color' => [
                'label' => 'Link Color',
                'type' => 'color',
                'default' => '#2ea3f2',
                'hover' => true
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#333333'
            ],
            'label_color' => [
                'label' => 'Label Color',
                'type' => 'color',
                'default' => '#999999'
            ],
            'overlay_color' => [
                'label' => 'Image Overlay',
                'type' => 'color',
                'default' => 'rgba(0,0,0,0.3)'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $showImage = $attrs['show_featured_image'] ?? true;
        $prevText = $this->esc($attrs['prev_text'] ?? 'Previous Post');
        $nextText = $this->esc($attrs['next_text'] ?? 'Next Post');

        // SVG arrow icons
        $arrowLeftIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>';
        $arrowRightIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';

        // Sample navigation data
        $prevPost = [
            'title' => 'The Art of User Experience Design',
            'url' => '#',
            'image' => ''
        ];

        $nextPost = [
            'title' => 'Building Scalable Web Applications',
            'url' => '#',
            'image' => ''
        ];

        $innerHtml = '<div class="jtb-post-navigation-container">';

        // Previous
        $innerHtml .= '<div class="jtb-post-nav-item jtb-post-nav-prev">';
        $innerHtml .= '<a href="' . $this->esc($prevPost['url']) . '" class="jtb-post-nav-link">';

        if ($showImage) {
            $innerHtml .= '<div class="jtb-post-nav-image">';
            if (!empty($prevPost['image'])) {
                $innerHtml .= '<img src="' . $this->esc($prevPost['image']) . '" alt="" />';
            } else {
                $innerHtml .= '<div class="jtb-post-nav-image-placeholder" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>';
            }
            $innerHtml .= '<div class="jtb-post-nav-overlay"></div>';
            $innerHtml .= '</div>';
        }

        $innerHtml .= '<div class="jtb-post-nav-content">';
        $innerHtml .= '<span class="jtb-post-nav-label"><span class="jtb-nav-icon">' . $arrowLeftIcon . '</span> ' . $prevText . '</span>';
        $innerHtml .= '<span class="jtb-post-nav-title">' . $this->esc($prevPost['title']) . '</span>';
        $innerHtml .= '</div>';
        $innerHtml .= '</a>';
        $innerHtml .= '</div>';

        // Next
        $innerHtml .= '<div class="jtb-post-nav-item jtb-post-nav-next">';
        $innerHtml .= '<a href="' . $this->esc($nextPost['url']) . '" class="jtb-post-nav-link">';

        if ($showImage) {
            $innerHtml .= '<div class="jtb-post-nav-image">';
            if (!empty($nextPost['image'])) {
                $innerHtml .= '<img src="' . $this->esc($nextPost['image']) . '" alt="" />';
            } else {
                $innerHtml .= '<div class="jtb-post-nav-image-placeholder" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>';
            }
            $innerHtml .= '<div class="jtb-post-nav-overlay"></div>';
            $innerHtml .= '</div>';
        }

        $innerHtml .= '<div class="jtb-post-nav-content">';
        $innerHtml .= '<span class="jtb-post-nav-label">' . $nextText . ' <span class="jtb-nav-icon">' . $arrowRightIcon . '</span></span>';
        $innerHtml .= '<span class="jtb-post-nav-title">' . $this->esc($nextPost['title']) . '</span>';
        $innerHtml .= '</div>';
        $innerHtml .= '</a>';
        $innerHtml .= '</div>';

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        $showImage = $attrs['show_featured_image'] ?? true;
        $linkColor = $attrs['link_color'] ?? '#2ea3f2';
        $titleColor = $attrs['title_color'] ?? '#333333';
        $labelColor = $attrs['label_color'] ?? '#999999';
        $overlayColor = $attrs['overlay_color'] ?? 'rgba(0,0,0,0.3)';

        // Container
        $css .= $selector . ' .jtb-post-navigation-container { display: flex; gap: 20px; }' . "\n";

        // Item
        $css .= $selector . ' .jtb-post-nav-item { flex: 1; }' . "\n";
        $css .= $selector . ' .jtb-post-nav-link { display: block; text-decoration: none; position: relative; overflow: hidden; }' . "\n";

        if ($showImage) {
            $css .= $selector . ' .jtb-post-nav-link { min-height: 150px; }' . "\n";

            // Image
            $css .= $selector . ' .jtb-post-nav-image { position: absolute; top: 0; left: 0; right: 0; bottom: 0; }' . "\n";
            $css .= $selector . ' .jtb-post-nav-image img, ' . $selector . ' .jtb-post-nav-image-placeholder { width: 100%; height: 100%; object-fit: cover; }' . "\n";
            $css .= $selector . ' .jtb-post-nav-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: ' . $overlayColor . '; transition: background 0.3s ease; }' . "\n";
            $css .= $selector . ' .jtb-post-nav-link:hover .jtb-post-nav-overlay { background: rgba(0,0,0,0.5); }' . "\n";

            // Content over image
            $css .= $selector . ' .jtb-post-nav-content { position: relative; z-index: 1; padding: 30px; display: flex; flex-direction: column; justify-content: center; min-height: 90px; }' . "\n";
            $css .= $selector . ' .jtb-post-nav-label { color: rgba(255,255,255,0.8); }' . "\n";
            $css .= $selector . ' .jtb-post-nav-title { color: #ffffff; }' . "\n";
        } else {
            $css .= $selector . ' .jtb-post-nav-content { padding: 20px; border: 1px solid #eee; }' . "\n";
            $css .= $selector . ' .jtb-post-nav-label { color: ' . $labelColor . '; }' . "\n";
            $css .= $selector . ' .jtb-post-nav-title { color: ' . $titleColor . '; }' . "\n";
            $css .= $selector . ' .jtb-post-nav-link:hover { border-color: ' . $linkColor . '; }' . "\n";
        }

        // Label
        $css .= $selector . ' .jtb-post-nav-label { '
            . 'font-size: 12px; '
            . 'text-transform: uppercase; '
            . 'letter-spacing: 1px; '
            . 'margin-bottom: 8px; '
            . 'display: flex; '
            . 'align-items: center; '
            . 'gap: 6px; '
            . '}' . "\n";
        $css .= $selector . ' .jtb-post-nav-next .jtb-post-nav-label { justify-content: flex-end; }' . "\n";
        $css .= $selector . ' .jtb-nav-icon { display: inline-flex; align-items: center; }' . "\n";
        $css .= $selector . ' .jtb-nav-icon svg { width: 14px; height: 14px; }' . "\n";

        // Title
        $css .= $selector . ' .jtb-post-nav-title { '
            . 'font-size: 18px; '
            . 'font-weight: bold; '
            . 'display: block; '
            . 'transition: color 0.3s ease; '
            . '}' . "\n";

        // Alignment
        $css .= $selector . ' .jtb-post-nav-next { text-align: right; }' . "\n";

        // Hover
        if (!empty($attrs['link_color__hover'])) {
            $css .= $selector . ' .jtb-post-nav-link:hover .jtb-post-nav-title { color: ' . $attrs['link_color__hover'] . '; }' . "\n";
        }

        // Responsive
        $css .= '@media (max-width: 767px) {' . "\n";
        $css .= '  ' . $selector . ' .jtb-post-navigation-container { flex-direction: column; }' . "\n";
        $css .= '  ' . $selector . ' .jtb-post-nav-next { text-align: left; }' . "\n";
        $css .= '}' . "\n";

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('post_navigation', JTB_Module_PostNavigation::class);
