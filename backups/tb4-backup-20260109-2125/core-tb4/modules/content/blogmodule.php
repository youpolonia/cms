<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Blog Module
 * Displays blog posts in grid, list, or masonry layouts
 */
class BlogModule extends Module
{
    public function __construct()
    {
        $this->name = 'Blog';
        $this->slug = 'blog';
        $this->icon = 'newspaper';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-blog-preview',
            'grid' => '.tb4-blog-grid',
            'card' => '.tb4-blog-card',
            'image' => '.tb4-blog-image',
            'content' => '.tb4-blog-content',
            'category' => '.tb4-blog-category',
            'title' => '.tb4-blog-title',
            'excerpt' => '.tb4-blog-excerpt',
            'meta' => '.tb4-blog-meta',
            'read_more' => '.tb4-blog-read-more'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'posts_count' => [
                'label' => 'Number of Posts',
                'type' => 'select',
                'options' => [
                    '3' => '3 Posts',
                    '6' => '6 Posts',
                    '9' => '9 Posts',
                    '12' => '12 Posts'
                ],
                'default' => '6'
            ],
            'layout' => [
                'label' => 'Layout',
                'type' => 'select',
                'options' => [
                    'grid' => 'Grid',
                    'list' => 'List',
                    'masonry' => 'Masonry'
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
                    '4' => '4 Columns'
                ],
                'default' => '3'
            ],
            'show_image' => [
                'label' => 'Show Featured Image',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'image_position' => [
                'label' => 'Image Position',
                'type' => 'select',
                'options' => [
                    'top' => 'Top',
                    'left' => 'Left',
                    'right' => 'Right',
                    'background' => 'Background'
                ],
                'default' => 'top'
            ],
            'show_title' => [
                'label' => 'Show Title',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_excerpt' => [
                'label' => 'Show Excerpt',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'excerpt_length' => [
                'label' => 'Excerpt Length',
                'type' => 'select',
                'options' => [
                    '50' => 'Short (50 words)',
                    '100' => 'Medium (100 words)',
                    '150' => 'Long (150 words)'
                ],
                'default' => '100'
            ],
            'show_date' => [
                'label' => 'Show Date',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_author' => [
                'label' => 'Show Author',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_category' => [
                'label' => 'Show Category',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_read_more' => [
                'label' => 'Show Read More',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'read_more_text' => [
                'label' => 'Read More Text',
                'type' => 'text',
                'default' => 'Read More'
            ],
            'show_pagination' => [
                'label' => 'Show Pagination',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'no'
            ],
            'order_by' => [
                'label' => 'Order By',
                'type' => 'select',
                'options' => [
                    'date_desc' => 'Date (Newest)',
                    'date_asc' => 'Date (Oldest)',
                    'title_asc' => 'Title (A-Z)',
                    'title_desc' => 'Title (Z-A)'
                ],
                'default' => 'date_desc'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'card_background' => [
                'label' => 'Card Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'card_border_color' => [
                'label' => 'Card Border Color',
                'type' => 'color',
                'default' => '#e5e7eb'
            ],
            'card_border_radius' => [
                'label' => 'Card Border Radius',
                'type' => 'text',
                'default' => '12px'
            ],
            'card_shadow' => [
                'label' => 'Card Shadow',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'sm' => 'Small',
                    'md' => 'Medium',
                    'lg' => 'Large'
                ],
                'default' => 'sm'
            ],
            'card_padding' => [
                'label' => 'Card Padding',
                'type' => 'text',
                'default' => '0'
            ],
            'content_padding' => [
                'label' => 'Content Padding',
                'type' => 'text',
                'default' => '20px'
            ],
            'gap' => [
                'label' => 'Gap Between Posts',
                'type' => 'text',
                'default' => '24px'
            ],
            'image_height' => [
                'label' => 'Image Height',
                'type' => 'text',
                'default' => '200px'
            ],
            'image_border_radius' => [
                'label' => 'Image Border Radius',
                'type' => 'text',
                'default' => '12px 12px 0 0'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'text',
                'default' => '20px'
            ],
            'title_hover_color' => [
                'label' => 'Title Hover Color',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'excerpt_color' => [
                'label' => 'Excerpt Color',
                'type' => 'color',
                'default' => '#6b7280'
            ],
            'excerpt_font_size' => [
                'label' => 'Excerpt Font Size',
                'type' => 'text',
                'default' => '14px'
            ],
            'meta_color' => [
                'label' => 'Meta Color',
                'type' => 'color',
                'default' => '#9ca3af'
            ],
            'meta_font_size' => [
                'label' => 'Meta Font Size',
                'type' => 'text',
                'default' => '12px'
            ],
            'category_bg_color' => [
                'label' => 'Category Background',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'category_text_color' => [
                'label' => 'Category Text Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'read_more_color' => [
                'label' => 'Read More Color',
                'type' => 'color',
                'default' => '#2563eb'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return [
            'css_id' => [
                'label' => 'CSS ID',
                'type' => 'text',
                'default' => ''
            ],
            'css_class' => [
                'label' => 'CSS Class',
                'type' => 'text',
                'default' => ''
            ],
            'custom_css' => [
                'label' => 'Custom CSS',
                'type' => 'textarea',
                'default' => ''
            ]
        ];
    }

    /**
     * Get sample blog posts for preview
     */
    private function getSamplePosts(): array
    {
        return [
            [
                'title' => 'Getting Started with Web Design',
                'excerpt' => 'Learn the fundamentals of modern web design and create stunning websites that engage your audience.',
                'category' => 'Design',
                'author' => 'John Doe',
                'date' => 'Jan 5, 2026'
            ],
            [
                'title' => 'Top 10 SEO Tips for 2026',
                'excerpt' => 'Discover the latest SEO strategies to improve your website ranking and drive more organic traffic.',
                'category' => 'Marketing',
                'author' => 'Jane Smith',
                'date' => 'Jan 4, 2026'
            ],
            [
                'title' => 'The Future of AI in Business',
                'excerpt' => 'Explore how artificial intelligence is transforming businesses and creating new opportunities.',
                'category' => 'Technology',
                'author' => 'Mike Johnson',
                'date' => 'Jan 3, 2026'
            ],
            [
                'title' => 'Building a Strong Brand Identity',
                'excerpt' => 'A comprehensive guide to creating a memorable brand that resonates with your target audience.',
                'category' => 'Branding',
                'author' => 'Sarah Wilson',
                'date' => 'Jan 2, 2026'
            ],
            [
                'title' => 'E-commerce Trends to Watch',
                'excerpt' => 'Stay ahead of the competition with these emerging e-commerce trends and strategies.',
                'category' => 'E-commerce',
                'author' => 'Tom Brown',
                'date' => 'Jan 1, 2026'
            ],
            [
                'title' => 'Mastering Social Media Marketing',
                'excerpt' => 'Effective social media strategies to grow your following and increase engagement.',
                'category' => 'Marketing',
                'author' => 'Lisa Davis',
                'date' => 'Dec 31, 2025'
            ]
        ];
    }

    /**
     * Get shadow CSS value
     */
    private function getShadowValue(string $shadow): string
    {
        return match($shadow) {
            'none' => 'none',
            'sm' => '0 1px 3px rgba(0,0,0,0.1)',
            'md' => '0 4px 6px rgba(0,0,0,0.1)',
            'lg' => '0 10px 15px rgba(0,0,0,0.1)',
            default => '0 1px 3px rgba(0,0,0,0.1)'
        };
    }

    public function render(array $settings): string
    {
        // Content fields
        $postsCount = (int)($settings['posts_count'] ?? 6);
        $layout = $settings['layout'] ?? 'grid';
        $columns = $settings['columns'] ?? '3';
        $showImage = ($settings['show_image'] ?? 'yes') === 'yes';
        $imagePosition = $settings['image_position'] ?? 'top';
        $showTitle = ($settings['show_title'] ?? 'yes') === 'yes';
        $showExcerpt = ($settings['show_excerpt'] ?? 'yes') === 'yes';
        $showDate = ($settings['show_date'] ?? 'yes') === 'yes';
        $showAuthor = ($settings['show_author'] ?? 'yes') === 'yes';
        $showCategory = ($settings['show_category'] ?? 'yes') === 'yes';
        $showReadMore = ($settings['show_read_more'] ?? 'yes') === 'yes';
        $readMoreText = $settings['read_more_text'] ?? 'Read More';

        // Design fields
        $cardBg = $settings['card_background'] ?? '#ffffff';
        $cardBorder = $settings['card_border_color'] ?? '#e5e7eb';
        $cardRadius = $settings['card_border_radius'] ?? '12px';
        $cardShadow = $this->getShadowValue($settings['card_shadow'] ?? 'sm');
        $contentPadding = $settings['content_padding'] ?? '20px';
        $gap = $settings['gap'] ?? '24px';
        $imageHeight = $settings['image_height'] ?? '200px';
        $titleColor = $settings['title_color'] ?? '#111827';
        $titleSize = $settings['title_font_size'] ?? '20px';
        $titleHoverColor = $settings['title_hover_color'] ?? '#2563eb';
        $excerptColor = $settings['excerpt_color'] ?? '#6b7280';
        $excerptSize = $settings['excerpt_font_size'] ?? '14px';
        $metaColor = $settings['meta_color'] ?? '#9ca3af';
        $metaSize = $settings['meta_font_size'] ?? '12px';
        $categoryBg = $settings['category_bg_color'] ?? '#2563eb';
        $categoryColor = $settings['category_text_color'] ?? '#ffffff';
        $readMoreColor = $settings['read_more_color'] ?? '#2563eb';

        // Build unique ID for scoped styles
        $uniqueId = 'tb4-blog-' . uniqid();

        // Grid template columns based on layout
        $gridCols = $layout === 'list' ? '1fr' : "repeat($columns, 1fr)";

        // Get sample posts
        $posts = array_slice($this->getSamplePosts(), 0, $postsCount);

        // Build HTML
        $html = '<div class="tb4-blog-preview" id="' . esc_attr($uniqueId) . '">';
        $html .= '<div class="tb4-blog-grid" style="display:grid;grid-template-columns:' . $gridCols . ';gap:' . esc_attr($gap) . ';">';

        foreach ($posts as $post) {
            $cardStyles = [
                'background:' . esc_attr($cardBg),
                'border:1px solid ' . esc_attr($cardBorder),
                'border-radius:' . esc_attr($cardRadius),
                'box-shadow:' . $cardShadow,
                'overflow:hidden',
                'transition:transform 0.2s,box-shadow 0.2s'
            ];

            if ($layout === 'list') {
                $cardStyles[] = 'display:flex';
                $cardStyles[] = 'flex-direction:row';
            }

            $html .= '<div class="tb4-blog-card" style="' . implode(';', $cardStyles) . '">';

            // Image
            if ($showImage) {
                $imgContainerStyle = 'width:100%;height:' . esc_attr($imageHeight) . ';background:linear-gradient(135deg,#e5e7eb 0%,#f3f4f6 100%);display:flex;align-items:center;justify-content:center;color:#9ca3af;';
                if ($layout === 'list') {
                    $imgContainerStyle = 'width:300px;min-width:300px;height:auto;min-height:' . esc_attr($imageHeight) . ';background:linear-gradient(135deg,#e5e7eb 0%,#f3f4f6 100%);display:flex;align-items:center;justify-content:center;color:#9ca3af;';
                }
                $html .= '<div class="tb4-blog-image-placeholder" style="' . $imgContainerStyle . '">';
                $html .= '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>';
                $html .= '</div>';
            }

            // Content
            $contentStyle = 'padding:' . esc_attr($contentPadding) . ';';
            if ($layout === 'list') {
                $contentStyle .= 'flex:1;display:flex;flex-direction:column;justify-content:center;';
            }
            $html .= '<div class="tb4-blog-content" style="' . $contentStyle . '">';

            // Category
            if ($showCategory) {
                $categoryStyle = 'display:inline-block;padding:4px 10px;background:' . esc_attr($categoryBg) . ';color:' . esc_attr($categoryColor) . ';font-size:11px;font-weight:600;text-transform:uppercase;border-radius:4px;margin-bottom:12px;';
                $html .= '<span class="tb4-blog-category" style="' . $categoryStyle . '">' . esc_html($post['category']) . '</span>';
            }

            // Title
            if ($showTitle) {
                $titleStyle = 'font-size:' . esc_attr($titleSize) . ';font-weight:700;color:' . esc_attr($titleColor) . ';margin:0 0 12px 0;line-height:1.3;cursor:pointer;';
                $html .= '<h3 class="tb4-blog-title" style="' . $titleStyle . '">' . esc_html($post['title']) . '</h3>';
            }

            // Meta
            if ($showDate || $showAuthor) {
                $metaStyle = 'display:flex;gap:12px;font-size:' . esc_attr($metaSize) . ';color:' . esc_attr($metaColor) . ';margin-bottom:12px;';
                $html .= '<div class="tb4-blog-meta" style="' . $metaStyle . '">';
                if ($showDate) {
                    $html .= '<span class="tb4-blog-meta-item">' . esc_html($post['date']) . '</span>';
                }
                if ($showAuthor) {
                    $html .= '<span class="tb4-blog-meta-item">by ' . esc_html($post['author']) . '</span>';
                }
                $html .= '</div>';
            }

            // Excerpt
            if ($showExcerpt) {
                $excerptStyle = 'font-size:' . esc_attr($excerptSize) . ';color:' . esc_attr($excerptColor) . ';line-height:1.6;margin:0 0 16px 0;';
                $html .= '<p class="tb4-blog-excerpt" style="' . $excerptStyle . '">' . esc_html($post['excerpt']) . '</p>';
            }

            // Read More
            if ($showReadMore) {
                $readMoreStyle = 'color:' . esc_attr($readMoreColor) . ';font-size:14px;font-weight:600;text-decoration:none;';
                $html .= '<a href="#" class="tb4-blog-read-more" style="' . $readMoreStyle . '">' . esc_html($readMoreText) . ' &rarr;</a>';
            }

            $html .= '</div>'; // Close content
            $html .= '</div>'; // Close card
        }

        $html .= '</div>'; // Close grid
        $html .= '</div>'; // Close preview

        // Add scoped CSS for hover effects
        $html .= '<style>';
        $html .= '#' . $uniqueId . ' .tb4-blog-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.1); }';
        $html .= '#' . $uniqueId . ' .tb4-blog-title:hover { color: ' . esc_attr($titleHoverColor) . '; }';
        $html .= '#' . $uniqueId . ' .tb4-blog-read-more:hover { text-decoration: underline; }';
        $html .= '</style>';

        return $html;
    }
}
