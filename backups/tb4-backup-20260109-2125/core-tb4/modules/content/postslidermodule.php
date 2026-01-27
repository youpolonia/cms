<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Post Slider Module
 * Displays blog posts in a carousel/slider format
 */
class PostSliderModule extends Module
{
    public function __construct()
    {
        $this->name = 'Post Slider';
        $this->slug = 'post_slider';
        $this->icon = 'gallery-horizontal';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-post-slider-preview',
            'container' => '.tb4-post-slider-container',
            'track' => '.tb4-post-slider-track',
            'slide' => '.tb4-post-slide',
            'card' => '.tb4-post-slide-card',
            'image' => '.tb4-post-slide-image',
            'content' => '.tb4-post-slide-content',
            'category' => '.tb4-post-slide-category',
            'title' => '.tb4-post-slide-title',
            'excerpt' => '.tb4-post-slide-excerpt',
            'meta' => '.tb4-post-slide-meta',
            'arrows' => '.tb4-post-slider-arrows',
            'arrow' => '.tb4-post-slider-arrow',
            'dots' => '.tb4-post-slider-dots',
            'dot' => '.tb4-post-slider-dot'
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
                    '4' => '4 Posts',
                    '5' => '5 Posts',
                    '6' => '6 Posts',
                    '8' => '8 Posts'
                ],
                'default' => '4'
            ],
            'visible_posts' => [
                'label' => 'Visible Posts',
                'type' => 'select',
                'options' => [
                    '1' => '1 Post',
                    '2' => '2 Posts',
                    '3' => '3 Posts',
                    '4' => '4 Posts'
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
                'default' => 'no'
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
            'show_arrows' => [
                'label' => 'Show Arrows',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_dots' => [
                'label' => 'Show Dots',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'autoplay' => [
                'label' => 'Autoplay',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'no'
            ],
            'autoplay_speed' => [
                'label' => 'Autoplay Speed',
                'type' => 'select',
                'options' => [
                    '3000' => '3 seconds',
                    '5000' => '5 seconds',
                    '7000' => '7 seconds'
                ],
                'default' => '5000'
            ],
            'loop' => [
                'label' => 'Loop',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'card_style' => [
                'label' => 'Card Style',
                'type' => 'select',
                'options' => [
                    'card' => 'Card',
                    'overlay' => 'Overlay',
                    'minimal' => 'Minimal'
                ],
                'default' => 'card'
            ],
            'card_background' => [
                'label' => 'Card Background',
                'type' => 'color',
                'default' => '#ffffff'
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
            'gap' => [
                'label' => 'Gap Between Cards',
                'type' => 'text',
                'default' => '24px'
            ],
            'image_height' => [
                'label' => 'Image Height',
                'type' => 'text',
                'default' => '200px'
            ],
            'content_padding' => [
                'label' => 'Content Padding',
                'type' => 'text',
                'default' => '20px'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'text',
                'default' => '18px'
            ],
            'excerpt_color' => [
                'label' => 'Excerpt Color',
                'type' => 'color',
                'default' => '#6b7280'
            ],
            'meta_color' => [
                'label' => 'Meta Color',
                'type' => 'color',
                'default' => '#9ca3af'
            ],
            'category_bg' => [
                'label' => 'Category Background',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'category_color' => [
                'label' => 'Category Text',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'arrow_color' => [
                'label' => 'Arrow Color',
                'type' => 'color',
                'default' => '#374151'
            ],
            'arrow_bg' => [
                'label' => 'Arrow Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'dot_color' => [
                'label' => 'Dot Color',
                'type' => 'color',
                'default' => '#d1d5db'
            ],
            'dot_active_color' => [
                'label' => 'Active Dot Color',
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
                'excerpt' => 'Learn the fundamentals of modern web design.',
                'category' => 'Design',
                'author' => 'John Doe',
                'date' => 'Jan 5, 2026'
            ],
            [
                'title' => 'Top 10 SEO Tips for 2026',
                'excerpt' => 'Discover the latest SEO strategies to improve rankings.',
                'category' => 'Marketing',
                'author' => 'Jane Smith',
                'date' => 'Jan 4, 2026'
            ],
            [
                'title' => 'The Future of AI in Business',
                'excerpt' => 'Explore how AI is transforming businesses.',
                'category' => 'Technology',
                'author' => 'Mike Johnson',
                'date' => 'Jan 3, 2026'
            ],
            [
                'title' => 'Building a Strong Brand',
                'excerpt' => 'A guide to creating a memorable brand identity.',
                'category' => 'Branding',
                'author' => 'Sarah Wilson',
                'date' => 'Jan 2, 2026'
            ],
            [
                'title' => 'E-commerce Trends to Watch',
                'excerpt' => 'Stay ahead with emerging e-commerce trends.',
                'category' => 'E-commerce',
                'author' => 'Tom Brown',
                'date' => 'Jan 1, 2026'
            ],
            [
                'title' => 'Social Media Marketing Tips',
                'excerpt' => 'Effective strategies to grow your following.',
                'category' => 'Marketing',
                'author' => 'Lisa Davis',
                'date' => 'Dec 31, 2025'
            ],
            [
                'title' => 'UX Design Best Practices',
                'excerpt' => 'Create intuitive user experiences that convert.',
                'category' => 'Design',
                'author' => 'Chris Lee',
                'date' => 'Dec 30, 2025'
            ],
            [
                'title' => 'Content Strategy Guide',
                'excerpt' => 'Plan and execute a winning content strategy.',
                'category' => 'Marketing',
                'author' => 'Amy Chen',
                'date' => 'Dec 29, 2025'
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
        $postsCount = (int)($settings['posts_count'] ?? 4);
        $visiblePosts = (int)($settings['visible_posts'] ?? 3);
        $showImage = ($settings['show_image'] ?? 'yes') === 'yes';
        $showTitle = ($settings['show_title'] ?? 'yes') === 'yes';
        $showExcerpt = ($settings['show_excerpt'] ?? 'yes') === 'yes';
        $showDate = ($settings['show_date'] ?? 'yes') === 'yes';
        $showAuthor = ($settings['show_author'] ?? 'no') === 'yes';
        $showCategory = ($settings['show_category'] ?? 'yes') === 'yes';
        $showArrows = ($settings['show_arrows'] ?? 'yes') === 'yes';
        $showDots = ($settings['show_dots'] ?? 'yes') === 'yes';
        $autoplay = ($settings['autoplay'] ?? 'no') === 'yes';
        $autoplaySpeed = $settings['autoplay_speed'] ?? '5000';
        $loop = ($settings['loop'] ?? 'yes') === 'yes';

        // Design fields
        $cardStyle = $settings['card_style'] ?? 'card';
        $cardBg = $settings['card_background'] ?? '#ffffff';
        $cardRadius = $settings['card_border_radius'] ?? '12px';
        $cardShadow = $this->getShadowValue($settings['card_shadow'] ?? 'sm');
        $gap = $settings['gap'] ?? '24px';
        $imageHeight = $settings['image_height'] ?? '200px';
        $contentPadding = $settings['content_padding'] ?? '20px';
        $titleColor = $settings['title_color'] ?? '#111827';
        $titleSize = $settings['title_font_size'] ?? '18px';
        $excerptColor = $settings['excerpt_color'] ?? '#6b7280';
        $metaColor = $settings['meta_color'] ?? '#9ca3af';
        $categoryBg = $settings['category_bg'] ?? '#2563eb';
        $categoryColor = $settings['category_color'] ?? '#ffffff';
        $arrowColor = $settings['arrow_color'] ?? '#374151';
        $arrowBg = $settings['arrow_bg'] ?? '#ffffff';
        $dotColor = $settings['dot_color'] ?? '#d1d5db';
        $dotActiveColor = $settings['dot_active_color'] ?? '#2563eb';

        // Calculate slide width
        $slideWidth = 100 / $visiblePosts;
        $gapNum = (int)preg_replace('/[^0-9]/', '', $gap);

        // Build unique ID for scoped styles
        $uniqueId = 'tb4-post-slider-' . uniqid();

        // Get sample posts
        $posts = array_slice($this->getSamplePosts(), 0, $postsCount);

        // Build HTML
        $html = '<div class="tb4-post-slider-preview" id="' . esc_attr($uniqueId) . '" data-current="0" data-visible="' . $visiblePosts . '" data-total="' . $postsCount . '" data-autoplay="' . ($autoplay ? 'true' : 'false') . '" data-speed="' . esc_attr($autoplaySpeed) . '" data-loop="' . ($loop ? 'true' : 'false') . '">';

        $html .= '<div class="tb4-post-slider-container" style="position:relative;overflow:hidden;">';
        $html .= '<div class="tb4-post-slider-track" style="display:flex;transition:transform 0.5s ease;">';

        foreach ($posts as $index => $post) {
            $html .= '<div class="tb4-post-slide" style="flex-shrink:0;width:' . $slideWidth . '%;padding:0 ' . ($gapNum / 2) . 'px;box-sizing:border-box;">';
            $html .= '<div class="tb4-post-slide-card" style="background:' . esc_attr($cardBg) . ';border-radius:' . esc_attr($cardRadius) . ';overflow:hidden;box-shadow:' . $cardShadow . ';height:100%;">';

            // Image
            if ($showImage) {
                $html .= '<div class="tb4-post-slide-image" style="width:100%;height:' . esc_attr($imageHeight) . ';background:linear-gradient(135deg,#e5e7eb 0%,#f3f4f6 100%);display:flex;align-items:center;justify-content:center;color:#9ca3af;position:relative;">';
                $html .= '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>';

                if ($showCategory) {
                    $html .= '<span class="tb4-post-slide-category" style="position:absolute;top:12px;left:12px;padding:4px 10px;background:' . esc_attr($categoryBg) . ';color:' . esc_attr($categoryColor) . ';font-size:11px;font-weight:600;text-transform:uppercase;border-radius:4px;">' . esc_html($post['category']) . '</span>';
                }
                $html .= '</div>';
            }

            // Content
            $html .= '<div class="tb4-post-slide-content" style="padding:' . esc_attr($contentPadding) . ';">';

            // Meta
            if ($showDate || $showAuthor) {
                $html .= '<div class="tb4-post-slide-meta" style="display:flex;gap:12px;font-size:12px;color:' . esc_attr($metaColor) . ';margin-bottom:8px;">';
                if ($showDate) {
                    $html .= '<span>' . esc_html($post['date']) . '</span>';
                }
                if ($showAuthor) {
                    $html .= '<span>by ' . esc_html($post['author']) . '</span>';
                }
                $html .= '</div>';
            }

            // Title
            if ($showTitle) {
                $html .= '<h4 class="tb4-post-slide-title" style="font-size:' . esc_attr($titleSize) . ';font-weight:700;color:' . esc_attr($titleColor) . ';margin:0 0 8px 0;line-height:1.4;">' . esc_html($post['title']) . '</h4>';
            }

            // Excerpt
            if ($showExcerpt) {
                $html .= '<p class="tb4-post-slide-excerpt" style="font-size:14px;color:' . esc_attr($excerptColor) . ';line-height:1.5;margin:0;">' . esc_html($post['excerpt']) . '</p>';
            }

            $html .= '</div>'; // Close content
            $html .= '</div>'; // Close card
            $html .= '</div>'; // Close slide
        }

        $html .= '</div>'; // Close track
        $html .= '</div>'; // Close container

        // Arrows
        if ($showArrows && $postsCount > $visiblePosts) {
            $html .= '<div class="tb4-post-slider-arrows" style="position:absolute;top:50%;left:0;right:0;transform:translateY(-50%);display:flex;justify-content:space-between;padding:0 8px;pointer-events:none;z-index:10;">';
            $html .= '<button class="tb4-post-slider-arrow tb4-post-slider-prev" style="width:40px;height:40px;border-radius:50%;background:' . esc_attr($arrowBg) . ';color:' . esc_attr($arrowColor) . ';border:1px solid #e5e7eb;cursor:pointer;pointer-events:auto;font-size:18px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.1);">&#8249;</button>';
            $html .= '<button class="tb4-post-slider-arrow tb4-post-slider-next" style="width:40px;height:40px;border-radius:50%;background:' . esc_attr($arrowBg) . ';color:' . esc_attr($arrowColor) . ';border:1px solid #e5e7eb;cursor:pointer;pointer-events:auto;font-size:18px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.1);">&#8250;</button>';
            $html .= '</div>';
        }

        // Dots
        if ($showDots && $postsCount > $visiblePosts) {
            $totalDots = (int)ceil($postsCount / $visiblePosts);
            $html .= '<div class="tb4-post-slider-dots" style="display:flex;justify-content:center;gap:8px;margin-top:20px;">';
            for ($i = 0; $i < $totalDots; $i++) {
                $isActive = $i === 0;
                $dotStyle = $isActive
                    ? 'width:24px;border-radius:4px;background:' . esc_attr($dotActiveColor)
                    : 'width:8px;border-radius:50%;background:' . esc_attr($dotColor);
                $html .= '<button class="tb4-post-slider-dot' . ($isActive ? ' active' : '') . '" data-index="' . $i . '" style="height:8px;' . $dotStyle . ';border:none;cursor:pointer;transition:all 0.2s;"></button>';
            }
            $html .= '</div>';
        }

        $html .= '</div>'; // Close preview

        // Add scoped CSS for hover effects
        $html .= '<style>';
        $html .= '#' . $uniqueId . ' .tb4-post-slide-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.1); }';
        $html .= '#' . $uniqueId . ' .tb4-post-slider-arrow:hover { background: #f9fafb; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }';
        $html .= '</style>';

        return $html;
    }
}
