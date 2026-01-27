<?php
/**
 * Fullwidth Portfolio Module
 * Full-width portfolio grid
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_FullwidthPortfolio extends JTB_Element
{
    public string $icon = 'portfolio-fullwidth';
    public string $category = 'fullwidth';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = false;
    public bool $use_box_shadow = false;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    public function getSlug(): string
    {
        return 'fullwidth_portfolio';
    }

    public function getName(): string
    {
        return 'Fullwidth Portfolio';
    }

    public function getFields(): array
    {
        return [
            'posts_per_page' => [
                'label' => 'Number of Projects',
                'type' => 'range',
                'min' => 1,
                'max' => 24,
                'default' => 8
            ],
            'columns' => [
                'label' => 'Columns',
                'type' => 'select',
                'options' => [
                    '2' => '2 Columns',
                    '3' => '3 Columns',
                    '4' => '4 Columns',
                    '5' => '5 Columns',
                    '6' => '6 Columns'
                ],
                'default' => '4'
            ],
            'layout' => [
                'label' => 'Layout',
                'type' => 'select',
                'options' => [
                    'grid' => 'Grid',
                    'masonry' => 'Masonry'
                ],
                'default' => 'grid'
            ],
            'show_title' => [
                'label' => 'Show Title',
                'type' => 'toggle',
                'default' => true
            ],
            'show_categories' => [
                'label' => 'Show Categories',
                'type' => 'toggle',
                'default' => true
            ],
            'show_pagination' => [
                'label' => 'Show Pagination',
                'type' => 'toggle',
                'default' => false
            ],
            'category_filter' => [
                'label' => 'Category Filter',
                'type' => 'select',
                'options' => [
                    'all' => 'All Categories',
                    'design' => 'Design',
                    'development' => 'Development',
                    'marketing' => 'Marketing'
                ],
                'default' => 'all'
            ],
            'overlay_style' => [
                'label' => 'Overlay Style',
                'type' => 'select',
                'options' => [
                    'overlay' => 'Full Overlay',
                    'slide_up' => 'Slide Up',
                    'zoom' => 'Zoom'
                ],
                'default' => 'overlay'
            ],
            'gap_width' => [
                'label' => 'Gap Between Items',
                'type' => 'range',
                'min' => 0,
                'max' => 30,
                'unit' => 'px',
                'default' => 0
            ],
            // Colors
            'overlay_color' => [
                'label' => 'Overlay Color',
                'type' => 'color',
                'default' => 'rgba(0,0,0,0.7)'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'category_color' => [
                'label' => 'Category Color',
                'type' => 'color',
                'default' => 'rgba(255,255,255,0.8)'
            ],
            'icon_color' => [
                'label' => 'Icon Color',
                'type' => 'color',
                'default' => '#ffffff'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $columns = $attrs['columns'] ?? '4';
        $layout = $attrs['layout'] ?? 'grid';
        $showTitle = $attrs['show_title'] ?? true;
        $showCategories = $attrs['show_categories'] ?? true;
        $overlayStyle = $attrs['overlay_style'] ?? 'overlay';
        $postsPerPage = $attrs['posts_per_page'] ?? 8;

        // Sample portfolio items
        $portfolioItems = [
            ['title' => 'Brand Identity', 'category' => 'Design', 'image' => ''],
            ['title' => 'E-commerce Platform', 'category' => 'Development', 'image' => ''],
            ['title' => 'Mobile Application', 'category' => 'Development', 'image' => ''],
            ['title' => 'Marketing Campaign', 'category' => 'Marketing', 'image' => ''],
            ['title' => 'Website Redesign', 'category' => 'Design', 'image' => ''],
            ['title' => 'Social Media Strategy', 'category' => 'Marketing', 'image' => ''],
            ['title' => 'Product Photography', 'category' => 'Design', 'image' => ''],
            ['title' => 'SaaS Dashboard', 'category' => 'Development', 'image' => '']
        ];

        $containerClass = 'jtb-fullwidth-portfolio-container jtb-portfolio-layout-' . $layout . ' jtb-portfolio-style-' . $overlayStyle;

        $innerHtml = '<div class="' . $containerClass . '">';
        $innerHtml .= '<div class="jtb-portfolio-grid jtb-portfolio-cols-' . $columns . '">';

        $count = 0;
        foreach ($portfolioItems as $item) {
            if ($count >= $postsPerPage) break;

            $innerHtml .= '<div class="jtb-portfolio-item">';
            $innerHtml .= '<div class="jtb-portfolio-image">';

            if (!empty($item['image'])) {
                $innerHtml .= '<img src="' . $this->esc($item['image']) . '" alt="' . $this->esc($item['title']) . '" />';
            } else {
                $innerHtml .= '<div class="jtb-portfolio-placeholder" style="background: hsl(' . ($count * 45) . ', 60%, 50%);"></div>';
            }

            $innerHtml .= '<div class="jtb-portfolio-overlay">';
            $innerHtml .= '<div class="jtb-portfolio-overlay-content">';

            if ($showTitle) {
                $innerHtml .= '<h3 class="jtb-portfolio-title">' . $this->esc($item['title']) . '</h3>';
            }

            if ($showCategories) {
                $innerHtml .= '<span class="jtb-portfolio-category">' . $this->esc($item['category']) . '</span>';
            }

            $linkIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>';
            $searchIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>';
            $innerHtml .= '<div class="jtb-portfolio-icons">';
            $innerHtml .= '<a href="#" class="jtb-portfolio-icon jtb-portfolio-link" title="View Project">' . $linkIcon . '</a>';
            $innerHtml .= '<a href="#" class="jtb-portfolio-icon jtb-portfolio-expand" title="View Image">' . $searchIcon . '</a>';
            $innerHtml .= '</div>';

            $innerHtml .= '</div>';
            $innerHtml .= '</div>';
            $innerHtml .= '</div>';
            $innerHtml .= '</div>';

            $count++;
        }

        $innerHtml .= '</div>';
        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        $columns = $attrs['columns'] ?? '4';
        $gapWidth = $attrs['gap_width'] ?? 0;
        $overlayColor = $attrs['overlay_color'] ?? 'rgba(0,0,0,0.7)';
        $titleColor = $attrs['title_color'] ?? '#ffffff';
        $categoryColor = $attrs['category_color'] ?? 'rgba(255,255,255,0.8)';
        $iconColor = $attrs['icon_color'] ?? '#ffffff';
        $overlayStyle = $attrs['overlay_style'] ?? 'overlay';

        // Grid
        $css .= $selector . ' .jtb-portfolio-grid { '
            . 'display: grid; '
            . 'gap: ' . $gapWidth . 'px; '
            . '}' . "\n";

        // Columns
        $css .= $selector . ' .jtb-portfolio-cols-2 { grid-template-columns: repeat(2, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-portfolio-cols-3 { grid-template-columns: repeat(3, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-portfolio-cols-4 { grid-template-columns: repeat(4, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-portfolio-cols-5 { grid-template-columns: repeat(5, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-portfolio-cols-6 { grid-template-columns: repeat(6, 1fr); }' . "\n";

        // Item
        $css .= $selector . ' .jtb-portfolio-item { overflow: hidden; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-image { position: relative; overflow: hidden; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-image img { width: 100%; height: auto; display: block; transition: transform 0.5s ease; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-placeholder { padding-bottom: 100%; }' . "\n";

        // Overlay
        $css .= $selector . ' .jtb-portfolio-overlay { '
            . 'position: absolute; '
            . 'top: 0; left: 0; right: 0; bottom: 0; '
            . 'background: ' . $overlayColor . '; '
            . 'display: flex; '
            . 'align-items: center; '
            . 'justify-content: center; '
            . 'opacity: 0; '
            . 'transition: all 0.4s ease; '
            . '}' . "\n";

        // Overlay styles
        if ($overlayStyle === 'slide_up') {
            $css .= $selector . ' .jtb-portfolio-overlay { transform: translateY(100%); opacity: 1; }' . "\n";
            $css .= $selector . ' .jtb-portfolio-image:hover .jtb-portfolio-overlay { transform: translateY(0); }' . "\n";
        } elseif ($overlayStyle === 'zoom') {
            $css .= $selector . ' .jtb-portfolio-image:hover img { transform: scale(1.1); }' . "\n";
            $css .= $selector . ' .jtb-portfolio-image:hover .jtb-portfolio-overlay { opacity: 1; }' . "\n";
        } else {
            $css .= $selector . ' .jtb-portfolio-image:hover .jtb-portfolio-overlay { opacity: 1; }' . "\n";
        }

        // Content
        $css .= $selector . ' .jtb-portfolio-overlay-content { text-align: center; padding: 20px; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-title { color: ' . $titleColor . '; font-size: 18px; margin: 0 0 5px; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-category { color: ' . $categoryColor . '; font-size: 14px; display: block; margin-bottom: 15px; }' . "\n";

        // Icons
        $css .= $selector . ' .jtb-portfolio-icons { display: flex; gap: 10px; justify-content: center; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-icon { '
            . 'color: ' . $iconColor . '; '
            . 'width: 40px; '
            . 'height: 40px; '
            . 'display: flex; '
            . 'align-items: center; '
            . 'justify-content: center; '
            . 'border: 1px solid ' . $iconColor . '; '
            . 'text-decoration: none; '
            . 'transition: all 0.3s ease; '
            . '}' . "\n";

        $css .= $selector . ' .jtb-portfolio-icon svg { width: 18px; height: 18px; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-icon:hover { background: ' . $iconColor . '; color: #333; }' . "\n";

        // Responsive
        $css .= '@media (max-width: 980px) {' . "\n";
        $css .= '  ' . $selector . ' .jtb-portfolio-cols-4, ' . $selector . ' .jtb-portfolio-cols-5, ' . $selector . ' .jtb-portfolio-cols-6 { grid-template-columns: repeat(3, 1fr); }' . "\n";
        $css .= '}' . "\n";

        $css .= '@media (max-width: 767px) {' . "\n";
        $css .= '  ' . $selector . ' .jtb-portfolio-grid { grid-template-columns: repeat(2, 1fr) !important; }' . "\n";
        $css .= '}' . "\n";

        $css .= '@media (max-width: 480px) {' . "\n";
        $css .= '  ' . $selector . ' .jtb-portfolio-grid { grid-template-columns: 1fr !important; }' . "\n";
        $css .= '}' . "\n";

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('fullwidth_portfolio', JTB_Module_FullwidthPortfolio::class);
