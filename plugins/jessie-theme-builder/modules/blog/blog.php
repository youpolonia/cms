<?php
/**
 * Blog Module
 * Blog posts grid/list
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Blog extends JTB_Element
{
    public string $icon = 'blog';
    public string $category = 'blog';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'blog';

    /**
     * Declarative style configuration
     * Maps attribute names to CSS properties and selectors
     * Base styles are in jtb-base-modules.css
     */
    protected array $style_config = [
        // Grid/Layout
        'columns' => [
            'property' => '--blog-columns',
            'selector' => '.jtb-blog-container',
            'responsive' => true
        ],
        'gap' => [
            'property' => 'gap',
            'selector' => '.jtb-blog-grid',
            'unit' => 'px'
        ],
        // Overlay
        'overlay_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-blog-overlay'
        ],
        // Card styling
        'card_background' => [
            'property' => 'background-color',
            'selector' => '.jtb-blog-post'
        ],
        'card_padding' => [
            'property' => 'padding',
            'selector' => '.jtb-blog-content'
        ],
        'card_border_radius' => [
            'property' => 'border-radius',
            'selector' => '.jtb-blog-post',
            'unit' => 'px'
        ],
        // Typography
        'title_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-blog-title',
            'unit' => 'px'
        ],
        'title_color' => [
            'property' => 'color',
            'selector' => '.jtb-blog-title a'
        ],
        'title_hover_color' => [
            'property' => 'color',
            'selector' => '.jtb-blog-title a:hover'
        ],
        'meta_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-blog-meta',
            'unit' => 'px'
        ],
        'meta_color' => [
            'property' => 'color',
            'selector' => '.jtb-blog-meta'
        ],
        'excerpt_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-blog-excerpt',
            'unit' => 'px'
        ],
        'excerpt_color' => [
            'property' => 'color',
            'selector' => '.jtb-blog-excerpt'
        ],
        // Read more button
        'read_more_color' => [
            'property' => 'color',
            'selector' => '.jtb-blog-more'
        ],
        'read_more_hover_color' => [
            'property' => 'color',
            'selector' => '.jtb-blog-more:hover'
        ],
        // Pagination
        'pagination_color' => [
            'property' => 'color',
            'selector' => '.jtb-page-number'
        ],
        'pagination_active_bg' => [
            'property' => 'background-color',
            'selector' => '.jtb-page-number.current'
        ]
    ];

    public function getSlug(): string
    {
        return 'blog';
    }

    public function getName(): string
    {
        return 'Blog';
    }

    public function getFields(): array
    {
        return [
            'fullwidth' => [
                'label' => 'Fullwidth Layout',
                'type' => 'toggle',
                'default' => false
            ],
            'posts_number' => [
                'label' => 'Posts Count',
                'type' => 'range',
                'min' => 1,
                'max' => 50,
                'default' => 10
            ],
            'include_categories' => [
                'label' => 'Include Categories',
                'type' => 'text',
                'description' => 'Comma-separated category IDs'
            ],
            'meta_date' => [
                'label' => 'Show Date',
                'type' => 'toggle',
                'default' => true
            ],
            'meta_author' => [
                'label' => 'Show Author',
                'type' => 'toggle',
                'default' => true
            ],
            'meta_categories' => [
                'label' => 'Show Categories',
                'type' => 'toggle',
                'default' => true
            ],
            'meta_comments' => [
                'label' => 'Show Comments Count',
                'type' => 'toggle',
                'default' => true
            ],
            'show_thumbnail' => [
                'label' => 'Show Featured Image',
                'type' => 'toggle',
                'default' => true
            ],
            'show_content' => [
                'label' => 'Show Content',
                'type' => 'toggle',
                'default' => true
            ],
            'show_more' => [
                'label' => 'Show Read More',
                'type' => 'toggle',
                'default' => true
            ],
            'show_pagination' => [
                'label' => 'Show Pagination',
                'type' => 'toggle',
                'default' => true
            ],
            'offset_number' => [
                'label' => 'Post Offset',
                'type' => 'range',
                'min' => 0,
                'max' => 20,
                'default' => 0
            ],
            'use_overlay' => [
                'label' => 'Image Overlay on Hover',
                'type' => 'toggle',
                'default' => true
            ],
            'overlay_color' => [
                'label' => 'Overlay Color',
                'type' => 'color',
                'default' => 'rgba(0,0,0,0.3)',
                'show_if' => ['use_overlay' => true]
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
                'default' => 'h2'
            ],
            'masonry' => [
                'label' => 'Masonry Layout',
                'type' => 'toggle',
                'default' => false
            ],
            'columns' => [
                'label' => 'Columns (Grid)',
                'type' => 'select',
                'options' => [
                    '1' => '1 Column',
                    '2' => '2 Columns',
                    '3' => '3 Columns',
                    '4' => '4 Columns'
                ],
                'default' => '3',
                'show_if' => ['fullwidth' => true],
                'responsive' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $fullwidth = !empty($attrs['fullwidth']);
        $postsNumber = $attrs['posts_number'] ?? 10;
        $showThumbnail = $attrs['show_thumbnail'] ?? true;
        $showContent = $attrs['show_content'] ?? true;
        $showMore = $attrs['show_more'] ?? true;
        $showPagination = $attrs['show_pagination'] ?? true;
        $showDate = $attrs['meta_date'] ?? true;
        $showAuthor = $attrs['meta_author'] ?? true;
        $showCategories = $attrs['meta_categories'] ?? true;
        $showComments = $attrs['meta_comments'] ?? true;
        $headerLevel = $attrs['header_level'] ?? 'h2';
        $columns = $attrs['columns'] ?? '3';
        $masonry = !empty($attrs['masonry']);
        $useOverlay = !empty($attrs['use_overlay']);

        $layoutClass = $fullwidth ? 'jtb-blog-grid jtb-blog-cols-' . $columns : 'jtb-blog-list';
        if ($masonry) {
            $layoutClass .= ' jtb-blog-masonry';
        }

        $innerHtml = '<div class="jtb-blog-container ' . $layoutClass . '">';

        // Sample blog posts (in real implementation, would fetch from database)
        $samplePosts = [
            ['title' => 'Sample Blog Post Title', 'excerpt' => 'This is a sample excerpt for the blog post. In a real implementation, this would be fetched from your CMS database.', 'date' => date('F j, Y'), 'author' => 'Admin', 'categories' => ['News', 'Updates'], 'comments' => 5, 'image' => ''],
            ['title' => 'Another Blog Post', 'excerpt' => 'Another sample excerpt demonstrating how blog posts will appear in this module.', 'date' => date('F j, Y', strtotime('-1 day')), 'author' => 'Admin', 'categories' => ['Tutorial'], 'comments' => 3, 'image' => ''],
            ['title' => 'Third Post Example', 'excerpt' => 'A third sample post to show the grid layout functionality.', 'date' => date('F j, Y', strtotime('-2 days')), 'author' => 'Admin', 'categories' => ['News'], 'comments' => 0, 'image' => ''],
        ];

        foreach ($samplePosts as $post) {
            $innerHtml .= '<article class="jtb-blog-post">';

            // Thumbnail
            if ($showThumbnail) {
                $innerHtml .= '<div class="jtb-blog-thumbnail">';
                $innerHtml .= '<a href="#">';
                if (!empty($post['image'])) {
                    $innerHtml .= '<img src="' . $this->esc($post['image']) . '" alt="' . $this->esc($post['title']) . '" />';
                } else {
                    $innerHtml .= '<div class="jtb-blog-thumbnail-placeholder"></div>';
                }
                if ($useOverlay) {
                    $innerHtml .= '<div class="jtb-blog-overlay"></div>';
                }
                $innerHtml .= '</a>';
                $innerHtml .= '</div>';
            }

            $innerHtml .= '<div class="jtb-blog-content">';

            // Title
            $innerHtml .= '<' . $headerLevel . ' class="jtb-blog-title">';
            $innerHtml .= '<a href="#">' . $this->esc($post['title']) . '</a>';
            $innerHtml .= '</' . $headerLevel . '>';

            // Meta
            $meta = [];
            if ($showDate) {
                $meta[] = '<span class="jtb-blog-date">' . $post['date'] . '</span>';
            }
            if ($showAuthor) {
                $meta[] = '<span class="jtb-blog-author">by ' . $this->esc($post['author']) . '</span>';
            }
            if ($showCategories && !empty($post['categories'])) {
                $meta[] = '<span class="jtb-blog-categories">' . implode(', ', array_map([$this, 'esc'], $post['categories'])) . '</span>';
            }
            if ($showComments) {
                $meta[] = '<span class="jtb-blog-comments">' . $post['comments'] . ' comments</span>';
            }

            if (!empty($meta)) {
                $innerHtml .= '<div class="jtb-blog-meta">' . implode(' | ', $meta) . '</div>';
            }

            // Excerpt
            if ($showContent) {
                $innerHtml .= '<div class="jtb-blog-excerpt">' . $this->esc($post['excerpt']) . '</div>';
            }

            // Read more
            if ($showMore) {
                $innerHtml .= '<a href="#" class="jtb-blog-more">Read More</a>';
            }

            $innerHtml .= '</div>';
            $innerHtml .= '</article>';
        }

        $innerHtml .= '</div>';

        // Pagination
        if ($showPagination) {
            $innerHtml .= '<div class="jtb-blog-pagination">';
            $innerHtml .= '<span class="jtb-page-number current">1</span>';
            $innerHtml .= '<a href="#" class="jtb-page-number">2</a>';
            $innerHtml .= '<a href="#" class="jtb-page-number">3</a>';
            $innerHtml .= '<a href="#" class="jtb-page-next">Next »</a>';
            $innerHtml .= '</div>';
        }

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for Blog module
     *
     * Base styles are defined in jtb-base-modules.css using CSS variables.
     * This method only generates CSS for values that differ from defaults.
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Handle columns as CSS variable
        $columns = $attrs['columns'] ?? 3;
        if ($this->isDifferentFromDefault('blog_columns', $columns)) {
            $css .= $selector . ' .jtb-blog-container { --blog-columns: ' . intval($columns) . '; }' . "\n";
        }

        // Responsive columns
        if (!empty($attrs['columns__tablet'])) {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-blog-container { --blog-columns: ' . intval($attrs['columns__tablet']) . '; } }' . "\n";
        }

        if (!empty($attrs['columns__phone'])) {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-blog-container { --blog-columns: ' . intval($attrs['columns__phone']) . '; } }' . "\n";
        }

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('blog', JTB_Module_Blog::class);
