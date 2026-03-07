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

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'filterable_portfolio';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'gutter' => [
            'property' => 'gap',
            'selector' => '.jtb-portfolio-grid',
            'unit' => 'px'
        ],
        'filter_bg_color' => [
            'property' => 'background',
            'selector' => '.jtb-filter-btn'
        ],
        'filter_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-filter-btn'
        ],
        'filter_active_bg' => [
            'property' => 'background',
            'selector' => '.jtb-filter-btn.active'
        ],
        'filter_active_text' => [
            'property' => 'color',
            'selector' => '.jtb-filter-btn.active'
        ],
        'item_bg_color' => [
            'property' => 'background',
            'selector' => '.jtb-portfolio-inner'
        ],
        'hover_overlay_color' => [
            'property' => 'background',
            'selector' => '.jtb-portfolio-overlay'
        ],
        'title_color' => [
            'property' => 'color',
            'selector' => '.jtb-portfolio-title'
        ],
        'title_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-portfolio-title',
            'unit' => 'px',
            'responsive' => true
        ],
        'category_color' => [
            'property' => 'color',
            'selector' => '.jtb-portfolio-cats'
        ],
        'zoom_icon_color' => [
            'property' => 'color',
            'selector' => '.jtb-portfolio-actions a'
        ],
        'border_radius' => [
            'property' => 'border-radius',
            'selector' => '.jtb-portfolio-inner',
            'unit' => 'px'
        ]
    ];

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
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $postsNumber = $attrs['posts_number'] ?? 12;
        $showTitle = $attrs['show_title'] ?? true;
        $showCategories = $attrs['show_categories'] ?? true;
        $showFilter = $attrs['show_filter'] ?? true;
        $filterAllText = $this->esc($attrs['filter_all_text'] ?? 'All');
        $columns = $attrs['columns'] ?? '4';

        // Sample categories
        // Fetch real data from DB
        [$sampleItems, $categories] = $this->fetchProjectsWithCategories($attrs);

        $innerHtml = '<div class="jtb-filterable-portfolio">';

        // Filter bar
        if ($showFilter && !empty($categories)) {
            $innerHtml .= '<div class="jtb-portfolio-filter">';
            $innerHtml .= '<button class="jtb-filter-btn active" data-filter="*">' . $this->esc($filterAllText) . '</button>';
            foreach ($categories as $catSlug => $catName) {
                $innerHtml .= '<button class="jtb-filter-btn" data-filter=".cat-' . $this->esc($catSlug) . '">' . $this->esc($catName) . '</button>';
            }
            $innerHtml .= '</div>';
        }

        // Portfolio grid
        $innerHtml .= '<div class="jtb-portfolio-grid jtb-portfolio-cols-' . $columns . '">';

        if (empty($sampleItems)) {
            $innerHtml .= '<p class="jtb-portfolio-empty">No projects found.</p>';
        }

        foreach ($sampleItems as $item) {
            $catSlug  = 'cat-' . $this->esc($item['category_slug'] ?? 'uncategorized');
            $catName  = $this->esc($item['category_name'] ?? '');
            $itemUrl  = '/portfolio/' . $this->esc($item['slug'] ?? '');
            $extUrl   = !empty($item['project_url']) ? $this->esc($item['project_url']) : $itemUrl;

            $innerHtml .= '<div class="jtb-portfolio-item ' . $catSlug . '">';
            $innerHtml .= '<div class="jtb-portfolio-inner">';

            $innerHtml .= '<div class="jtb-portfolio-image">';
            if (!empty($item['cover_image'])) {
                $innerHtml .= '<img src="' . $this->esc($item['cover_image']) . '" alt="' . $this->esc($item['title']) . '" loading="lazy">';
            } else {
                $innerHtml .= '<div class="jtb-portfolio-placeholder"></div>';
            }

            $innerHtml .= '<div class="jtb-portfolio-overlay">';
            $innerHtml .= '<div class="jtb-portfolio-content">';

            if ($showTitle) {
                $innerHtml .= '<h3 class="jtb-portfolio-title">' . $this->esc($item['title']) . '</h3>';
            }

            if ($showCategories && $catName) {
                $innerHtml .= '<div class="jtb-portfolio-cats">' . $catName . '</div>';
            }

            $innerHtml .= '<div class="jtb-portfolio-actions">';
            $innerHtml .= '<a href="' . $itemUrl . '" class="jtb-portfolio-link" title="View Project">';
            $innerHtml .= '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>';
            $innerHtml .= '</a>';
            if (!empty($item['project_url'])) {
                $innerHtml .= '<a href="' . $extUrl . '" class="jtb-portfolio-external" title="Visit Project" target="_blank" rel="noopener">';
                $innerHtml .= '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>';
                $innerHtml .= '</a>';
            }
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

    /**
     * Generate CSS for Filterable Portfolio module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $filterAlign = $attrs['filter_alignment'] ?? 'center';

        // Filter bar
        $justify = $filterAlign === 'left' ? 'flex-start' : ($filterAlign === 'right' ? 'flex-end' : 'center');
        $css .= $selector . ' .jtb-portfolio-filter { display: flex; flex-wrap: wrap; gap: 10px; justify-content: ' . $justify . '; margin-bottom: 30px; }' . "\n";

        // Filter buttons base styles
        $css .= $selector . ' .jtb-filter-btn { padding: 10px 20px; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; }' . "\n";
        $css .= $selector . ' .jtb-filter-btn:hover { filter: brightness(0.95); }' . "\n";

        // Grid
        $css .= $selector . ' .jtb-portfolio-grid { display: grid; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-cols-2 { grid-template-columns: repeat(2, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-portfolio-cols-3 { grid-template-columns: repeat(3, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-portfolio-cols-4 { grid-template-columns: repeat(4, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-portfolio-cols-5 { grid-template-columns: repeat(5, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-portfolio-cols-6 { grid-template-columns: repeat(6, 1fr); }' . "\n";

        // Item
        $css .= $selector . ' .jtb-portfolio-item { transition: opacity 0.3s ease, transform 0.3s ease; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-item.hidden { opacity: 0; transform: scale(0.8); pointer-events: none; position: absolute; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-inner { overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }' . "\n";

        // Image container
        $css .= $selector . ' .jtb-portfolio-image { position: relative; overflow: hidden; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-image img { width: 100%; height: auto; display: block; transition: transform 0.4s ease; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-item:hover .jtb-portfolio-image img { transform: scale(1.05); }' . "\n";

        // Placeholder
        $css .= $selector . ' .jtb-portfolio-placeholder { background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); padding-bottom: 75%; }' . "\n";

        // Overlay
        $css .= $selector . ' .jtb-portfolio-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-item:hover .jtb-portfolio-overlay { opacity: 1; }' . "\n";

        // Content
        $css .= $selector . ' .jtb-portfolio-content { text-align: center; padding: 20px; transform: translateY(10px); transition: transform 0.3s ease; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-item:hover .jtb-portfolio-content { transform: translateY(0); }' . "\n";

        // Title
        $css .= $selector . ' .jtb-portfolio-title { margin: 0 0 8px; font-weight: 600; }' . "\n";

        // Categories
        $css .= $selector . ' .jtb-portfolio-cats { font-size: 13px; margin-bottom: 15px; }' . "\n";

        // Actions
        $css .= $selector . ' .jtb-portfolio-actions { display: flex; gap: 10px; justify-content: center; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-actions a { display: flex; align-items: center; justify-content: center; width: 44px; height: 44px; background: rgba(255,255,255,0.2); border-radius: 50%; transition: all 0.2s ease; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-actions a:hover { background: rgba(255,255,255,0.3); transform: scale(1.1); }' . "\n";

        // Responsive
        $tabletCols = $attrs['columns__tablet'] ?? '3';
        $phoneCols = $attrs['columns__phone'] ?? '2';

        $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-portfolio-grid { grid-template-columns: repeat(' . $tabletCols . ', 1fr); } }' . "\n";
        $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-portfolio-grid { grid-template-columns: repeat(' . $phoneCols . ', 1fr); } ' . $selector . ' .jtb-portfolio-filter { gap: 8px; } ' . $selector . ' .jtb-filter-btn { padding: 8px 14px; font-size: 13px; } }' . "\n";

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }

    /**
     * Returns [items[], categories[slug => name]]
     */
    private function fetchProjectsWithCategories(array $attrs): array
    {
        try {
            $pdo    = db();
            $limit  = max(1, (int)($attrs['posts_number'] ?? 12));
            $where  = ["p.status = 'published'"];
            $params = [];

            if (!empty($attrs['category_id'])) {
                $where[]  = 'p.category_id = ?';
                $params[] = (int)$attrs['category_id'];
            }

            $whereClause = implode(' AND ', $where);
            $params[]    = $limit;

            $sql = "
                SELECT p.id, p.slug, p.title, p.short_description,
                       p.cover_image, p.project_url, p.is_featured,
                       c.name AS category_name, c.slug AS category_slug
                FROM portfolio_projects p
                LEFT JOIN portfolio_categories c ON p.category_id = c.id
                WHERE $whereClause
                ORDER BY p.sort_order ASC, p.created_at DESC
                LIMIT ?
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Build unique categories map [slug => name]
            $categories = [];
            foreach ($items as $item) {
                if (!empty($item['category_slug']) && !empty($item['category_name'])) {
                    $categories[$item['category_slug']] = $item['category_name'];
                }
            }

            return [$items, $categories];

        } catch (\Throwable $e) {
            return [[], []];
        }
    }
}

JTB_Registry::register('filterable_portfolio', JTB_Module_FilterablePortfolio::class);
