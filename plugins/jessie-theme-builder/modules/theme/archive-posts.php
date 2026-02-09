<?php
/**
 * Archive Posts Module (Post Loop)
 * Displays a list/grid of posts for archive pages
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Archive_Posts extends JTB_Element
{
    public string $slug = 'archive_posts';
    public string $name = 'Archive Posts';
    public string $icon = 'layout-grid';
    public string $category = 'dynamic';

    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_typography = true;

    protected string $module_prefix = 'archive_posts';

    protected array $style_config = [
        'gap' => [
            'property' => 'gap',
            'selector' => '.jtb-posts-grid',
            'unit' => 'px',
            'responsive' => true
        ],
        'card_background' => [
            'property' => 'background',
            'selector' => '.jtb-post-card'
        ],
        'card_border_radius' => [
            'property' => 'border-radius',
            'selector' => '.jtb-post-card',
            'unit' => 'px'
        ],
        'title_color' => [
            'property' => 'color',
            'selector' => '.jtb-post-title a',
            'hover' => true
        ],
        'excerpt_color' => [
            'property' => 'color',
            'selector' => '.jtb-post-excerpt'
        ],
        'meta_color' => [
            'property' => 'color',
            'selector' => '.jtb-post-meta'
        ],
        'category_color' => [
            'property' => 'color',
            'selector' => '.jtb-post-category'
        ],
        'link_color' => [
            'property' => 'color',
            'selector' => '.jtb-read-more, .jtb-post-meta a',
            'hover' => true
        ]
    ];

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
                'default' => '3',
                'responsive' => true
            ],
            'posts_per_page' => [
                'label' => 'Posts Per Page',
                'type' => 'range',
                'min' => 3,
                'max' => 24,
                'step' => 3,
                'default' => 9,
                'unit' => ''
            ],
            'show_featured_image' => [
                'label' => 'Show Featured Image',
                'type' => 'toggle',
                'default' => true
            ],
            'image_position' => [
                'label' => 'Image Position',
                'type' => 'select',
                'options' => [
                    'top' => 'Top',
                    'left' => 'Left',
                    'right' => 'Right',
                    'background' => 'Background Overlay'
                ],
                'default' => 'top'
            ],
            'image_aspect' => [
                'label' => 'Image Aspect Ratio',
                'type' => 'select',
                'options' => [
                    '16/9' => '16:9 (Landscape)',
                    '4/3' => '4:3',
                    '1/1' => '1:1 (Square)',
                    '3/4' => '3:4 (Portrait)',
                    '21/9' => '21:9 (Wide)'
                ],
                'default' => '16/9'
            ],
            'show_category' => [
                'label' => 'Show Category',
                'type' => 'toggle',
                'default' => true
            ],
            'show_title' => [
                'label' => 'Show Title',
                'type' => 'toggle',
                'default' => true
            ],
            'title_length' => [
                'label' => 'Title Max Length',
                'type' => 'range',
                'min' => 20,
                'max' => 100,
                'step' => 5,
                'default' => 60,
                'unit' => 'chars'
            ],
            'show_excerpt' => [
                'label' => 'Show Excerpt',
                'type' => 'toggle',
                'default' => true
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
            'show_author' => [
                'label' => 'Show Author',
                'type' => 'toggle',
                'default' => true
            ],
            'show_date' => [
                'label' => 'Show Date',
                'type' => 'toggle',
                'default' => true
            ],
            'date_format' => [
                'label' => 'Date Format',
                'type' => 'select',
                'options' => [
                    'F j, Y' => 'January 1, 2026',
                    'M j, Y' => 'Jan 1, 2026',
                    'Y-m-d' => '2026-01-01',
                    'relative' => 'Relative (2 days ago)'
                ],
                'default' => 'M j, Y'
            ],
            'show_read_more' => [
                'label' => 'Show Read More',
                'type' => 'toggle',
                'default' => true
            ],
            'read_more_text' => [
                'label' => 'Read More Text',
                'type' => 'text',
                'default' => 'Read More'
            ],
            'show_pagination' => [
                'label' => 'Show Pagination',
                'type' => 'toggle',
                'default' => true
            ],
            'pagination_style' => [
                'label' => 'Pagination Style',
                'type' => 'select',
                'options' => [
                    'numbers' => 'Page Numbers',
                    'prev_next' => 'Prev/Next Only',
                    'load_more' => 'Load More Button',
                    'infinite' => 'Infinite Scroll'
                ],
                'default' => 'numbers'
            ],
            'gap' => [
                'label' => 'Gap Between Items',
                'type' => 'range',
                'min' => 10,
                'max' => 50,
                'step' => 5,
                'default' => 30,
                'unit' => 'px',
                'responsive' => true
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
            'card_hover_effect' => [
                'label' => 'Hover Effect',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'lift' => 'Lift Up',
                    'shadow' => 'Shadow Grow',
                    'border' => 'Border Color'
                ],
                'default' => 'lift'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#1f2937',
                'hover' => true
            ],
            'excerpt_color' => [
                'label' => 'Excerpt Color',
                'type' => 'color',
                'default' => '#4b5563'
            ],
            'meta_color' => [
                'label' => 'Meta Color',
                'type' => 'color',
                'default' => '#6b7280'
            ],
            'category_color' => [
                'label' => 'Category Color',
                'type' => 'color',
                'default' => '#7c3aed'
            ],
            'link_color' => [
                'label' => 'Link/Button Color',
                'type' => 'color',
                'default' => '#7c3aed',
                'hover' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $id = $attrs['id'] ?? 'archive_posts_' . uniqid();
        $layout = $attrs['layout'] ?? 'grid';
        $postsPerPage = $attrs['posts_per_page'] ?? 9;
        $showImage = $attrs['show_featured_image'] ?? true;
        $imageAspect = $attrs['image_aspect'] ?? '16/9';
        $showCategory = $attrs['show_category'] ?? true;
        $showTitle = $attrs['show_title'] ?? true;
        $showExcerpt = $attrs['show_excerpt'] ?? true;
        $excerptLength = $attrs['excerpt_length'] ?? 20;
        $showAuthor = $attrs['show_author'] ?? true;
        $showDate = $attrs['show_date'] ?? true;
        $dateFormat = $attrs['date_format'] ?? 'M j, Y';
        $showReadMore = $attrs['show_read_more'] ?? true;
        $readMoreText = $attrs['read_more_text'] ?? 'Read More';
        $showPagination = $attrs['show_pagination'] ?? true;

        // Get dynamic posts
        $isPreview = JTB_Dynamic_Context::isPreviewMode();
        $currentPage = (int)($_GET['page'] ?? 1);
        $offset = ($currentPage - 1) * $postsPerPage;
        $posts = JTB_Dynamic_Context::getArchivePosts($postsPerPage, $offset);

        // SVG arrow icon
        $arrowIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>';

        // Placeholder image SVG
        $imgSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="225" viewBox="0 0 400 225"><rect fill="#e5e7eb" width="400" height="225"/><g transform="translate(175, 87)"><rect x="3" y="3" width="44" height="44" rx="2" ry="2" fill="none" stroke="#9ca3af" stroke-width="3"/><circle cx="18" cy="18" r="5" fill="#9ca3af"/><polyline points="44 33 33 22 15 40" fill="none" stroke="#9ca3af" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></g></svg>';

        $classes = ['jtb-archive-posts', 'jtb-layout-' . $this->esc($layout)];

        $html = '<div id="' . $this->esc($id) . '" class="' . implode(' ', $classes) . '">';
        $html .= '<div class="jtb-posts-grid">';

        // Use real posts if available, otherwise show placeholders
        if (!empty($posts) && !$isPreview) {
            foreach ($posts as $post) {
                $html .= $this->renderPostCard($post, [
                    'showImage' => $showImage,
                    'imageAspect' => $imageAspect,
                    'showCategory' => $showCategory,
                    'showTitle' => $showTitle,
                    'showExcerpt' => $showExcerpt,
                    'excerptLength' => $excerptLength,
                    'showAuthor' => $showAuthor,
                    'showDate' => $showDate,
                    'dateFormat' => $dateFormat,
                    'showReadMore' => $showReadMore,
                    'readMoreText' => $readMoreText,
                    'arrowIcon' => $arrowIcon,
                    'imgSvg' => $imgSvg
                ]);
            }
        } else {
            // Render placeholder posts for preview
            $previewCount = min($postsPerPage, 6);
            for ($i = 0; $i < $previewCount; $i++) {
                $html .= '<article class="jtb-post-card">';

                if ($showImage) {
                    $html .= '<div class="jtb-post-image" style="aspect-ratio: ' . $this->esc($imageAspect) . ';">';
                    $html .= '<a href="#"><img src="data:image/svg+xml,' . rawurlencode($imgSvg) . '" alt="" /></a>';
                    $html .= '</div>';
                }

                $html .= '<div class="jtb-post-content">';

                if ($showCategory) {
                    $html .= '<a href="#" class="jtb-post-category">Category</a>';
                }

                if ($showTitle) {
                    $html .= '<h3 class="jtb-post-title"><a href="#">Sample Post Title ' . ($i + 1) . ' Goes Here</a></h3>';
                }

                if ($showExcerpt) {
                    $html .= '<p class="jtb-post-excerpt">This is a sample excerpt that would appear for each post in the archive. It gives readers a preview of the content...</p>';
                }

                if ($showAuthor || $showDate) {
                    $html .= '<div class="jtb-post-meta">';
                    if ($showAuthor) {
                        $html .= '<span class="jtb-post-author">By <a href="#">Author</a></span>';
                    }
                    if ($showAuthor && $showDate) {
                        $html .= '<span class="jtb-post-meta-sep">|</span>';
                    }
                    if ($showDate) {
                        $html .= '<span class="jtb-post-date">' . date($dateFormat, strtotime('-' . ($i * 2) . ' days')) . '</span>';
                    }
                    $html .= '</div>';
                }

                if ($showReadMore) {
                    $html .= '<a href="#" class="jtb-read-more">' . $this->esc($readMoreText) . ' ' . $arrowIcon . '</a>';
                }

                $html .= '</div>';
                $html .= '</article>';
            }
        }

        $html .= '</div>'; // grid

        // Pagination
        if ($showPagination) {
            $html .= '<nav class="jtb-pagination">';
            $html .= '<a href="?page=1" class="jtb-page-num' . ($currentPage === 1 ? ' jtb-page-active' : '') . '">1</a>';
            $html .= '<a href="?page=2" class="jtb-page-num' . ($currentPage === 2 ? ' jtb-page-active' : '') . '">2</a>';
            $html .= '<a href="?page=3" class="jtb-page-num' . ($currentPage === 3 ? ' jtb-page-active' : '') . '">3</a>';
            $html .= '<span class="jtb-page-dots">...</span>';
            $html .= '<a href="?page=' . ($currentPage + 1) . '" class="jtb-page-next">Next ' . $arrowIcon . '</a>';
            $html .= '</nav>';
        }

        $html .= '</div>'; // wrapper

        return $html;
    }

    /**
     * Render a single post card
     */
    private function renderPostCard(array $post, array $options): string
    {
        $html = '<article class="jtb-post-card">';

        if ($options['showImage']) {
            $html .= '<div class="jtb-post-image" style="aspect-ratio: ' . $this->esc($options['imageAspect']) . ';">';
            $imgSrc = !empty($post['featured_image'])
                ? $post['featured_image']
                : 'data:image/svg+xml,' . rawurlencode($options['imgSvg']);
            $html .= '<a href="' . $this->esc($post['url'] ?? '#') . '"><img src="' . $this->esc($imgSrc) . '" alt="' . $this->esc($post['title'] ?? '') . '" /></a>';
            $html .= '</div>';
        }

        $html .= '<div class="jtb-post-content">';

        if ($options['showCategory'] && !empty($post['category'])) {
            $catSlug = strtolower(str_replace(' ', '-', $post['category']));
            $html .= '<a href="/category/' . $this->esc($catSlug) . '" class="jtb-post-category">' . $this->esc($post['category']) . '</a>';
        }

        if ($options['showTitle']) {
            $html .= '<h3 class="jtb-post-title"><a href="' . $this->esc($post['url'] ?? '#') . '">' . $this->esc($post['title'] ?? 'Untitled') . '</a></h3>';
        }

        if ($options['showExcerpt'] && !empty($post['excerpt'])) {
            $excerpt = wp_trim_words($post['excerpt'], $options['excerptLength'], '...');
            $html .= '<p class="jtb-post-excerpt">' . $this->esc($excerpt) . '</p>';
        }

        if ($options['showAuthor'] || $options['showDate']) {
            $html .= '<div class="jtb-post-meta">';
            if ($options['showAuthor'] && !empty($post['author'])) {
                $html .= '<span class="jtb-post-author">By <a href="/author/' . $this->esc($post['author_slug'] ?? '') . '">' . $this->esc($post['author']) . '</a></span>';
            }
            if ($options['showAuthor'] && $options['showDate']) {
                $html .= '<span class="jtb-post-meta-sep">|</span>';
            }
            if ($options['showDate'] && !empty($post['date'])) {
                $html .= '<span class="jtb-post-date">' . $this->esc($post['date']) . '</span>';
            }
            $html .= '</div>';
        }

        if ($options['showReadMore']) {
            $html .= '<a href="' . $this->esc($post['url'] ?? '#') . '" class="jtb-read-more">' . $this->esc($options['readMoreText']) . ' ' . $options['arrowIcon'] . '</a>';
        }

        $html .= '</div>';
        $html .= '</article>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $layout = $attrs['layout'] ?? 'grid';
        $columns = $attrs['columns'] ?? '3';
        $gap = $attrs['gap'] ?? 30;
        $cardBg = $attrs['card_background'] ?? '#ffffff';
        $cardRadius = $attrs['card_border_radius'] ?? 8;
        $cardShadow = $attrs['card_shadow'] ?? true;
        $hoverEffect = $attrs['card_hover_effect'] ?? 'lift';
        $titleColor = $attrs['title_color'] ?? '#1f2937';
        $titleHoverColor = $attrs['title_color__hover'] ?? '#7c3aed';
        $excerptColor = $attrs['excerpt_color'] ?? '#4b5563';
        $metaColor = $attrs['meta_color'] ?? '#6b7280';
        $categoryColor = $attrs['category_color'] ?? '#7c3aed';
        $linkColor = $attrs['link_color'] ?? '#7c3aed';
        $linkHoverColor = $attrs['link_color__hover'] ?? '#5b21b6';

        // Grid
        if ($layout === 'list') {
            $css .= $selector . ' .jtb-posts-grid { display: flex; flex-direction: column; gap: ' . intval($gap) . 'px; }' . "\n";
            $css .= $selector . ' .jtb-post-card { display: flex; flex-direction: row; }' . "\n";
            $css .= $selector . ' .jtb-post-image { width: 280px; flex-shrink: 0; }' . "\n";
            $css .= $selector . ' .jtb-post-content { flex: 1; padding: 24px; }' . "\n";
        } else {
            $css .= $selector . ' .jtb-posts-grid { display: grid; grid-template-columns: repeat(' . intval($columns) . ', 1fr); gap: ' . intval($gap) . 'px; }' . "\n";
            $css .= $selector . ' .jtb-post-content { padding: 20px; }' . "\n";
        }

        // Card
        $css .= $selector . ' .jtb-post-card { ';
        $css .= 'background: ' . $cardBg . '; ';
        $css .= 'border-radius: ' . intval($cardRadius) . 'px; ';
        $css .= 'overflow: hidden; ';
        $css .= 'transition: all 0.3s ease; ';
        if ($cardShadow) {
            $css .= 'box-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06); ';
        }
        $css .= '}' . "\n";

        // Card hover effect
        if ($hoverEffect === 'lift') {
            $css .= $selector . ' .jtb-post-card:hover { transform: translateY(-6px); ';
            if ($cardShadow) {
                $css .= 'box-shadow: 0 12px 20px rgba(0,0,0,0.12), 0 4px 8px rgba(0,0,0,0.06); ';
            }
            $css .= '}' . "\n";
        } elseif ($hoverEffect === 'shadow') {
            $css .= $selector . ' .jtb-post-card:hover { box-shadow: 0 15px 30px rgba(0,0,0,0.15), 0 5px 15px rgba(0,0,0,0.08); }' . "\n";
        } elseif ($hoverEffect === 'border') {
            $css .= $selector . ' .jtb-post-card { border: 2px solid transparent; }' . "\n";
            $css .= $selector . ' .jtb-post-card:hover { border-color: ' . $linkColor . '; }' . "\n";
        }

        // Image
        $css .= $selector . ' .jtb-post-image { overflow: hidden; }' . "\n";
        $css .= $selector . ' .jtb-post-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease; }' . "\n";
        $css .= $selector . ' .jtb-post-card:hover .jtb-post-image img { transform: scale(1.05); }' . "\n";

        // Category
        $css .= $selector . ' .jtb-post-category { ';
        $css .= 'display: inline-block; ';
        $css .= 'font-size: 12px; ';
        $css .= 'color: ' . $categoryColor . '; ';
        $css .= 'text-transform: uppercase; ';
        $css .= 'letter-spacing: 0.5px; ';
        $css .= 'margin-bottom: 10px; ';
        $css .= 'text-decoration: none; ';
        $css .= 'font-weight: 500; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-post-category:hover { text-decoration: underline; }' . "\n";

        // Title
        $css .= $selector . ' .jtb-post-title { margin: 0 0 10px; font-size: 18px; line-height: 1.4; font-weight: 600; }' . "\n";
        $css .= $selector . ' .jtb-post-title a { color: ' . $titleColor . '; text-decoration: none; transition: color 0.3s ease; }' . "\n";
        $css .= $selector . ' .jtb-post-title a:hover { color: ' . $titleHoverColor . '; }' . "\n";

        // Excerpt
        $css .= $selector . ' .jtb-post-excerpt { margin: 0 0 16px; font-size: 14px; color: ' . $excerptColor . '; line-height: 1.6; }' . "\n";

        // Meta
        $css .= $selector . ' .jtb-post-meta { display: flex; align-items: center; gap: 8px; font-size: 13px; color: ' . $metaColor . '; margin-bottom: 16px; }' . "\n";
        $css .= $selector . ' .jtb-post-meta a { color: ' . $linkColor . '; text-decoration: none; }' . "\n";
        $css .= $selector . ' .jtb-post-meta a:hover { color: ' . $linkHoverColor . '; }' . "\n";
        $css .= $selector . ' .jtb-post-meta-sep { color: ' . $metaColor . '; }' . "\n";

        // Read more
        $css .= $selector . ' .jtb-read-more { ';
        $css .= 'display: inline-flex; ';
        $css .= 'align-items: center; ';
        $css .= 'gap: 6px; ';
        $css .= 'font-size: 14px; ';
        $css .= 'font-weight: 500; ';
        $css .= 'color: ' . $linkColor . '; ';
        $css .= 'text-decoration: none; ';
        $css .= 'transition: color 0.3s ease; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-read-more:hover { color: ' . $linkHoverColor . '; }' . "\n";
        $css .= $selector . ' .jtb-read-more svg { transition: transform 0.3s ease; }' . "\n";
        $css .= $selector . ' .jtb-read-more:hover svg { transform: translateX(4px); }' . "\n";

        // Pagination
        $css .= $selector . ' .jtb-pagination { margin-top: 40px; display: flex; justify-content: center; align-items: center; gap: 8px; flex-wrap: wrap; }' . "\n";
        $css .= $selector . ' .jtb-page-num, ' . $selector . ' .jtb-page-next { ';
        $css .= 'padding: 8px 16px; ';
        $css .= 'background: #f3f4f6; ';
        $css .= 'color: ' . $titleColor . '; ';
        $css .= 'border-radius: 4px; ';
        $css .= 'text-decoration: none; ';
        $css .= 'transition: all 0.3s ease; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-page-num:hover, ' . $selector . ' .jtb-page-next:hover { background: ' . $linkColor . '; color: white; }' . "\n";
        $css .= $selector . ' .jtb-page-active { background: ' . $linkColor . '; color: white; }' . "\n";
        $css .= $selector . ' .jtb-page-dots { padding: 8px 16px; color: ' . $metaColor . '; }' . "\n";
        $css .= $selector . ' .jtb-page-next { display: inline-flex; align-items: center; gap: 4px; }' . "\n";

        // Responsive - Columns
        if (!empty($attrs['columns__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-posts-grid { grid-template-columns: repeat(' . intval($attrs['columns__tablet']) . ', 1fr); }';
            $css .= ' }' . "\n";
        } else {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-posts-grid { grid-template-columns: repeat(2, 1fr); }';
            $css .= ' }' . "\n";
        }

        if (!empty($attrs['columns__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-posts-grid { grid-template-columns: repeat(' . intval($attrs['columns__phone']) . ', 1fr); }';
            $css .= ' }' . "\n";
        } else {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-posts-grid { grid-template-columns: 1fr; }';
            $css .= ' }' . "\n";
        }

        // Responsive - Gap
        if (!empty($attrs['gap__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-posts-grid { gap: ' . intval($attrs['gap__tablet']) . 'px; }';
            $css .= ' }' . "\n";
        }

        if (!empty($attrs['gap__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-posts-grid { gap: ' . intval($attrs['gap__phone']) . 'px; }';
            $css .= ' }' . "\n";
        }

        // List layout responsive
        if ($layout === 'list') {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-post-card { flex-direction: column; }';
            $css .= $selector . ' .jtb-post-image { width: 100%; }';
            $css .= ' }' . "\n";
        }

        return $css;
    }
}

JTB_Registry::register('archive_posts', JTB_Module_Archive_Posts::class);
