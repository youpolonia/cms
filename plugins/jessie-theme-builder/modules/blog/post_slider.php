<?php
/**
 * Post Slider Module
 * Blog post carousel/slider
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_PostSlider extends JTB_Element
{
    public string $icon = 'post-slider';
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
    protected string $module_prefix = 'post_slider';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'content_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-post-slide-content'
        ],
        'overlay_color' => [
            'property' => 'background',
            'selector' => '.jtb-post-slide-overlay'
        ],
        'slider_height' => [
            'property' => 'min-height',
            'selector' => '.jtb-post-slide',
            'unit' => 'px',
            'responsive' => true
        ]
    ];

    public function getSlug(): string
    {
        return 'post_slider';
    }

    public function getName(): string
    {
        return 'Post Slider';
    }

    public function getFields(): array
    {
        return [
            'posts_number' => [
                'label' => 'Posts Count',
                'type' => 'range',
                'min' => 1,
                'max' => 20,
                'default' => 5
            ],
            'include_categories' => [
                'label' => 'Include Categories',
                'type' => 'text'
            ],
            'show_arrows' => [
                'label' => 'Show Arrows',
                'type' => 'toggle',
                'default' => true
            ],
            'show_pagination' => [
                'label' => 'Show Dots',
                'type' => 'toggle',
                'default' => true
            ],
            'auto' => [
                'label' => 'Auto Rotate',
                'type' => 'toggle',
                'default' => true
            ],
            'auto_speed' => [
                'label' => 'Rotation Speed',
                'type' => 'range',
                'min' => 1000,
                'max' => 10000,
                'unit' => 'ms',
                'default' => 5000,
                'show_if' => ['auto' => true]
            ],
            'show_meta' => [
                'label' => 'Show Meta',
                'type' => 'toggle',
                'default' => true
            ],
            'image_placement' => [
                'label' => 'Image Placement',
                'type' => 'select',
                'options' => [
                    'background' => 'Background',
                    'left' => 'Left',
                    'right' => 'Right',
                    'top' => 'Top'
                ],
                'default' => 'background'
            ],
            'content_text_color' => [
                'label' => 'Text Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'overlay_color' => [
                'label' => 'Overlay Color',
                'type' => 'color',
                'default' => 'rgba(0,0,0,0.5)'
            ],
            'slider_height' => [
                'label' => 'Slider Height',
                'type' => 'range',
                'min' => 200,
                'max' => 800,
                'unit' => 'px',
                'default' => 500,
                'responsive' => true
            ],
            'show_excerpt' => [
                'label' => 'Show Excerpt',
                'type' => 'toggle',
                'default' => true
            ],
            'show_read_more' => [
                'label' => 'Show Read More',
                'type' => 'toggle',
                'default' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $showArrows = $attrs['show_arrows'] ?? true;
        $showPagination = $attrs['show_pagination'] ?? true;
        $showMeta = $attrs['show_meta'] ?? true;
        $showExcerpt = $attrs['show_excerpt'] ?? true;
        $showReadMore = $attrs['show_read_more'] ?? true;
        $imagePlacement = $attrs['image_placement'] ?? 'background';
        $auto = !empty($attrs['auto']);
        $autoSpeed = $attrs['auto_speed'] ?? 5000;

        $innerHtml = '<div class="jtb-post-slider-container jtb-post-slider-' . $imagePlacement . '" data-auto="' . ($auto ? 'true' : 'false') . '" data-speed="' . $autoSpeed . '">';
        $innerHtml .= '<div class="jtb-post-slider-track">';

        // Sample posts
        $samplePosts = [
            ['title' => 'Featured Post One', 'excerpt' => 'This is a sample excerpt for the first featured post.', 'date' => date('F j, Y'), 'author' => 'Admin', 'image' => ''],
            ['title' => 'Featured Post Two', 'excerpt' => 'Another sample excerpt showing in the post slider.', 'date' => date('F j, Y', strtotime('-1 day')), 'author' => 'Editor', 'image' => ''],
            ['title' => 'Featured Post Three', 'excerpt' => 'Third sample post with featured content.', 'date' => date('F j, Y', strtotime('-2 days')), 'author' => 'Admin', 'image' => ''],
        ];

        foreach ($samplePosts as $post) {
            $innerHtml .= '<div class="jtb-post-slide">';

            if ($imagePlacement === 'background') {
                $innerHtml .= '<div class="jtb-post-slide-bg"></div>';
                $innerHtml .= '<div class="jtb-post-slide-overlay"></div>';
            } else {
                $innerHtml .= '<div class="jtb-post-slide-image"><div class="jtb-post-slide-bg"></div></div>';
            }

            $innerHtml .= '<div class="jtb-post-slide-content">';
            $innerHtml .= '<h2 class="jtb-post-slide-title"><a href="#">' . $this->esc($post['title']) . '</a></h2>';

            if ($showMeta) {
                $innerHtml .= '<div class="jtb-post-slide-meta">';
                $innerHtml .= '<span class="jtb-post-date">' . $post['date'] . '</span>';
                $innerHtml .= ' | <span class="jtb-post-author">by ' . $this->esc($post['author']) . '</span>';
                $innerHtml .= '</div>';
            }

            if ($showExcerpt) {
                $innerHtml .= '<div class="jtb-post-slide-excerpt">' . $this->esc($post['excerpt']) . '</div>';
            }

            if ($showReadMore) {
                $innerHtml .= '<a href="#" class="jtb-post-slide-more jtb-button">Read More</a>';
            }

            $innerHtml .= '</div>';
            $innerHtml .= '</div>';
        }

        $innerHtml .= '</div>';

        if ($showArrows) {
            $innerHtml .= '<button class="jtb-post-slider-arrow jtb-post-slider-prev">‹</button>';
            $innerHtml .= '<button class="jtb-post-slider-arrow jtb-post-slider-next">›</button>';
        }

        if ($showPagination) {
            $innerHtml .= '<div class="jtb-post-slider-dots"></div>';
        }

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for Post Slider module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $textColor = $attrs['content_text_color'] ?? '#ffffff';
        $imagePlacement = $attrs['image_placement'] ?? 'background';

        // Container
        $css .= $selector . ' .jtb-post-slider-container { position: relative; overflow: hidden; }' . "\n";
        $css .= $selector . ' .jtb-post-slider-track { display: flex; transition: transform 0.5s ease; }' . "\n";

        // Slide
        $css .= $selector . ' .jtb-post-slide { flex: 0 0 100%; position: relative; display: flex; align-items: center; justify-content: center; }' . "\n";

        // Background image style
        if ($imagePlacement === 'background') {
            $css .= $selector . ' .jtb-post-slide-bg { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: #333 center/cover no-repeat; }' . "\n";
            $css .= $selector . ' .jtb-post-slide-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; }' . "\n";
            $css .= $selector . ' .jtb-post-slide-content { position: relative; z-index: 1; text-align: center; max-width: 800px; padding: 40px; }' . "\n";
        } else {
            // Side/top image layouts
            $css .= $selector . ' .jtb-post-slide-image { flex: 0 0 50%; }' . "\n";
            $css .= $selector . ' .jtb-post-slide-image .jtb-post-slide-bg { height: 100%; background: #333 center/cover no-repeat; }' . "\n";
            $css .= $selector . ' .jtb-post-slide-content { flex: 0 0 50%; padding: 40px; }' . "\n";

            if ($imagePlacement === 'top') {
                $css .= $selector . ' .jtb-post-slide { flex-direction: column; }' . "\n";
                $css .= $selector . ' .jtb-post-slide-image { flex: 0 0 60%; width: 100%; }' . "\n";
                $css .= $selector . ' .jtb-post-slide-content { flex: 0 0 40%; width: 100%; }' . "\n";
            } elseif ($imagePlacement === 'right') {
                $css .= $selector . ' .jtb-post-slide { flex-direction: row-reverse; }' . "\n";
            }
        }

        // Title
        $css .= $selector . ' .jtb-post-slide-title { font-size: 36px; margin-bottom: 15px; }' . "\n";
        $css .= $selector . ' .jtb-post-slide-title a { color: inherit; text-decoration: none; }' . "\n";

        // Meta
        $css .= $selector . ' .jtb-post-slide-meta { font-size: 14px; opacity: 0.8; margin-bottom: 20px; }' . "\n";

        // Excerpt
        $css .= $selector . ' .jtb-post-slide-excerpt { margin-bottom: 25px; font-size: 16px; }' . "\n";

        // Button
        $css .= $selector . ' .jtb-post-slide-more { display: inline-block; padding: 12px 24px; border: 2px solid ' . $textColor . '; color: ' . $textColor . '; text-decoration: none; transition: all 0.3s ease; }' . "\n";
        $css .= $selector . ' .jtb-post-slide-more:hover { background: ' . $textColor . '; color: #333; }' . "\n";

        // Arrows
        $css .= $selector . ' .jtb-post-slider-arrow { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.3); color: #fff; border: none; width: 50px; height: 50px; font-size: 30px; cursor: pointer; z-index: 10; border-radius: 50%; }' . "\n";
        $css .= $selector . ' .jtb-post-slider-prev { left: 20px; }' . "\n";
        $css .= $selector . ' .jtb-post-slider-next { right: 20px; }' . "\n";

        // Dots
        $css .= $selector . ' .jtb-post-slider-dots { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); display: flex; gap: 10px; }' . "\n";
        $css .= $selector . ' .jtb-post-slider-dot { width: 12px; height: 12px; border-radius: 50%; background: rgba(255,255,255,0.5); cursor: pointer; border: none; }' . "\n";
        $css .= $selector . ' .jtb-post-slider-dot.jtb-active { background: #fff; }' . "\n";

        // Responsive phone title
        $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-post-slide-title { font-size: 24px; } }' . "\n";

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('post_slider', JTB_Module_PostSlider::class);
