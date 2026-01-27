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

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        $columns = $attrs['columns'] ?? '3';
        $overlayColor = $attrs['overlay_color'] ?? 'rgba(0,0,0,0.3)';

        // Grid layout
        $css .= $selector . ' .jtb-blog-grid { display: grid; gap: 30px; }' . "\n";
        $css .= $selector . ' .jtb-blog-cols-2 { grid-template-columns: repeat(2, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-blog-cols-3 { grid-template-columns: repeat(3, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-blog-cols-4 { grid-template-columns: repeat(4, 1fr); }' . "\n";

        // List layout
        $css .= $selector . ' .jtb-blog-list .jtb-blog-post { margin-bottom: 40px; padding-bottom: 40px; border-bottom: 1px solid #eee; }' . "\n";

        // Thumbnail
        $css .= $selector . ' .jtb-blog-thumbnail { position: relative; overflow: hidden; margin-bottom: 20px; }' . "\n";
        $css .= $selector . ' .jtb-blog-thumbnail img { width: 100%; height: auto; display: block; transition: transform 0.3s ease; }' . "\n";
        $css .= $selector . ' .jtb-blog-thumbnail:hover img { transform: scale(1.05); }' . "\n";

        // Placeholder
        $css .= $selector . ' .jtb-blog-thumbnail-placeholder { background: #f0f0f0; padding-bottom: 60%; }' . "\n";

        // Overlay
        $css .= $selector . ' .jtb-blog-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: ' . $overlayColor . '; opacity: 0; transition: opacity 0.3s ease; }' . "\n";
        $css .= $selector . ' .jtb-blog-thumbnail:hover .jtb-blog-overlay { opacity: 1; }' . "\n";

        // Title
        $css .= $selector . ' .jtb-blog-title { margin: 0 0 10px; }' . "\n";
        $css .= $selector . ' .jtb-blog-title a { text-decoration: none; color: inherit; }' . "\n";
        $css .= $selector . ' .jtb-blog-title a:hover { color: #2ea3f2; }' . "\n";

        // Meta
        $css .= $selector . ' .jtb-blog-meta { font-size: 13px; color: #888; margin-bottom: 15px; }' . "\n";

        // Excerpt
        $css .= $selector . ' .jtb-blog-excerpt { margin-bottom: 15px; }' . "\n";

        // Read more
        $css .= $selector . ' .jtb-blog-more { color: #2ea3f2; text-decoration: none; font-weight: 500; }' . "\n";

        // Pagination
        $css .= $selector . ' .jtb-blog-pagination { display: flex; justify-content: center; gap: 10px; margin-top: 40px; }' . "\n";
        $css .= $selector . ' .jtb-page-number { padding: 8px 15px; border: 1px solid #ddd; text-decoration: none; color: #666; }' . "\n";
        $css .= $selector . ' .jtb-page-number.current { background: #2ea3f2; color: #fff; border-color: #2ea3f2; }' . "\n";

        // Responsive
        $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-blog-grid { grid-template-columns: repeat(2, 1fr); } }' . "\n";
        $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-blog-grid { grid-template-columns: 1fr; } }' . "\n";

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('blog', JTB_Module_Blog::class);
