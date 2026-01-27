<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Portfolio Module
 * Displays portfolio/project gallery with hover effects
 */
class PortfolioModule extends Module
{
    public function __construct()
    {
        $this->name = 'Portfolio';
        $this->slug = 'portfolio';
        $this->icon = 'layout-grid';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-portfolio-preview',
            'filter' => '.tb4-portfolio-filter',
            'filter_btn' => '.tb4-portfolio-filter-btn',
            'grid' => '.tb4-portfolio-grid',
            'item' => '.tb4-portfolio-item',
            'image' => '.tb4-portfolio-image',
            'overlay' => '.tb4-portfolio-overlay',
            'icon' => '.tb4-portfolio-icon',
            'title' => '.tb4-portfolio-title',
            'category' => '.tb4-portfolio-category'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'items_count' => [
                'label' => 'Number of Items',
                'type' => 'select',
                'options' => [
                    '4' => '4 Items',
                    '6' => '6 Items',
                    '8' => '8 Items',
                    '9' => '9 Items',
                    '12' => '12 Items'
                ],
                'default' => '6'
            ],
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
            'show_title' => [
                'label' => 'Show Title',
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
            'show_description' => [
                'label' => 'Show Description',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'no'
            ],
            'hover_effect' => [
                'label' => 'Hover Effect',
                'type' => 'select',
                'options' => [
                    'fade' => 'Fade Overlay',
                    'slide-up' => 'Slide Up',
                    'zoom' => 'Zoom',
                    'none' => 'None'
                ],
                'default' => 'fade'
            ],
            'click_action' => [
                'label' => 'Click Action',
                'type' => 'select',
                'options' => [
                    'lightbox' => 'Open Lightbox',
                    'link' => 'Go to Project',
                    'none' => 'None'
                ],
                'default' => 'lightbox'
            ],
            'show_filter' => [
                'label' => 'Show Category Filter',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'no'
            ],
            'filter_style' => [
                'label' => 'Filter Style',
                'type' => 'select',
                'options' => [
                    'buttons' => 'Buttons',
                    'dropdown' => 'Dropdown',
                    'tabs' => 'Tabs'
                ],
                'default' => 'buttons'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'gap' => [
                'label' => 'Gap Between Items',
                'type' => 'text',
                'default' => '16px'
            ],
            'item_border_radius' => [
                'label' => 'Item Border Radius',
                'type' => 'text',
                'default' => '12px'
            ],
            'image_aspect_ratio' => [
                'label' => 'Image Aspect Ratio',
                'type' => 'select',
                'options' => [
                    'square' => 'Square (1:1)',
                    'landscape' => 'Landscape (4:3)',
                    'portrait' => 'Portrait (3:4)',
                    'wide' => 'Wide (16:9)'
                ],
                'default' => 'square'
            ],
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
            'category_font_size' => [
                'label' => 'Category Font Size',
                'type' => 'text',
                'default' => '12px'
            ],
            'icon_color' => [
                'label' => 'Icon Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'icon_size' => [
                'label' => 'Icon Size',
                'type' => 'text',
                'default' => '24px'
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
     * Get sample portfolio items for preview
     */
    private function getSampleItems(): array
    {
        return [
            [
                'title' => 'Brand Identity Design',
                'category' => 'Branding',
                'description' => 'Complete brand identity for a tech startup',
                'color' => '#667eea'
            ],
            [
                'title' => 'E-commerce Website',
                'category' => 'Web Design',
                'description' => 'Full e-commerce solution with custom features',
                'color' => '#f59e0b'
            ],
            [
                'title' => 'Mobile App UI',
                'category' => 'UI/UX',
                'description' => 'Modern mobile application interface design',
                'color' => '#10b981'
            ],
            [
                'title' => 'Marketing Campaign',
                'category' => 'Marketing',
                'description' => 'Multi-channel marketing campaign materials',
                'color' => '#ef4444'
            ],
            [
                'title' => 'Product Photography',
                'category' => 'Photography',
                'description' => 'Professional product photography services',
                'color' => '#8b5cf6'
            ],
            [
                'title' => 'Corporate Video',
                'category' => 'Video',
                'description' => 'Corporate promotional video production',
                'color' => '#06b6d4'
            ],
            [
                'title' => 'Social Media Kit',
                'category' => 'Branding',
                'description' => 'Complete social media branding package',
                'color' => '#ec4899'
            ],
            [
                'title' => 'Landing Page',
                'category' => 'Web Design',
                'description' => 'High-converting landing page design',
                'color' => '#84cc16'
            ],
            [
                'title' => 'Dashboard Design',
                'category' => 'UI/UX',
                'description' => 'Analytics dashboard interface design',
                'color' => '#f97316'
            ],
            [
                'title' => 'Logo Collection',
                'category' => 'Branding',
                'description' => 'Various logo designs for different clients',
                'color' => '#14b8a6'
            ],
            [
                'title' => 'Annual Report',
                'category' => 'Print',
                'description' => 'Corporate annual report design',
                'color' => '#a855f7'
            ],
            [
                'title' => 'App Prototype',
                'category' => 'UI/UX',
                'description' => 'Interactive prototype for fitness app',
                'color' => '#f43f5e'
            ]
        ];
    }

    /**
     * Get aspect ratio CSS value
     */
    private function getAspectRatio(string $ratio): string
    {
        return match($ratio) {
            'square' => '1/1',
            'landscape' => '4/3',
            'portrait' => '3/4',
            'wide' => '16/9',
            default => '1/1'
        };
    }

    public function render(array $settings): string
    {
        // Content fields
        $itemsCount = (int)($settings['items_count'] ?? 6);
        $columns = $settings['columns'] ?? '3';
        $showTitle = ($settings['show_title'] ?? 'yes') === 'yes';
        $showCategory = ($settings['show_category'] ?? 'yes') === 'yes';
        $showDescription = ($settings['show_description'] ?? 'no') === 'yes';
        $hoverEffect = $settings['hover_effect'] ?? 'fade';
        $clickAction = $settings['click_action'] ?? 'lightbox';
        $showFilter = ($settings['show_filter'] ?? 'no') === 'yes';
        $filterStyle = $settings['filter_style'] ?? 'buttons';

        // Design fields
        $gap = $settings['gap'] ?? '16px';
        $borderRadius = $settings['item_border_radius'] ?? '12px';
        $aspectRatio = $settings['image_aspect_ratio'] ?? 'square';
        $overlayColor = $settings['overlay_color'] ?? 'rgba(0,0,0,0.7)';
        $titleColor = $settings['title_color'] ?? '#ffffff';
        $titleSize = $settings['title_font_size'] ?? '18px';
        $categoryColor = $settings['category_color'] ?? 'rgba(255,255,255,0.8)';
        $categorySize = $settings['category_font_size'] ?? '12px';
        $iconColor = $settings['icon_color'] ?? '#ffffff';
        $iconSize = $settings['icon_size'] ?? '24px';
        $filterBg = $settings['filter_bg_color'] ?? '#f3f4f6';
        $filterActiveBg = $settings['filter_active_bg'] ?? '#2563eb';
        $filterText = $settings['filter_text_color'] ?? '#374151';
        $filterActiveText = $settings['filter_active_text'] ?? '#ffffff';

        // Build unique ID for scoped styles
        $uniqueId = 'tb4-portfolio-' . uniqid();

        // Get sample items
        $items = array_slice($this->getSampleItems(), 0, $itemsCount);

        // Get unique categories for filter
        $categories = array_unique(array_column($items, 'category'));

        // Build HTML
        $html = '<div class="tb4-portfolio-preview" id="' . esc_attr($uniqueId) . '">';

        // Filter buttons
        if ($showFilter) {
            $html .= '<div class="tb4-portfolio-filter" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:24px;justify-content:center;">';
            $html .= '<button class="tb4-portfolio-filter-btn active" style="padding:8px 20px;background:' . esc_attr($filterActiveBg) . ';border:none;border-radius:6px;font-size:14px;font-weight:500;color:' . esc_attr($filterActiveText) . ';cursor:pointer;">All</button>';
            foreach ($categories as $cat) {
                $html .= '<button class="tb4-portfolio-filter-btn" style="padding:8px 20px;background:' . esc_attr($filterBg) . ';border:none;border-radius:6px;font-size:14px;font-weight:500;color:' . esc_attr($filterText) . ';cursor:pointer;">' . esc_html($cat) . '</button>';
            }
            $html .= '</div>';
        }

        // Grid
        $gridCols = "repeat($columns, 1fr)";
        $html .= '<div class="tb4-portfolio-grid" style="display:grid;grid-template-columns:' . $gridCols . ';gap:' . esc_attr($gap) . ';">';

        foreach ($items as $item) {
            $aspectRatioValue = $this->getAspectRatio($aspectRatio);

            $html .= '<div class="tb4-portfolio-item" data-category="' . esc_attr($item['category']) . '" style="position:relative;overflow:hidden;border-radius:' . esc_attr($borderRadius) . ';cursor:pointer;aspect-ratio:' . $aspectRatioValue . ';">';

            // Image placeholder with gradient
            $html .= '<div class="tb4-portfolio-image-placeholder" style="width:100%;height:100%;background:linear-gradient(135deg,' . esc_attr($item['color']) . ' 0%,' . esc_attr($item['color']) . '99 100%);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.3);transition:transform 0.4s;">';
            $html .= '<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>';
            $html .= '</div>';

            // Overlay
            $html .= '<div class="tb4-portfolio-overlay" style="position:absolute;inset:0;background:' . esc_attr($overlayColor) . ';display:flex;flex-direction:column;align-items:center;justify-content:center;opacity:0;transition:opacity 0.3s;">';

            // Icon
            $html .= '<div class="tb4-portfolio-icon" style="width:48px;height:48px;border:2px solid rgba(255,255,255,0.3);border-radius:50%;display:flex;align-items:center;justify-content:center;color:' . esc_attr($iconColor) . ';margin-bottom:16px;">';
            if ($clickAction === 'lightbox') {
                $html .= '<svg width="' . esc_attr($iconSize) . '" height="' . esc_attr($iconSize) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/><path d="M11 8v6M8 11h6"/></svg>';
            } else {
                $html .= '<svg width="' . esc_attr($iconSize) . '" height="' . esc_attr($iconSize) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>';
            }
            $html .= '</div>';

            // Title
            if ($showTitle) {
                $html .= '<h4 class="tb4-portfolio-title" style="font-size:' . esc_attr($titleSize) . ';font-weight:600;color:' . esc_attr($titleColor) . ';margin:0 0 4px 0;text-align:center;padding:0 16px;">' . esc_html($item['title']) . '</h4>';
            }

            // Category
            if ($showCategory) {
                $html .= '<span class="tb4-portfolio-category" style="font-size:' . esc_attr($categorySize) . ';color:' . esc_attr($categoryColor) . ';text-transform:uppercase;letter-spacing:1px;">' . esc_html($item['category']) . '</span>';
            }

            // Description
            if ($showDescription && !empty($item['description'])) {
                $html .= '<p class="tb4-portfolio-description" style="font-size:13px;color:rgba(255,255,255,0.7);margin:8px 0 0 0;text-align:center;padding:0 16px;line-height:1.4;">' . esc_html($item['description']) . '</p>';
            }

            $html .= '</div>'; // Close overlay
            $html .= '</div>'; // Close item
        }

        $html .= '</div>'; // Close grid
        $html .= '</div>'; // Close preview

        // Add scoped CSS for hover effects
        $html .= '<style>';
        $html .= '#' . $uniqueId . ' .tb4-portfolio-item:hover .tb4-portfolio-overlay { opacity: 1; }';
        $html .= '#' . $uniqueId . ' .tb4-portfolio-item:hover .tb4-portfolio-image-placeholder { transform: scale(1.1); }';
        $html .= '#' . $uniqueId . ' .tb4-portfolio-filter-btn:hover { opacity: 0.8; }';
        $html .= '</style>';

        return $html;
    }
}
