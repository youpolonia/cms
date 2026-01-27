<?php
/**
 * Related Posts Module
 * Displays related posts based on category/tags
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Related_Posts extends JTB_Element
{
    public string $slug = 'related_posts';
    public string $name = 'Related Posts';
    public string $icon = 'grid';
    public string $category = 'theme';

    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;

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
            'title' => [
                'label' => 'Section Title',
                'type' => 'text',
                'default' => 'Related Posts'
            ],
            'show_title' => [
                'label' => 'Show Section Title',
                'type' => 'toggle',
                'default' => true
            ],
            'posts_count' => [
                'label' => 'Number of Posts',
                'type' => 'range',
                'min' => 2,
                'max' => 8,
                'step' => 1,
                'default' => 3,
                'unit' => ''
            ],
            'columns' => [
                'label' => 'Columns',
                'type' => 'select',
                'options' => [
                    '2' => '2 Columns',
                    '3' => '3 Columns',
                    '4' => '4 Columns'
                ],
                'default' => '3',
                'responsive' => true
            ],
            'relation_type' => [
                'label' => 'Related By',
                'type' => 'select',
                'options' => [
                    'category' => 'Category',
                    'tag' => 'Tags',
                    'both' => 'Category or Tags',
                    'author' => 'Same Author'
                ],
                'default' => 'category'
            ],
            'show_image' => [
                'label' => 'Show Featured Image',
                'type' => 'toggle',
                'default' => true
            ],
            'image_aspect' => [
                'label' => 'Image Aspect Ratio',
                'type' => 'select',
                'options' => [
                    '16/9' => '16:9 (Landscape)',
                    '4/3' => '4:3',
                    '1/1' => '1:1 (Square)',
                    '3/4' => '3:4 (Portrait)'
                ],
                'default' => '16/9'
            ],
            'show_category' => [
                'label' => 'Show Category',
                'type' => 'toggle',
                'default' => true
            ],
            'show_date' => [
                'label' => 'Show Date',
                'type' => 'toggle',
                'default' => true
            ],
            'show_excerpt' => [
                'label' => 'Show Excerpt',
                'type' => 'toggle',
                'default' => false
            ],
            'excerpt_length' => [
                'label' => 'Excerpt Length',
                'type' => 'range',
                'min' => 10,
                'max' => 50,
                'step' => 5,
                'default' => 20,
                'unit' => 'words'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#1f2937',
                'hover' => true
            ],
            'category_color' => [
                'label' => 'Category Color',
                'type' => 'color',
                'default' => '#7c3aed'
            ],
            'meta_color' => [
                'label' => 'Meta Color',
                'type' => 'color',
                'default' => '#6b7280'
            ],
            'card_background' => [
                'label' => 'Card Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'card_border_radius' => [
                'label' => 'Card Border Radius',
                'type' => 'range',
                'min' => 0,
                'max' => 24,
                'step' => 1,
                'default' => 8,
                'unit' => 'px'
            ],
            'card_shadow' => [
                'label' => 'Card Shadow',
                'type' => 'toggle',
                'default' => true
            ],
            'gap' => [
                'label' => 'Gap Between Cards',
                'type' => 'range',
                'min' => 10,
                'max' => 40,
                'step' => 5,
                'default' => 24,
                'unit' => 'px'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $id = $attrs['id'] ?? 'related_posts_' . uniqid();
        $title = $attrs['title'] ?? 'Related Posts';
        $showTitle = $attrs['show_title'] ?? true;
        $postsCount = $attrs['posts_count'] ?? 3;
        $showImage = $attrs['show_image'] ?? true;
        $imageAspect = $attrs['image_aspect'] ?? '16/9';
        $showCategory = $attrs['show_category'] ?? true;
        $showDate = $attrs['show_date'] ?? true;
        $showExcerpt = $attrs['show_excerpt'] ?? false;

        $classes = ['jtb-related-posts'];

        // Placeholder SVG
        $imgSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="225" viewBox="0 0 400 225"><rect fill="#e5e7eb" width="400" height="225"/><g transform="translate(175, 87)"><rect x="3" y="3" width="44" height="44" rx="2" ry="2" fill="none" stroke="#9ca3af" stroke-width="3"/><circle cx="18" cy="18" r="5" fill="#9ca3af"/><polyline points="44 33 33 22 15 40" fill="none" stroke="#9ca3af" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></g></svg>';

        $html = '<div id="' . $this->esc($id) . '" class="' . implode(' ', $classes) . '">';

        if ($showTitle) {
            $html .= '<h3 class="jtb-related-section-title">' . $this->esc($title) . '</h3>';
        }

        $html .= '<div class="jtb-related-grid">';

        // Render placeholder posts
        for ($i = 0; $i < $postsCount; $i++) {
            $html .= '<article class="jtb-related-card">';

            if ($showImage) {
                $html .= '<div class="jtb-related-image" style="aspect-ratio: ' . $this->esc($imageAspect) . ';">';
                $html .= '<a href="#"><img src="data:image/svg+xml,' . rawurlencode($imgSvg) . '" alt="" /></a>';
                $html .= '</div>';
            }

            $html .= '<div class="jtb-related-content">';

            if ($showCategory) {
                $html .= '<a href="#" class="jtb-related-category">Category</a>';
            }

            $html .= '<h4 class="jtb-related-post-title"><a href="#">Related Post Title ' . ($i + 1) . '</a></h4>';

            if ($showDate) {
                $html .= '<span class="jtb-related-date">' . date('M j, Y') . '</span>';
            }

            if ($showExcerpt) {
                $html .= '<p class="jtb-related-excerpt">A brief excerpt from this related post will appear here...</p>';
            }

            $html .= '</div>';
            $html .= '</article>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);

        $columns = $attrs['columns'] ?? '3';
        $gap = $attrs['gap'] ?? 24;
        $titleColor = $attrs['title_color'] ?? '#1f2937';
        $categoryColor = $attrs['category_color'] ?? '#7c3aed';
        $metaColor = $attrs['meta_color'] ?? '#6b7280';
        $cardBg = $attrs['card_background'] ?? '#ffffff';
        $cardRadius = $attrs['card_border_radius'] ?? 8;
        $cardShadow = $attrs['card_shadow'] ?? true;

        // Section title
        $css .= $selector . ' .jtb-related-section-title { ';
        $css .= 'margin: 0 0 24px; ';
        $css .= 'font-size: 24px; ';
        $css .= 'font-weight: 600; ';
        $css .= 'color: ' . $titleColor . '; ';
        $css .= '}' . "\n";

        // Grid
        $css .= $selector . ' .jtb-related-grid { ';
        $css .= 'display: grid; ';
        $css .= 'grid-template-columns: repeat(' . intval($columns) . ', 1fr); ';
        $css .= 'gap: ' . intval($gap) . 'px; ';
        $css .= '}' . "\n";

        // Card
        $css .= $selector . ' .jtb-related-card { ';
        $css .= 'background: ' . $cardBg . '; ';
        $css .= 'border-radius: ' . intval($cardRadius) . 'px; ';
        $css .= 'overflow: hidden; ';
        $css .= 'transition: transform 0.3s ease, box-shadow 0.3s ease; ';
        if ($cardShadow) {
            $css .= 'box-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06); ';
        }
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-related-card:hover { ';
        $css .= 'transform: translateY(-4px); ';
        if ($cardShadow) {
            $css .= 'box-shadow: 0 10px 15px rgba(0,0,0,0.1), 0 4px 6px rgba(0,0,0,0.05); ';
        }
        $css .= '}' . "\n";

        // Image
        $css .= $selector . ' .jtb-related-image { overflow: hidden; }' . "\n";
        $css .= $selector . ' .jtb-related-image img { ';
        $css .= 'width: 100%; ';
        $css .= 'height: 100%; ';
        $css .= 'object-fit: cover; ';
        $css .= 'transition: transform 0.3s ease; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-related-card:hover .jtb-related-image img { transform: scale(1.05); }' . "\n";

        // Content
        $css .= $selector . ' .jtb-related-content { padding: 16px; }' . "\n";

        // Category
        $css .= $selector . ' .jtb-related-category { ';
        $css .= 'display: inline-block; ';
        $css .= 'font-size: 12px; ';
        $css .= 'color: ' . $categoryColor . '; ';
        $css .= 'text-transform: uppercase; ';
        $css .= 'letter-spacing: 0.5px; ';
        $css .= 'margin-bottom: 8px; ';
        $css .= 'text-decoration: none; ';
        $css .= 'font-weight: 500; ';
        $css .= '}' . "\n";

        // Post title
        $css .= $selector . ' .jtb-related-post-title { ';
        $css .= 'margin: 0 0 8px; ';
        $css .= 'font-size: 16px; ';
        $css .= 'font-weight: 600; ';
        $css .= 'line-height: 1.4; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-related-post-title a { ';
        $css .= 'color: ' . $titleColor . '; ';
        $css .= 'text-decoration: none; ';
        $css .= 'transition: color 0.3s ease; ';
        $css .= '}' . "\n";

        if (!empty($attrs['title_color__hover'])) {
            $css .= $selector . ' .jtb-related-post-title a:hover { color: ' . $attrs['title_color__hover'] . '; }' . "\n";
        } else {
            $css .= $selector . ' .jtb-related-post-title a:hover { color: ' . $categoryColor . '; }' . "\n";
        }

        // Date
        $css .= $selector . ' .jtb-related-date { ';
        $css .= 'font-size: 13px; ';
        $css .= 'color: ' . $metaColor . '; ';
        $css .= '}' . "\n";

        // Excerpt
        $css .= $selector . ' .jtb-related-excerpt { ';
        $css .= 'margin: 12px 0 0; ';
        $css .= 'font-size: 14px; ';
        $css .= 'color: ' . $metaColor . '; ';
        $css .= 'line-height: 1.5; ';
        $css .= '}' . "\n";

        // Responsive
        if (!empty($attrs['columns__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-related-grid { grid-template-columns: repeat(' . intval($attrs['columns__tablet']) . ', 1fr); }';
            $css .= ' }' . "\n";
        } else {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-related-grid { grid-template-columns: repeat(2, 1fr); }';
            $css .= ' }' . "\n";
        }

        if (!empty($attrs['columns__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-related-grid { grid-template-columns: repeat(' . intval($attrs['columns__phone']) . ', 1fr); }';
            $css .= ' }' . "\n";
        } else {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-related-grid { grid-template-columns: 1fr; }';
            $css .= ' }' . "\n";
        }

        return $css;
    }
}

JTB_Registry::register('related_posts', JTB_Module_Related_Posts::class);
