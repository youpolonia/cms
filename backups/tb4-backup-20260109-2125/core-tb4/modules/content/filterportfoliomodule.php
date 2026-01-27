<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Filterable Portfolio Module
 * Displays portfolio grid with category filter buttons and animated filtering
 */
class FilterPortfolioModule extends Module
{
    public function __construct()
    {
        $this->name = 'Filterable Portfolio';
        $this->slug = 'filter_portfolio';
        $this->icon = 'filter';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-filter-portfolio-preview',
            'filter' => '.tb4-filter-buttons',
            'filter_btn' => '.tb4-filter-btn',
            'grid' => '.tb4-filter-grid',
            'item' => '.tb4-filter-item',
            'inner' => '.tb4-filter-item-inner',
            'overlay' => '.tb4-filter-item-overlay',
            'title' => '.tb4-filter-item-title',
            'category' => '.tb4-filter-item-category'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'item1_image' => [
                'label' => 'Item 1 Image URL',
                'type' => 'text',
                'default' => ''
            ],
            'item1_title' => [
                'label' => 'Item 1 Title',
                'type' => 'text',
                'default' => 'Brand Identity'
            ],
            'item1_category' => [
                'label' => 'Item 1 Category',
                'type' => 'text',
                'default' => 'Branding'
            ],
            'item1_link' => [
                'label' => 'Item 1 Link',
                'type' => 'text',
                'default' => '#'
            ],
            'item2_image' => [
                'label' => 'Item 2 Image URL',
                'type' => 'text',
                'default' => ''
            ],
            'item2_title' => [
                'label' => 'Item 2 Title',
                'type' => 'text',
                'default' => 'Website Redesign'
            ],
            'item2_category' => [
                'label' => 'Item 2 Category',
                'type' => 'text',
                'default' => 'Web Design'
            ],
            'item2_link' => [
                'label' => 'Item 2 Link',
                'type' => 'text',
                'default' => '#'
            ],
            'item3_image' => [
                'label' => 'Item 3 Image URL',
                'type' => 'text',
                'default' => ''
            ],
            'item3_title' => [
                'label' => 'Item 3 Title',
                'type' => 'text',
                'default' => 'Mobile App'
            ],
            'item3_category' => [
                'label' => 'Item 3 Category',
                'type' => 'text',
                'default' => 'Development'
            ],
            'item3_link' => [
                'label' => 'Item 3 Link',
                'type' => 'text',
                'default' => '#'
            ],
            'item4_image' => [
                'label' => 'Item 4 Image URL',
                'type' => 'text',
                'default' => ''
            ],
            'item4_title' => [
                'label' => 'Item 4 Title',
                'type' => 'text',
                'default' => 'Logo Design'
            ],
            'item4_category' => [
                'label' => 'Item 4 Category',
                'type' => 'text',
                'default' => 'Branding'
            ],
            'item4_link' => [
                'label' => 'Item 4 Link',
                'type' => 'text',
                'default' => '#'
            ],
            'item5_image' => [
                'label' => 'Item 5 Image URL',
                'type' => 'text',
                'default' => ''
            ],
            'item5_title' => [
                'label' => 'Item 5 Title',
                'type' => 'text',
                'default' => 'E-commerce Platform'
            ],
            'item5_category' => [
                'label' => 'Item 5 Category',
                'type' => 'text',
                'default' => 'Web Design'
            ],
            'item5_link' => [
                'label' => 'Item 5 Link',
                'type' => 'text',
                'default' => '#'
            ],
            'item6_image' => [
                'label' => 'Item 6 Image URL',
                'type' => 'text',
                'default' => ''
            ],
            'item6_title' => [
                'label' => 'Item 6 Title',
                'type' => 'text',
                'default' => 'Dashboard UI'
            ],
            'item6_category' => [
                'label' => 'Item 6 Category',
                'type' => 'text',
                'default' => 'Development'
            ],
            'item6_link' => [
                'label' => 'Item 6 Link',
                'type' => 'text',
                'default' => '#'
            ],
            'show_all_filter' => [
                'label' => 'Show "All" Filter',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'all_filter_text' => [
                'label' => '"All" Filter Text',
                'type' => 'text',
                'default' => 'All'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'columns' => [
                'label' => 'Columns',
                'type' => 'select',
                'options' => [
                    '2' => '2 Columns',
                    '3' => '3 Columns',
                    '4' => '4 Columns'
                ],
                'default' => '3'
            ],
            'gap' => [
                'label' => 'Grid Gap',
                'type' => 'text',
                'default' => '24px'
            ],
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
            'filter_style' => [
                'label' => 'Filter Style',
                'type' => 'select',
                'options' => [
                    'buttons' => 'Buttons',
                    'pills' => 'Pills',
                    'underline' => 'Underline'
                ],
                'default' => 'pills'
            ],
            'filter_bg_color' => [
                'label' => 'Filter Background',
                'type' => 'color',
                'default' => '#f3f4f6'
            ],
            'filter_active_bg' => [
                'label' => 'Filter Active Background',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'filter_text_color' => [
                'label' => 'Filter Text Color',
                'type' => 'color',
                'default' => '#374151'
            ],
            'filter_active_text' => [
                'label' => 'Filter Active Text',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'filter_gap' => [
                'label' => 'Filter Buttons Gap',
                'type' => 'text',
                'default' => '8px'
            ],
            'filter_margin_bottom' => [
                'label' => 'Filter Margin Bottom',
                'type' => 'text',
                'default' => '32px'
            ],
            'item_bg_color' => [
                'label' => 'Item Background',
                'type' => 'color',
                'default' => '#1f2937'
            ],
            'item_border_radius' => [
                'label' => 'Item Border Radius',
                'type' => 'text',
                'default' => '12px'
            ],
            'overlay_color' => [
                'label' => 'Hover Overlay',
                'type' => 'color',
                'default' => 'rgba(37, 99, 235, 0.9)'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'text',
                'default' => '18px'
            ],
            'category_color' => [
                'label' => 'Category Color',
                'type' => 'color',
                'default' => 'rgba(255,255,255,0.8)'
            ],
            'animation_type' => [
                'label' => 'Filter Animation',
                'type' => 'select',
                'options' => [
                    'fade' => 'Fade',
                    'scale' => 'Scale',
                    'slide' => 'Slide Up'
                ],
                'default' => 'fade'
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
     * Get gradient backgrounds for placeholder items
     */
    private function getGradients(): array
    {
        return [
            'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
            'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
            'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
            'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
            'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)'
        ];
    }

    public function render(array $settings): string
    {
        // Collect items from settings
        $items = [];
        $categories = [];

        for ($i = 1; $i <= 6; $i++) {
            $title = $settings['item' . $i . '_title'] ?? '';
            $category = $settings['item' . $i . '_category'] ?? '';
            $image = $settings['item' . $i . '_image'] ?? '';
            $link = $settings['item' . $i . '_link'] ?? '#';

            if (trim($title) !== '') {
                $items[] = [
                    'title' => $title,
                    'category' => $category,
                    'image' => $image,
                    'link' => $link
                ];

                if (trim($category) !== '' && !in_array($category, $categories)) {
                    $categories[] = $category;
                }
            }
        }

        // Default items if none provided
        if (empty($items)) {
            $items = [
                ['title' => 'Brand Identity', 'category' => 'Branding', 'image' => '', 'link' => '#'],
                ['title' => 'Website Redesign', 'category' => 'Web Design', 'image' => '', 'link' => '#'],
                ['title' => 'Mobile App', 'category' => 'Development', 'image' => '', 'link' => '#'],
                ['title' => 'Logo Design', 'category' => 'Branding', 'image' => '', 'link' => '#'],
                ['title' => 'E-commerce Platform', 'category' => 'Web Design', 'image' => '', 'link' => '#'],
                ['title' => 'Dashboard UI', 'category' => 'Development', 'image' => '', 'link' => '#']
            ];
            $categories = ['Branding', 'Web Design', 'Development'];
        }

        // Design settings
        $columns = $settings['columns'] ?? '3';
        $gap = $settings['gap'] ?? '24px';
        $filterAlignment = $settings['filter_alignment'] ?? 'center';
        $filterStyle = $settings['filter_style'] ?? 'pills';
        $filterBg = $settings['filter_bg_color'] ?? '#f3f4f6';
        $filterActiveBg = $settings['filter_active_bg'] ?? '#2563eb';
        $filterTextColor = $settings['filter_text_color'] ?? '#374151';
        $filterActiveText = $settings['filter_active_text'] ?? '#ffffff';
        $filterGap = $settings['filter_gap'] ?? '8px';
        $filterMarginBottom = $settings['filter_margin_bottom'] ?? '32px';
        $itemBg = $settings['item_bg_color'] ?? '#1f2937';
        $itemRadius = $settings['item_border_radius'] ?? '12px';
        $overlayColor = $settings['overlay_color'] ?? 'rgba(37, 99, 235, 0.9)';
        $titleColor = $settings['title_color'] ?? '#ffffff';
        $titleSize = $settings['title_font_size'] ?? '18px';
        $categoryColor = $settings['category_color'] ?? 'rgba(255,255,255,0.8)';
        $showAllFilter = ($settings['show_all_filter'] ?? 'yes') !== 'no';
        $allFilterText = $settings['all_filter_text'] ?? 'All';

        // Calculate justify-content
        $justifyMap = [
            'left' => 'flex-start',
            'center' => 'center',
            'right' => 'flex-end'
        ];
        $justify = $justifyMap[$filterAlignment] ?? 'center';

        // Button border-radius based on style
        $btnRadius = match ($filterStyle) {
            'pills' => '20px',
            'buttons' => '8px',
            'underline' => '0',
            default => '20px'
        };

        // Build unique ID
        $uniqueId = 'tb4-filter-portfolio-' . uniqid();

        // Build HTML
        $html = '<div class="tb4-filter-portfolio-preview" id="' . esc_attr($uniqueId) . '">';

        // Filter buttons
        $html .= '<div class="tb4-filter-buttons" style="display:flex;flex-wrap:wrap;gap:' . esc_attr($filterGap) . ';justify-content:' . $justify . ';margin-bottom:' . esc_attr($filterMarginBottom) . ';">';

        if ($showAllFilter) {
            if ($filterStyle === 'underline') {
                $html .= '<button class="tb4-filter-btn active" data-filter="all" style="background:transparent;border:none;border-bottom:2px solid ' . esc_attr($filterActiveBg) . ';border-radius:0;padding:8px 16px;font-size:14px;font-weight:500;color:' . esc_attr($filterActiveBg) . ';cursor:pointer;transition:all 0.3s;">' . esc_html($allFilterText) . '</button>';
            } else {
                $html .= '<button class="tb4-filter-btn active" data-filter="all" style="background:' . esc_attr($filterActiveBg) . ';border:none;border-radius:' . $btnRadius . ';padding:8px 20px;font-size:14px;font-weight:500;color:' . esc_attr($filterActiveText) . ';cursor:pointer;transition:all 0.3s;">' . esc_html($allFilterText) . '</button>';
            }
        }

        foreach ($categories as $idx => $cat) {
            $isFirst = !$showAllFilter && $idx === 0;
            $catSlug = strtolower(preg_replace('/\s+/', '-', $cat));

            if ($filterStyle === 'underline') {
                if ($isFirst) {
                    $html .= '<button class="tb4-filter-btn active" data-filter="' . esc_attr($catSlug) . '" style="background:transparent;border:none;border-bottom:2px solid ' . esc_attr($filterActiveBg) . ';border-radius:0;padding:8px 16px;font-size:14px;font-weight:500;color:' . esc_attr($filterActiveBg) . ';cursor:pointer;transition:all 0.3s;">' . esc_html($cat) . '</button>';
                } else {
                    $html .= '<button class="tb4-filter-btn" data-filter="' . esc_attr($catSlug) . '" style="background:transparent;border:none;border-bottom:2px solid transparent;border-radius:0;padding:8px 16px;font-size:14px;font-weight:500;color:' . esc_attr($filterTextColor) . ';cursor:pointer;transition:all 0.3s;">' . esc_html($cat) . '</button>';
                }
            } else {
                if ($isFirst) {
                    $html .= '<button class="tb4-filter-btn active" data-filter="' . esc_attr($catSlug) . '" style="background:' . esc_attr($filterActiveBg) . ';border:none;border-radius:' . $btnRadius . ';padding:8px 20px;font-size:14px;font-weight:500;color:' . esc_attr($filterActiveText) . ';cursor:pointer;transition:all 0.3s;">' . esc_html($cat) . '</button>';
                } else {
                    $html .= '<button class="tb4-filter-btn" data-filter="' . esc_attr($catSlug) . '" style="background:' . esc_attr($filterBg) . ';border:none;border-radius:' . $btnRadius . ';padding:8px 20px;font-size:14px;font-weight:500;color:' . esc_attr($filterTextColor) . ';cursor:pointer;transition:all 0.3s;">' . esc_html($cat) . '</button>';
                }
            }
        }

        $html .= '</div>';

        // Grid
        $html .= '<div class="tb4-filter-grid" style="display:grid;grid-template-columns:repeat(' . esc_attr($columns) . ',1fr);gap:' . esc_attr($gap) . ';">';

        $gradients = $this->getGradients();

        foreach ($items as $index => $item) {
            $catSlug = strtolower(preg_replace('/\s+/', '-', $item['category']));
            $itemBgStyle = !empty($item['image']) ? 'url(' . esc_attr($item['image']) . ')' : $gradients[$index % count($gradients)];

            $html .= '<div class="tb4-filter-item" data-category="' . esc_attr($catSlug) . '" style="position:relative;border-radius:' . esc_attr($itemRadius) . ';overflow:hidden;aspect-ratio:4/3;background:' . esc_attr($itemBg) . ';">';
            $html .= '<div class="tb4-filter-item-inner" style="width:100%;height:100%;background:' . $itemBgStyle . ';background-size:cover;background-position:center;"></div>';
            $html .= '<div class="tb4-filter-item-overlay" style="position:absolute;inset:0;background:' . esc_attr($overlayColor) . ';display:flex;flex-direction:column;align-items:center;justify-content:center;opacity:0;transition:opacity 0.3s;">';
            $html .= '<h4 class="tb4-filter-item-title" style="font-size:' . esc_attr($titleSize) . ';font-weight:600;color:' . esc_attr($titleColor) . ';margin:0 0 4px 0;text-align:center;">' . esc_html($item['title']) . '</h4>';
            $html .= '<span class="tb4-filter-item-category" style="font-size:14px;color:' . esc_attr($categoryColor) . ';">' . esc_html($item['category']) . '</span>';
            $html .= '</div></div>';
        }

        $html .= '</div></div>';

        // Add scoped CSS for hover effects
        $html .= '<style>';
        $html .= '#' . $uniqueId . ' .tb4-filter-item:hover .tb4-filter-item-overlay { opacity: 1; }';
        $html .= '#' . $uniqueId . ' .tb4-filter-btn:hover { opacity: 0.8; }';
        $html .= '</style>';

        return $html;
    }
}
