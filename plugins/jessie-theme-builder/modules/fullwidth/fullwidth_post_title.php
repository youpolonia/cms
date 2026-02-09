<?php
/**
 * Fullwidth Post Title Module
 * Full-width post/page title with background
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_FullwidthPostTitle extends JTB_Element
{
    public string $icon = 'title-fullwidth';
    public string $category = 'fullwidth';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = false;
    public bool $use_box_shadow = false;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = true;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'fullwidth_post_title';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'module_height' => [
            'property' => 'min-height',
            'selector' => '.jtb-fullwidth-post-title-container',
            'unit' => 'px',
            'responsive' => true
        ],
        'overlay_color' => [
            'property' => 'background',
            'selector' => '.jtb-post-title-overlay'
        ],
        'title_color' => [
            'property' => 'color',
            'selector' => '.jtb-post-title-heading'
        ],
        'meta_color' => [
            'property' => 'color',
            'selector' => '.jtb-post-title-meta, .jtb-post-title-category'
        ],
        'title_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-post-title-heading',
            'unit' => 'px',
            'responsive' => true
        ]
    ];

    public function getSlug(): string
    {
        return 'fullwidth_post_title';
    }

    public function getName(): string
    {
        return 'Fullwidth Post Title';
    }

    public function getFields(): array
    {
        return [
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Page Title',
                'description' => 'Leave empty to use page title'
            ],
            'show_meta' => [
                'label' => 'Show Meta',
                'type' => 'toggle',
                'default' => true
            ],
            'show_author' => [
                'label' => 'Show Author',
                'type' => 'toggle',
                'default' => true,
                'show_if' => ['show_meta' => true]
            ],
            'show_date' => [
                'label' => 'Show Date',
                'type' => 'toggle',
                'default' => true,
                'show_if' => ['show_meta' => true]
            ],
            'show_categories' => [
                'label' => 'Show Categories',
                'type' => 'toggle',
                'default' => true,
                'show_if' => ['show_meta' => true]
            ],
            'show_comments' => [
                'label' => 'Show Comments Count',
                'type' => 'toggle',
                'default' => false,
                'show_if' => ['show_meta' => true]
            ],
            'text_alignment' => [
                'label' => 'Text Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'center'
            ],
            'featured_placement' => [
                'label' => 'Featured Image Placement',
                'type' => 'select',
                'options' => [
                    'background' => 'Background',
                    'below' => 'Below Title',
                    'none' => 'None'
                ],
                'default' => 'background'
            ],
            'module_height' => [
                'label' => 'Module Height',
                'type' => 'range',
                'min' => 150,
                'max' => 600,
                'unit' => 'px',
                'default' => 300,
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
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'range',
                'min' => 20,
                'max' => 80,
                'unit' => 'px',
                'default' => 42,
                'responsive' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $title = $this->esc($attrs['title'] ?? 'Page Title');
        $showMeta = $attrs['show_meta'] ?? true;
        $showAuthor = $attrs['show_author'] ?? true;
        $showDate = $attrs['show_date'] ?? true;
        $showCategories = $attrs['show_categories'] ?? true;
        $showComments = $attrs['show_comments'] ?? false;
        $textAlignment = $attrs['text_alignment'] ?? 'center';
        $featuredPlacement = $attrs['featured_placement'] ?? 'background';

        $containerClass = 'jtb-fullwidth-post-title-container jtb-text-align-' . $textAlignment;

        // Sample meta data
        $author = 'John Doe';
        $date = 'January 15, 2025';
        $categories = ['Design', 'Development'];
        $commentsCount = 5;

        $innerHtml = '<div class="' . $containerClass . '">';
        $innerHtml .= '<div class="jtb-post-title-overlay"></div>';
        $innerHtml .= '<div class="jtb-post-title-content">';

        // Categories above title
        if ($showMeta && $showCategories) {
            $innerHtml .= '<div class="jtb-post-title-categories">';
            foreach ($categories as $cat) {
                $innerHtml .= '<a href="#" class="jtb-post-title-category">' . $this->esc($cat) . '</a>';
            }
            $innerHtml .= '</div>';
        }

        // Title
        $innerHtml .= '<h1 class="jtb-post-title-heading">' . $title . '</h1>';

        // Meta
        if ($showMeta && ($showAuthor || $showDate || $showComments)) {
            $innerHtml .= '<div class="jtb-post-title-meta">';

            if ($showAuthor) {
                $innerHtml .= '<span class="jtb-post-meta-author">By ' . $this->esc($author) . '</span>';
            }

            if ($showDate) {
                $innerHtml .= '<span class="jtb-post-meta-date">' . $this->esc($date) . '</span>';
            }

            if ($showComments) {
                $innerHtml .= '<span class="jtb-post-meta-comments">' . $commentsCount . ' Comments</span>';
            }

            $innerHtml .= '</div>';
        }

        $innerHtml .= '</div>';
        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $moduleHeight = $attrs['module_height'] ?? 300;
        $overlayColor = $attrs['overlay_color'] ?? 'rgba(0,0,0,0.4)';
        $titleColor = $attrs['title_color'] ?? '#ffffff';
        $metaColor = $attrs['meta_color'] ?? 'rgba(255,255,255,0.8)';
        $titleSize = $attrs['title_font_size'] ?? 42;

        // Container
        $css .= $selector . ' .jtb-fullwidth-post-title-container { '
            . 'position: relative; '
            . 'min-height: ' . $moduleHeight . 'px; '
            . 'display: flex; '
            . 'align-items: center; '
            . 'justify-content: center; '
            . 'background-size: cover; '
            . 'background-position: center; '
            . '}' . "\n";

        // Overlay
        $css .= $selector . ' .jtb-post-title-overlay { '
            . 'position: absolute; '
            . 'top: 0; left: 0; right: 0; bottom: 0; '
            . 'background: ' . $overlayColor . '; '
            . '}' . "\n";

        // Content
        $css .= $selector . ' .jtb-post-title-content { '
            . 'position: relative; '
            . 'z-index: 1; '
            . 'padding: 40px; '
            . 'max-width: 900px; '
            . 'width: 100%; '
            . '}' . "\n";

        // Alignment
        $css .= $selector . ' .jtb-text-align-left .jtb-post-title-content { text-align: left; }' . "\n";
        $css .= $selector . ' .jtb-text-align-center .jtb-post-title-content { text-align: center; margin: 0 auto; }' . "\n";
        $css .= $selector . ' .jtb-text-align-right .jtb-post-title-content { text-align: right; margin-left: auto; }' . "\n";

        // Categories
        $css .= $selector . ' .jtb-post-title-categories { margin-bottom: 15px; }' . "\n";
        $css .= $selector . ' .jtb-post-title-category { '
            . 'color: ' . $metaColor . '; '
            . 'text-transform: uppercase; '
            . 'font-size: 12px; '
            . 'letter-spacing: 2px; '
            . 'text-decoration: none; '
            . 'margin: 0 8px; '
            . 'transition: color 0.3s ease; '
            . '}' . "\n";
        $css .= $selector . ' .jtb-post-title-category:hover { color: #ffffff; }' . "\n";

        // Title
        $css .= $selector . ' .jtb-post-title-heading { '
            . 'color: ' . $titleColor . '; '
            . 'font-size: ' . $titleSize . 'px; '
            . 'margin: 0 0 15px; '
            . 'line-height: 1.2; '
            . '}' . "\n";

        // Meta
        $css .= $selector . ' .jtb-post-title-meta { '
            . 'color: ' . $metaColor . '; '
            . 'font-size: 14px; '
            . '}' . "\n";

        $css .= $selector . ' .jtb-post-title-meta span { margin: 0 10px; }' . "\n";
        $css .= $selector . ' .jtb-post-title-meta span:first-child { margin-left: 0; }' . "\n";
        $css .= $selector . ' .jtb-post-title-meta span:last-child { margin-right: 0; }' . "\n";

        // Responsive
        if (!empty($attrs['module_height__tablet'])) {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-fullwidth-post-title-container { min-height: ' . $attrs['module_height__tablet'] . 'px; } }' . "\n";
        }
        if (!empty($attrs['title_font_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-post-title-heading { font-size: ' . $attrs['title_font_size__tablet'] . 'px; } }' . "\n";
        }

        if (!empty($attrs['module_height__phone'])) {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-fullwidth-post-title-container { min-height: ' . $attrs['module_height__phone'] . 'px; } }' . "\n";
        }
        if (!empty($attrs['title_font_size__phone'])) {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-post-title-heading { font-size: ' . $attrs['title_font_size__phone'] . 'px; } }' . "\n";
        }

        $css .= '@media (max-width: 767px) {' . "\n";
        $css .= '  ' . $selector . ' .jtb-post-title-meta span { display: block; margin: 5px 0; }' . "\n";
        $css .= '}' . "\n";

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('fullwidth_post_title', JTB_Module_FullwidthPostTitle::class);
