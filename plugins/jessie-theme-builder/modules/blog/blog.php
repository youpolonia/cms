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

        // Fetch real posts from DB
        $posts = $this->fetchPosts($attrs);

        if (empty($posts)) {
            $innerHtml .= '<p class="jtb-blog-empty">No posts found.</p>';
        }

        foreach ($posts as $post) {
            $postUrl  = '/article/' . $this->esc($post['slug'] ?? '');
            $postDate = !empty($post['published_at']) ? date('F j, Y', strtotime($post['published_at'])) : '';
            $author   = $this->esc($post['author_name'] ?? 'Admin');
            $catName  = $this->esc($post['category_name'] ?? '');

            $innerHtml .= '<article class="jtb-blog-post">';

            // Thumbnail
            if ($showThumbnail) {
                $innerHtml .= '<div class="jtb-blog-thumbnail">';
                $innerHtml .= '<a href="' . $postUrl . '">';
                if (!empty($post['featured_image'])) {
                    $innerHtml .= '<img src="' . $this->esc($post['featured_image']) . '" alt="' . $this->esc($post['featured_image_alt'] ?? $post['title']) . '" loading="lazy">';
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
            $innerHtml .= '<a href="' . $postUrl . '">' . $this->esc($post['title']) . '</a>';
            $innerHtml .= '</' . $headerLevel . '>';

            // Meta
            $meta = [];
            if ($showDate && $postDate) {
                $meta[] = '<span class="jtb-blog-date">' . $postDate . '</span>';
            }
            if ($showAuthor) {
                $meta[] = '<span class="jtb-blog-author">by ' . $author . '</span>';
            }
            if ($showCategories && $catName) {
                $meta[] = '<span class="jtb-blog-categories">' . $catName . '</span>';
            }
            if ($showComments) {
                $commentCount = (int)($post['comment_count'] ?? 0);
                $meta[] = '<span class="jtb-blog-comments">' . $commentCount . ' comment' . ($commentCount !== 1 ? 's' : '') . '</span>';
            }

            if (!empty($meta)) {
                $innerHtml .= '<div class="jtb-blog-meta">' . implode(' <span class="jtb-meta-sep">·</span> ', $meta) . '</div>';
            }

            // Excerpt
            if ($showContent) {
                $excerpt = $post['excerpt'] ?? '';
                if (empty($excerpt) && !empty($post['content'])) {
                    $excerpt = mb_substr(strip_tags($post['content']), 0, 160) . '…';
                }
                $innerHtml .= '<div class="jtb-blog-excerpt">' . $this->esc($excerpt) . '</div>';
            }

            // Read more
            if ($showMore) {
                $innerHtml .= '<a href="' . $postUrl . '" class="jtb-blog-more">Read More</a>';
            }

            $innerHtml .= '</div>';
            $innerHtml .= '</article>';
        }

        $innerHtml .= '</div>';

        // Pagination (simple prev/next based on offset)
        if ($showPagination && !empty($posts)) {
            $postsNumber = (int)($attrs['posts_number'] ?? 10);
            $offset      = (int)($attrs['offset_number'] ?? 0);
            $currentPage = max(1, (int)(($offset / $postsNumber) + 1));
            $totalPosts  = $this->countPosts($attrs);
            $totalPages  = (int)ceil($totalPosts / $postsNumber);

            if ($totalPages > 1) {
                $innerHtml .= '<div class="jtb-blog-pagination">';
                for ($p = 1; $p <= min($totalPages, 10); $p++) {
                    $pageOffset = ($p - 1) * $postsNumber;
                    if ($p === $currentPage) {
                        $innerHtml .= '<span class="jtb-page-number current">' . $p . '</span>';
                    } else {
                        $innerHtml .= '<a href="?blog_offset=' . $pageOffset . '" class="jtb-page-number">' . $p . '</a>';
                    }
                }
                $innerHtml .= '</div>';
            }
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

    /**
     * Fetch real posts from DB
     */
    private function fetchPosts(array $attrs): array
    {
        try {
            $pdo    = db();
            $limit  = max(1, (int)($attrs['posts_number'] ?? 10));
            $offset = (int)($_GET['blog_offset'] ?? $attrs['offset_number'] ?? 0);

            $where  = ["a.status = 'published'"];
            $params = [];

            // Category filter
            if (!empty($attrs['include_categories'])) {
                $catIds = array_filter(array_map('intval', explode(',', $attrs['include_categories'])));
                if ($catIds) {
                    $placeholders = implode(',', array_fill(0, count($catIds), '?'));
                    $where[]  = "a.category_id IN ($placeholders)";
                    $params   = array_merge($params, $catIds);
                }
            }

            $whereClause = implode(' AND ', $where);
            $params[]    = $limit;
            $params[]    = $offset;

            $sql = "
                SELECT a.id, a.slug, a.title, a.excerpt, a.content,
                       a.featured_image, a.featured_image_alt, a.published_at,
                       c.name AS category_name, c.slug AS category_slug,
                       u.display_name AS author_name,
                       (SELECT COUNT(*) FROM comments cm WHERE cm.article_id = a.id AND cm.status = 'approved') AS comment_count
                FROM articles a
                LEFT JOIN article_categories c ON a.category_id = c.id
                LEFT JOIN users u ON a.author_id = u.id
                WHERE $whereClause
                ORDER BY a.published_at DESC
                LIMIT ? OFFSET ?
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Count total posts matching filters (for pagination)
     */
    private function countPosts(array $attrs): int
    {
        try {
            $pdo    = db();
            $where  = ["a.status = 'published'"];
            $params = [];

            if (!empty($attrs['include_categories'])) {
                $catIds = array_filter(array_map('intval', explode(',', $attrs['include_categories'])));
                if ($catIds) {
                    $placeholders = implode(',', array_fill(0, count($catIds), '?'));
                    $where[]  = "a.category_id IN ($placeholders)";
                    $params   = array_merge($params, $catIds);
                }
            }

            $whereClause = implode(' AND ', $where);
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles a WHERE $whereClause");
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();

        } catch (\Throwable $e) {
            return 0;
        }
    }
}

JTB_Registry::register('blog', JTB_Module_Blog::class);
