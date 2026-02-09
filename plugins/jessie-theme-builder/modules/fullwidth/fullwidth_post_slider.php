<?php
/**
 * Fullwidth Post Slider Module
 * Full-width blog post carousel
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_FullwidthPostSlider extends JTB_Element
{
    public string $icon = 'post-slider-fullwidth';
    public string $category = 'fullwidth';

    public bool $use_typography = true;
    public bool $use_background = false;
    public bool $use_spacing = true;
    public bool $use_border = false;
    public bool $use_box_shadow = false;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'fullwidth_post_slider';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'slider_height' => [
            'property' => 'height',
            'selector' => '.jtb-post-slider-wrapper',
            'unit' => 'px',
            'responsive' => true
        ],
        'overlay_color' => [
            'property' => 'background',
            'selector' => '.jtb-post-slide-overlay'
        ],
        'title_color' => [
            'property' => 'color',
            'selector' => '.jtb-post-slide-title'
        ],
        'meta_color' => [
            'property' => 'color',
            'selector' => '.jtb-post-slide-meta, .jtb-post-category, .jtb-post-date'
        ],
        'excerpt_color' => [
            'property' => 'color',
            'selector' => '.jtb-post-slide-excerpt'
        ],
        'arrows_color' => [
            'property' => 'color',
            'selector' => '.jtb-post-slider-arrow'
        ],
        'dots_color' => [
            'property' => 'background',
            'selector' => '.jtb-post-slider-pagination .jtb-slider-dot'
        ],
        'dots_active_color' => [
            'property' => 'background',
            'selector' => '.jtb-post-slider-pagination .jtb-slider-dot.active'
        ]
    ];

    public function getSlug(): string
    {
        return 'fullwidth_post_slider';
    }

    public function getName(): string
    {
        return 'Fullwidth Post Slider';
    }

    public function getFields(): array
    {
        return [
            'posts_number' => [
                'label' => 'Number of Posts',
                'type' => 'range',
                'min' => 1,
                'max' => 15,
                'default' => 5
            ],
            'category' => [
                'label' => 'Category',
                'type' => 'select',
                'options' => [
                    'all' => 'All Categories',
                    'news' => 'News',
                    'tutorials' => 'Tutorials',
                    'reviews' => 'Reviews'
                ],
                'default' => 'all'
            ],
            'orderby' => [
                'label' => 'Order By',
                'type' => 'select',
                'options' => [
                    'date' => 'Date',
                    'title' => 'Title',
                    'random' => 'Random',
                    'comment_count' => 'Comments'
                ],
                'default' => 'date'
            ],
            'show_arrows' => [
                'label' => 'Show Arrows',
                'type' => 'toggle',
                'default' => true
            ],
            'show_pagination' => [
                'label' => 'Show Pagination',
                'type' => 'toggle',
                'default' => true
            ],
            'auto_play' => [
                'label' => 'Auto Play',
                'type' => 'toggle',
                'default' => true
            ],
            'auto_speed' => [
                'label' => 'Auto Play Speed',
                'type' => 'range',
                'min' => 2000,
                'max' => 10000,
                'unit' => 'ms',
                'default' => 5000,
                'show_if' => ['auto_play' => true]
            ],
            'show_meta' => [
                'label' => 'Show Meta',
                'type' => 'toggle',
                'default' => true
            ],
            'show_excerpt' => [
                'label' => 'Show Excerpt',
                'type' => 'toggle',
                'default' => true
            ],
            'use_overlay' => [
                'label' => 'Use Overlay',
                'type' => 'toggle',
                'default' => true
            ],
            'slider_height' => [
                'label' => 'Slider Height',
                'type' => 'range',
                'min' => 300,
                'max' => 800,
                'unit' => 'px',
                'default' => 500,
                'responsive' => true
            ],
            // Colors
            'overlay_color' => [
                'label' => 'Overlay Color',
                'type' => 'color',
                'default' => 'rgba(0,0,0,0.4)'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'meta_color' => [
                'label' => 'Meta Color',
                'type' => 'color',
                'default' => 'rgba(255,255,255,0.8)'
            ],
            'excerpt_color' => [
                'label' => 'Excerpt Color',
                'type' => 'color',
                'default' => 'rgba(255,255,255,0.9)'
            ],
            'arrows_color' => [
                'label' => 'Arrows Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'dots_color' => [
                'label' => 'Dots Color',
                'type' => 'color',
                'default' => 'rgba(255,255,255,0.5)'
            ],
            'dots_active_color' => [
                'label' => 'Active Dot Color',
                'type' => 'color',
                'default' => '#ffffff'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $postsNumber = $attrs['posts_number'] ?? 5;
        $showArrows = $attrs['show_arrows'] ?? true;
        $showPagination = $attrs['show_pagination'] ?? true;
        $showMeta = $attrs['show_meta'] ?? true;
        $showExcerpt = $attrs['show_excerpt'] ?? true;
        $useOverlay = $attrs['use_overlay'] ?? true;
        $autoPlay = !empty($attrs['auto_play']);
        $autoSpeed = $attrs['auto_speed'] ?? 5000;

        // Sample posts
        $posts = [
            [
                'title' => 'The Future of Web Development',
                'excerpt' => 'Exploring the latest trends and technologies shaping the future of web development.',
                'date' => 'January 15, 2025',
                'author' => 'John Doe',
                'category' => 'Technology'
            ],
            [
                'title' => 'Design Principles for Modern UIs',
                'excerpt' => 'Learn the fundamental design principles that create intuitive user interfaces.',
                'date' => 'January 12, 2025',
                'author' => 'Jane Smith',
                'category' => 'Design'
            ],
            [
                'title' => 'Building Scalable Applications',
                'excerpt' => 'Best practices for building applications that can grow with your business.',
                'date' => 'January 10, 2025',
                'author' => 'Mike Johnson',
                'category' => 'Development'
            ],
            [
                'title' => 'The Art of User Experience',
                'excerpt' => 'How to create memorable experiences that keep users coming back.',
                'date' => 'January 8, 2025',
                'author' => 'Sarah Wilson',
                'category' => 'UX'
            ],
            [
                'title' => 'Mobile First Approach',
                'excerpt' => 'Why designing for mobile first leads to better overall experiences.',
                'date' => 'January 5, 2025',
                'author' => 'Tom Brown',
                'category' => 'Mobile'
            ]
        ];

        $sliderId = 'jtb-fullwidth-post-slider-' . $this->generateId();

        $sliderData = json_encode([
            'autoplay' => $autoPlay,
            'autoplaySpeed' => $autoSpeed
        ]);

        $containerClass = 'jtb-fullwidth-post-slider-container';
        if ($useOverlay) {
            $containerClass .= ' jtb-use-overlay';
        }

        $innerHtml = '<div class="' . $containerClass . '" id="' . $sliderId . '" data-slider="' . $this->esc($sliderData) . '">';
        $innerHtml .= '<div class="jtb-post-slider-wrapper">';
        $innerHtml .= '<div class="jtb-post-slider-track">';

        $count = 0;
        foreach ($posts as $post) {
            if ($count >= $postsNumber) break;

            $bgColor = 'hsl(' . ($count * 60 + 200) . ', 50%, 40%)';

            $innerHtml .= '<div class="jtb-post-slide" style="background: ' . $bgColor . ';">';
            $innerHtml .= '<div class="jtb-post-slide-overlay"></div>';
            $innerHtml .= '<div class="jtb-post-slide-content">';

            if ($showMeta) {
                $innerHtml .= '<div class="jtb-post-slide-meta">';
                $innerHtml .= '<span class="jtb-post-category">' . $this->esc($post['category']) . '</span>';
                $innerHtml .= '<span class="jtb-post-date">' . $this->esc($post['date']) . '</span>';
                $innerHtml .= '</div>';
            }

            $innerHtml .= '<h2 class="jtb-post-slide-title">' . $this->esc($post['title']) . '</h2>';

            if ($showExcerpt) {
                $innerHtml .= '<p class="jtb-post-slide-excerpt">' . $this->esc($post['excerpt']) . '</p>';
            }

            $innerHtml .= '<a href="#" class="jtb-button jtb-post-slide-button">Read More</a>';

            $innerHtml .= '</div>';
            $innerHtml .= '</div>';

            $count++;
        }

        $innerHtml .= '</div>';
        $innerHtml .= '</div>';

        // Arrows
        if ($showArrows) {
            $innerHtml .= '<button class="jtb-post-slider-arrow jtb-slider-prev" aria-label="Previous">‹</button>';
            $innerHtml .= '<button class="jtb-post-slider-arrow jtb-slider-next" aria-label="Next">›</button>';
        }

        // Pagination
        if ($showPagination) {
            $innerHtml .= '<div class="jtb-post-slider-pagination"></div>';
        }

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $sliderHeight = $attrs['slider_height'] ?? 500;
        $overlayColor = $attrs['overlay_color'] ?? 'rgba(0,0,0,0.4)';
        $titleColor = $attrs['title_color'] ?? '#ffffff';
        $metaColor = $attrs['meta_color'] ?? 'rgba(255,255,255,0.8)';
        $excerptColor = $attrs['excerpt_color'] ?? 'rgba(255,255,255,0.9)';
        $arrowsColor = $attrs['arrows_color'] ?? '#ffffff';
        $dotsColor = $attrs['dots_color'] ?? 'rgba(255,255,255,0.5)';
        $dotsActive = $attrs['dots_active_color'] ?? '#ffffff';

        // Container
        $css .= $selector . ' .jtb-fullwidth-post-slider-container { position: relative; overflow: hidden; }' . "\n";
        $css .= $selector . ' .jtb-post-slider-wrapper { height: ' . $sliderHeight . 'px; }' . "\n";
        $css .= $selector . ' .jtb-post-slider-track { display: flex; height: 100%; transition: transform 0.5s ease; }' . "\n";

        // Slides
        $css .= $selector . ' .jtb-post-slide { '
            . 'flex: 0 0 100%; '
            . 'display: flex; '
            . 'align-items: center; '
            . 'justify-content: center; '
            . 'position: relative; '
            . 'background-size: cover; '
            . 'background-position: center; '
            . '}' . "\n";

        // Overlay
        $css .= $selector . '.jtb-use-overlay .jtb-post-slide-overlay { '
            . 'position: absolute; '
            . 'top: 0; left: 0; right: 0; bottom: 0; '
            . 'background: ' . $overlayColor . '; '
            . '}' . "\n";

        // Content
        $css .= $selector . ' .jtb-post-slide-content { '
            . 'position: relative; '
            . 'z-index: 1; '
            . 'text-align: center; '
            . 'max-width: 800px; '
            . 'padding: 40px; '
            . '}' . "\n";

        // Meta
        $css .= $selector . ' .jtb-post-slide-meta { margin-bottom: 15px; }' . "\n";
        $css .= $selector . ' .jtb-post-category { '
            . 'color: ' . $metaColor . '; '
            . 'text-transform: uppercase; '
            . 'font-size: 12px; '
            . 'letter-spacing: 2px; '
            . 'margin-right: 15px; '
            . '}' . "\n";
        $css .= $selector . ' .jtb-post-date { color: ' . $metaColor . '; font-size: 14px; }' . "\n";

        // Title
        $css .= $selector . ' .jtb-post-slide-title { '
            . 'color: ' . $titleColor . '; '
            . 'font-size: 42px; '
            . 'margin: 0 0 20px; '
            . '}' . "\n";

        // Excerpt
        $css .= $selector . ' .jtb-post-slide-excerpt { '
            . 'color: ' . $excerptColor . '; '
            . 'font-size: 18px; '
            . 'margin-bottom: 25px; '
            . 'line-height: 1.6; '
            . '}' . "\n";

        // Button
        $css .= $selector . ' .jtb-post-slide-button { '
            . 'background: #ffffff; '
            . 'color: #333; '
            . 'padding: 12px 30px; '
            . 'text-decoration: none; '
            . 'display: inline-block; '
            . 'transition: all 0.3s ease; '
            . '}' . "\n";

        $css .= $selector . ' .jtb-post-slide-button:hover { background: rgba(255,255,255,0.9); }' . "\n";

        // Arrows
        $css .= $selector . ' .jtb-post-slider-arrow { '
            . 'position: absolute; '
            . 'top: 50%; '
            . 'transform: translateY(-50%); '
            . 'background: rgba(0,0,0,0.3); '
            . 'color: ' . $arrowsColor . '; '
            . 'border: none; '
            . 'width: 50px; '
            . 'height: 50px; '
            . 'font-size: 28px; '
            . 'cursor: pointer; '
            . 'z-index: 10; '
            . 'transition: all 0.3s ease; '
            . '}' . "\n";

        $css .= $selector . ' .jtb-slider-prev { left: 20px; }' . "\n";
        $css .= $selector . ' .jtb-slider-next { right: 20px; }' . "\n";
        $css .= $selector . ' .jtb-post-slider-arrow:hover { background: rgba(0,0,0,0.5); }' . "\n";

        // Pagination
        $css .= $selector . ' .jtb-post-slider-pagination { '
            . 'position: absolute; '
            . 'bottom: 25px; '
            . 'left: 50%; '
            . 'transform: translateX(-50%); '
            . 'display: flex; '
            . 'gap: 10px; '
            . 'z-index: 10; '
            . '}' . "\n";

        $css .= $selector . ' .jtb-post-slider-pagination .jtb-slider-dot { '
            . 'width: 12px; '
            . 'height: 12px; '
            . 'border-radius: 50%; '
            . 'background: ' . $dotsColor . '; '
            . 'cursor: pointer; '
            . 'transition: all 0.3s ease; '
            . '}' . "\n";

        $css .= $selector . ' .jtb-post-slider-pagination .jtb-slider-dot.active { background: ' . $dotsActive . '; }' . "\n";

        // Responsive
        $css .= '@media (max-width: 980px) {' . "\n";
        $css .= '  ' . $selector . ' .jtb-post-slide-title { font-size: 32px; }' . "\n";
        $css .= '  ' . $selector . ' .jtb-post-slide-excerpt { font-size: 16px; }' . "\n";
        if (!empty($attrs['slider_height__tablet'])) {
            $css .= '  ' . $selector . ' .jtb-post-slider-wrapper { height: ' . $attrs['slider_height__tablet'] . 'px; }' . "\n";
        }
        $css .= '}' . "\n";

        $css .= '@media (max-width: 767px) {' . "\n";
        $css .= '  ' . $selector . ' .jtb-post-slide-title { font-size: 26px; }' . "\n";
        $css .= '  ' . $selector . ' .jtb-post-slide-excerpt { font-size: 14px; }' . "\n";
        $css .= '  ' . $selector . ' .jtb-post-slider-arrow { width: 40px; height: 40px; font-size: 22px; }' . "\n";
        if (!empty($attrs['slider_height__phone'])) {
            $css .= '  ' . $selector . ' .jtb-post-slider-wrapper { height: ' . $attrs['slider_height__phone'] . 'px; }' . "\n";
        }
        $css .= '}' . "\n";

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('fullwidth_post_slider', JTB_Module_FullwidthPostSlider::class);
