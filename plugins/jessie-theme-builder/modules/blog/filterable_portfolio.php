<?php
/**
 * Filterable Portfolio Module
 * Portfolio grid with category filter buttons
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_FilterablePortfolio extends JTB_Element
{
    public string $icon = 'filter';
    public string $category = 'blog';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = true;

    public function getSlug(): string
    {
        return 'filterable_portfolio';
    }

    public function getName(): string
    {
        return 'Filterable Portfolio';
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
                'label' => 'Projects Count',
                'type' => 'range',
                'min' => 1,
                'max' => 50,
                'default' => 12
            ],
            'include_categories' => [
                'label' => 'Include Categories (IDs)',
                'type' => 'text',
                'description' => 'Comma-separated category IDs or leave empty for all'
            ],
            'show_title' => [
                'label' => 'Show Title',
                'type' => 'toggle',
                'default' => true
            ],
            'show_categories' => [
                'label' => 'Show Categories on Item',
                'type' => 'toggle',
                'default' => true
            ],
            'show_filter' => [
                'label' => 'Show Filter Bar',
                'type' => 'toggle',
                'default' => true
            ],
            'filter_all_text' => [
                'label' => 'All Button Text',
                'type' => 'text',
                'default' => 'All'
            ],
            'show_pagination' => [
                'label' => 'Show Pagination',
                'type' => 'toggle',
                'default' => false
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
                'default' => '4',
                'responsive' => true
            ],
            'gutter' => [
                'label' => 'Gutter Width',
                'type' => 'range',
                'min' => 0,
                'max' => 50,
                'unit' => 'px',
                'default' => 15
            ],
            // Filter styling
            'filter_alignment' => [
                'label' => 'Filter Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'center'
            ],
            'filter_bg_color' => [
                'label' => 'Filter Button Background',
                'type' => 'color',
                'default' => '#f1f5f9'
            ],
            'filter_text_color' => [
                'label' => 'Filter Button Text Color',
                'type' => 'color',
                'default' => '#64748b'
            ],
            'filter_active_bg' => [
                'label' => 'Active Filter Background',
                'type' => 'color',
                'default' => '#7c3aed'
            ],
            'filter_active_text' => [
                'label' => 'Active Filter Text Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            // Item styling
            'item_bg_color' => [
                'label' => 'Item Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'hover_overlay_color' => [
                'label' => 'Hover Overlay Color',
                'type' => 'color',
                'default' => 'rgba(124, 58, 237, 0.9)'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'range',
                'min' => 12,
                'max' => 36,
                'unit' => 'px',
                'default' => 18,
                'responsive' => true
            ],
            'category_color' => [
                'label' => 'Category Color',
                'type' => 'color',
                'default' => 'rgba(255,255,255,0.8)'
            ],
            'zoom_icon_color' => [
                'label' => 'Zoom Icon Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'border_radius' => [
                'label' => 'Item Border Radius',
                'type' => 'range',
                'min' => 0,
                'max' => 30,
                'unit' => 'px',
                'default' => 8
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $postsNumber = $attrs['posts_number'] ?? 12;
        $showTitle = $attrs['show_title'] ?? true;
        $showCategories = $attrs['show_categories'] ?? true;
        $showFilter = $attrs['show_filter'] ?? true;
        $filterAllText = $this->esc($attrs['filter_all_text'] ?? 'All');
        $columns = $attrs['columns'] ?? '4';

        // Sample categories
        $categories = ['Web Design', 'Branding', 'Development', 'Photography', 'UI/UX'];

        // Sample portfolio items
        $sampleItems = [
            ['title' => 'E-Commerce Platform', 'categories' => ['Web Design', 'Development'], 'image' => ''],
            ['title' => 'Brand Identity', 'categories' => ['Branding'], 'image' => ''],
            ['title' => 'Mobile App UI', 'categories' => ['UI/UX', 'Development'], 'image' => ''],
            ['title' => 'Product Photography', 'categories' => ['Photography'], 'image' => ''],
            ['title' => 'Corporate Website', 'categories' => ['Web Design'], 'image' => ''],
            ['title' => 'Logo Design', 'categories' => ['Branding'], 'image' => ''],
            ['title' => 'Dashboard Design', 'categories' => ['UI/UX'], 'image' => ''],
            ['title' => 'Event Photography', 'categories' => ['Photography'], 'image' => ''],
            ['title' => 'SaaS Application', 'categories' => ['Web Design', 'UI/UX'], 'image' => ''],
            ['title' => 'Restaurant Branding', 'categories' => ['Branding', 'Photography'], 'image' => ''],
            ['title' => 'Portfolio Website', 'categories' => ['Web Design', 'Development'], 'image' => ''],
            ['title' => 'Fashion Photography', 'categories' => ['Photography'], 'image' => ''],
        ];

        $innerHtml = '<div class="jtb-filterable-portfolio">';

        // Filter bar
        if ($showFilter) {
            $innerHtml .= '<div class="jtb-portfolio-filter">';
            $innerHtml .= '<button class="jtb-filter-btn active" data-filter="*">' . $filterAllText . '</button>';
            foreach ($categories as $cat) {
                $catSlug = strtolower(str_replace([' ', '/'], '-', $cat));
                $innerHtml .= '<button class="jtb-filter-btn" data-filter=".' . $catSlug . '">' . $this->esc($cat) . '</button>';
            }
            $innerHtml .= '</div>';
        }

        // Portfolio grid
        $innerHtml .= '<div class="jtb-portfolio-grid jtb-portfolio-cols-' . $columns . '">';

        foreach ($sampleItems as $item) {
            $catClasses = array_map(function ($c) {
                return strtolower(str_replace([' ', '/'], '-', $c));
            }, $item['categories']);

            $innerHtml .= '<div class="jtb-portfolio-item ' . implode(' ', $catClasses) . '">';
            $innerHtml .= '<div class="jtb-portfolio-inner">';

            // Image
            $innerHtml .= '<div class="jtb-portfolio-image">';
            if (!empty($item['image'])) {
                $innerHtml .= '<img src="' . $this->esc($item['image']) . '" alt="' . $this->esc($item['title']) . '">';
            } else {
                $innerHtml .= '<div class="jtb-portfolio-placeholder"></div>';
            }

            // Overlay
            $innerHtml .= '<div class="jtb-portfolio-overlay">';
            $innerHtml .= '<div class="jtb-portfolio-content">';

            if ($showTitle) {
                $innerHtml .= '<h3 class="jtb-portfolio-title">' . $this->esc($item['title']) . '</h3>';
            }

            if ($showCategories && !empty($item['categories'])) {
                $innerHtml .= '<div class="jtb-portfolio-cats">' . implode(' / ', array_map([$this, 'esc'], $item['categories'])) . '</div>';
            }

            // Actions
            $innerHtml .= '<div class="jtb-portfolio-actions">';
            $innerHtml .= '<a href="#" class="jtb-portfolio-link" title="View Project">';
            $innerHtml .= '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>';
            $innerHtml .= '</a>';
            $innerHtml .= '<a href="#" class="jtb-portfolio-external" title="External Link">';
            $innerHtml .= '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>';
            $innerHtml .= '</a>';
            $innerHtml .= '</div>';

            $innerHtml .= '</div>'; // content
            $innerHtml .= '</div>'; // overlay
            $innerHtml .= '</div>'; // image
            $innerHtml .= '</div>'; // inner
            $innerHtml .= '</div>'; // item
        }

        $innerHtml .= '</div>'; // grid
        $innerHtml .= '</div>'; // container

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        $columns = $attrs['columns'] ?? '4';
        $gutter = $attrs['gutter'] ?? 15;
        $filterAlign = $attrs['filter_alignment'] ?? 'center';
        $filterBg = $attrs['filter_bg_color'] ?? '#f1f5f9';
        $filterText = $attrs['filter_text_color'] ?? '#64748b';
        $filterActiveBg = $attrs['filter_active_bg'] ?? '#7c3aed';
        $filterActiveText = $attrs['filter_active_text'] ?? '#ffffff';
        $overlayColor = $attrs['hover_overlay_color'] ?? 'rgba(124, 58, 237, 0.9)';
        $titleColor = $attrs['title_color'] ?? '#ffffff';
        $titleSize = $attrs['title_font_size'] ?? 18;
        $catColor = $attrs['category_color'] ?? 'rgba(255,255,255,0.8)';
        $iconColor = $attrs['zoom_icon_color'] ?? '#ffffff';
        $borderRadius = $attrs['border_radius'] ?? 8;
        $itemBg = $attrs['item_bg_color'] ?? '#ffffff';

        // Filter bar
        $justify = $filterAlign === 'left' ? 'flex-start' : ($filterAlign === 'right' ? 'flex-end' : 'center');
        $css .= $selector . ' .jtb-portfolio-filter { ';
        $css .= 'display: flex; flex-wrap: wrap; gap: 10px; ';
        $css .= 'justify-content: ' . $justify . '; ';
        $css .= 'margin-bottom: 30px; ';
        $css .= '}' . "\n";

        // Filter buttons
        $css .= $selector . ' .jtb-filter-btn { ';
        $css .= 'padding: 10px 20px; ';
        $css .= 'background: ' . $filterBg . '; ';
        $css .= 'color: ' . $filterText . '; ';
        $css .= 'border: none; border-radius: 6px; ';
        $css .= 'font-size: 14px; font-weight: 500; ';
        $css .= 'cursor: pointer; ';
        $css .= 'transition: all 0.2s ease; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-filter-btn:hover { filter: brightness(0.95); }' . "\n";

        $css .= $selector . ' .jtb-filter-btn.active { ';
        $css .= 'background: ' . $filterActiveBg . '; ';
        $css .= 'color: ' . $filterActiveText . '; ';
        $css .= '}' . "\n";

        // Grid
        $css .= $selector . ' .jtb-portfolio-grid { display: grid; gap: ' . $gutter . 'px; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-cols-2 { grid-template-columns: repeat(2, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-portfolio-cols-3 { grid-template-columns: repeat(3, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-portfolio-cols-4 { grid-template-columns: repeat(4, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-portfolio-cols-5 { grid-template-columns: repeat(5, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-portfolio-cols-6 { grid-template-columns: repeat(6, 1fr); }' . "\n";

        // Item
        $css .= $selector . ' .jtb-portfolio-item { ';
        $css .= 'transition: opacity 0.3s ease, transform 0.3s ease; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-portfolio-item.hidden { opacity: 0; transform: scale(0.8); pointer-events: none; position: absolute; }' . "\n";

        $css .= $selector . ' .jtb-portfolio-inner { ';
        $css .= 'background: ' . $itemBg . '; ';
        $css .= 'border-radius: ' . $borderRadius . 'px; ';
        $css .= 'overflow: hidden; ';
        $css .= 'box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); ';
        $css .= '}' . "\n";

        // Image container
        $css .= $selector . ' .jtb-portfolio-image { position: relative; overflow: hidden; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-image img { width: 100%; height: auto; display: block; transition: transform 0.4s ease; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-item:hover .jtb-portfolio-image img { transform: scale(1.05); }' . "\n";

        // Placeholder
        $css .= $selector . ' .jtb-portfolio-placeholder { background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); padding-bottom: 75%; }' . "\n";

        // Overlay
        $css .= $selector . ' .jtb-portfolio-overlay { ';
        $css .= 'position: absolute; top: 0; left: 0; right: 0; bottom: 0; ';
        $css .= 'background: ' . $overlayColor . '; ';
        $css .= 'display: flex; align-items: center; justify-content: center; ';
        $css .= 'opacity: 0; transition: opacity 0.3s ease; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-portfolio-item:hover .jtb-portfolio-overlay { opacity: 1; }' . "\n";

        // Content
        $css .= $selector . ' .jtb-portfolio-content { ';
        $css .= 'text-align: center; padding: 20px; ';
        $css .= 'transform: translateY(10px); transition: transform 0.3s ease; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-portfolio-item:hover .jtb-portfolio-content { transform: translateY(0); }' . "\n";

        // Title
        $css .= $selector . ' .jtb-portfolio-title { ';
        $css .= 'margin: 0 0 8px; font-size: ' . $titleSize . 'px; ';
        $css .= 'color: ' . $titleColor . '; font-weight: 600; ';
        $css .= '}' . "\n";

        // Categories
        $css .= $selector . ' .jtb-portfolio-cats { ';
        $css .= 'font-size: 13px; color: ' . $catColor . '; margin-bottom: 15px; ';
        $css .= '}' . "\n";

        // Actions
        $css .= $selector . ' .jtb-portfolio-actions { display: flex; gap: 10px; justify-content: center; }' . "\n";

        $css .= $selector . ' .jtb-portfolio-actions a { ';
        $css .= 'display: flex; align-items: center; justify-content: center; ';
        $css .= 'width: 44px; height: 44px; ';
        $css .= 'background: rgba(255,255,255,0.2); ';
        $css .= 'color: ' . $iconColor . '; ';
        $css .= 'border-radius: 50%; ';
        $css .= 'transition: all 0.2s ease; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-portfolio-actions a:hover { background: rgba(255,255,255,0.3); transform: scale(1.1); }' . "\n";

        // Responsive
        $tabletCols = $attrs['columns__tablet'] ?? '3';
        $phoneCols = $attrs['columns__phone'] ?? '2';

        $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-portfolio-grid { grid-template-columns: repeat(' . $tabletCols . ', 1fr); } }' . "\n";
        $css .= '@media (max-width: 767px) { ';
        $css .= $selector . ' .jtb-portfolio-grid { grid-template-columns: repeat(' . $phoneCols . ', 1fr); } ';
        $css .= $selector . ' .jtb-portfolio-filter { gap: 8px; } ';
        $css .= $selector . ' .jtb-filter-btn { padding: 8px 14px; font-size: 13px; } ';
        $css .= '}' . "\n";

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('filterable_portfolio', JTB_Module_FilterablePortfolio::class);
