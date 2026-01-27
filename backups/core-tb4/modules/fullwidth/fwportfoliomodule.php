<?php
namespace Core\TB4\Modules\Fullwidth;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;

/**
 * TB 4.0 Fullwidth Portfolio Module
 *
 * Full-width portfolio grid with filtering, hover effects,
 * multiple layout styles, and lightbox/link options.
 */
class FwPortfolioModule extends Module
{
    protected array $content_fields = [];
    protected array $design_fields_custom = [];

    public function __construct()
    {
        $this->name = 'Fullwidth Portfolio';
        $this->slug = 'fw_portfolio';
        $this->icon = 'layout-grid';
        $this->category = 'fullwidth';

        $this->elements = [
            'main' => '.tb4-fw-portfolio',
            'container' => '.tb4-fw-portfolio-container',
            'header' => '.tb4-fw-portfolio-header',
            'title' => '.tb4-fw-portfolio-title',
            'subtitle' => '.tb4-fw-portfolio-subtitle',
            'filter' => '.tb4-fw-portfolio-filter',
            'filter_btn' => '.tb4-fw-portfolio-filter-btn',
            'grid' => '.tb4-fw-portfolio-grid',
            'item' => '.tb4-fw-portfolio-item',
            'overlay' => '.tb4-fw-portfolio-item-overlay',
            'item_title' => '.tb4-fw-portfolio-item-title',
            'item_category' => '.tb4-fw-portfolio-item-category',
            'load_more' => '.tb4-fw-portfolio-load-more'
        ];

        // Content fields
        $this->content_fields = [
            'items_count' => [
                'type' => 'select',
                'label' => 'Number of Items',
                'options' => [
                    '4' => '4 Items',
                    '6' => '6 Items',
                    '8' => '8 Items',
                    '9' => '9 Items',
                    '12' => '12 Items'
                ],
                'default' => '8'
            ],
            'columns' => [
                'type' => 'select',
                'label' => 'Columns',
                'options' => [
                    '2' => '2 Columns',
                    '3' => '3 Columns',
                    '4' => '4 Columns',
                    '5' => '5 Columns'
                ],
                'default' => '4'
            ],
            'show_filter' => [
                'type' => 'select',
                'label' => 'Show Filter',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'default' => 'yes'
            ],
            'filter_all_text' => [
                'type' => 'text',
                'label' => 'Filter "All" Text',
                'default' => 'All'
            ],
            'show_title' => [
                'type' => 'select',
                'label' => 'Show Title',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'default' => 'yes'
            ],
            'show_category' => [
                'type' => 'select',
                'label' => 'Show Category',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'default' => 'yes'
            ],
            'hover_effect' => [
                'type' => 'select',
                'label' => 'Hover Effect',
                'options' => [
                    'fade' => 'Fade Overlay',
                    'slide-up' => 'Slide Up',
                    'zoom' => 'Zoom',
                    'none' => 'None'
                ],
                'default' => 'fade'
            ],
            'click_action' => [
                'type' => 'select',
                'label' => 'Click Action',
                'options' => [
                    'lightbox' => 'Open Lightbox',
                    'link' => 'Go to Project',
                    'none' => 'None'
                ],
                'default' => 'lightbox'
            ],
            'section_title' => [
                'type' => 'text',
                'label' => 'Section Title',
                'default' => ''
            ],
            'section_subtitle' => [
                'type' => 'text',
                'label' => 'Section Subtitle',
                'default' => ''
            ],
            'show_load_more' => [
                'type' => 'select',
                'label' => 'Show Load More Button',
                'options' => ['no' => 'No', 'yes' => 'Yes'],
                'default' => 'no'
            ],
            'load_more_text' => [
                'type' => 'text',
                'label' => 'Load More Text',
                'default' => 'Load More'
            ]
        ];

        // Design fields
        $this->design_fields_custom = [
            'layout_style' => [
                'type' => 'select',
                'label' => 'Layout Style',
                'options' => [
                    'grid' => 'Grid',
                    'masonry' => 'Masonry',
                    'justified' => 'Justified'
                ],
                'default' => 'grid'
            ],
            'gap' => [
                'type' => 'text',
                'label' => 'Gap Between Items',
                'default' => '0px'
            ],
            'item_aspect_ratio' => [
                'type' => 'select',
                'label' => 'Item Aspect Ratio',
                'options' => [
                    'square' => 'Square (1:1)',
                    'landscape' => 'Landscape (4:3)',
                    'portrait' => 'Portrait (3:4)',
                    'wide' => 'Wide (16:9)'
                ],
                'default' => 'square'
            ],
            'background_color' => [
                'type' => 'color',
                'label' => 'Section Background',
                'default' => '#ffffff'
            ],
            'section_padding' => [
                'type' => 'text',
                'label' => 'Section Padding',
                'default' => '80px 0'
            ],
            'title_color' => [
                'type' => 'color',
                'label' => 'Section Title Color',
                'default' => '#111827'
            ],
            'title_font_size' => [
                'type' => 'text',
                'label' => 'Section Title Size',
                'default' => '36px'
            ],
            'subtitle_color' => [
                'type' => 'color',
                'label' => 'Section Subtitle Color',
                'default' => '#6b7280'
            ],
            'subtitle_font_size' => [
                'type' => 'text',
                'label' => 'Section Subtitle Size',
                'default' => '18px'
            ],
            'filter_bg_color' => [
                'type' => 'color',
                'label' => 'Filter Background',
                'default' => 'transparent'
            ],
            'filter_text_color' => [
                'type' => 'color',
                'label' => 'Filter Text Color',
                'default' => '#6b7280'
            ],
            'filter_active_bg' => [
                'type' => 'color',
                'label' => 'Filter Active Background',
                'default' => '#2563eb'
            ],
            'filter_active_text' => [
                'type' => 'color',
                'label' => 'Filter Active Text',
                'default' => '#ffffff'
            ],
            'filter_border_radius' => [
                'type' => 'text',
                'label' => 'Filter Border Radius',
                'default' => '24px'
            ],
            'filter_margin_bottom' => [
                'type' => 'text',
                'label' => 'Filter Margin Bottom',
                'default' => '48px'
            ],
            'overlay_color' => [
                'type' => 'color',
                'label' => 'Overlay Color',
                'default' => 'rgba(0,0,0,0.7)'
            ],
            'item_title_color' => [
                'type' => 'color',
                'label' => 'Item Title Color',
                'default' => '#ffffff'
            ],
            'item_title_size' => [
                'type' => 'text',
                'label' => 'Item Title Size',
                'default' => '18px'
            ],
            'item_category_color' => [
                'type' => 'color',
                'label' => 'Item Category Color',
                'default' => 'rgba(255,255,255,0.8)'
            ],
            'item_category_size' => [
                'type' => 'text',
                'label' => 'Item Category Size',
                'default' => '14px'
            ],
            'icon_color' => [
                'type' => 'color',
                'label' => 'Icon Color',
                'default' => '#ffffff'
            ],
            'icon_size' => [
                'type' => 'text',
                'label' => 'Icon Size',
                'default' => '24px'
            ],
            'load_more_bg' => [
                'type' => 'color',
                'label' => 'Load More Background',
                'default' => '#2563eb'
            ],
            'load_more_color' => [
                'type' => 'color',
                'label' => 'Load More Text Color',
                'default' => '#ffffff'
            ],
            'load_more_radius' => [
                'type' => 'text',
                'label' => 'Load More Radius',
                'default' => '8px'
            ]
        ];

        // Advanced fields
        $this->advanced_fields = array_merge($this->advanced_fields, [
            'css_id' => [
                'type' => 'text',
                'label' => 'CSS ID',
                'default' => ''
            ],
            'css_class' => [
                'type' => 'text',
                'label' => 'CSS Class',
                'default' => ''
            ],
            'custom_css' => [
                'type' => 'textarea',
                'label' => 'Custom CSS',
                'default' => ''
            ]
        ]);
    }

    public function get_content_fields(): array
    {
        return $this->content_fields;
    }

    public function get_design_fields(): array
    {
        return array_merge(parent::get_design_fields(), $this->design_fields_custom);
    }

    public function render(array $attrs): string
    {
        // Content fields
        $itemsCount = (int)($attrs['items_count'] ?? 8);
        $columns = (int)($attrs['columns'] ?? 4);
        $showFilter = ($attrs['show_filter'] ?? 'yes') === 'yes';
        $filterAllText = $attrs['filter_all_text'] ?? 'All';
        $showTitle = ($attrs['show_title'] ?? 'yes') === 'yes';
        $showCategory = ($attrs['show_category'] ?? 'yes') === 'yes';
        $hoverEffect = $attrs['hover_effect'] ?? 'fade';
        $clickAction = $attrs['click_action'] ?? 'lightbox';
        $sectionTitle = $attrs['section_title'] ?? '';
        $sectionSubtitle = $attrs['section_subtitle'] ?? '';
        $showLoadMore = ($attrs['show_load_more'] ?? 'no') === 'yes';
        $loadMoreText = $attrs['load_more_text'] ?? 'Load More';

        // Design fields
        $layoutStyle = $attrs['layout_style'] ?? 'grid';
        $gap = $attrs['gap'] ?? '0px';
        $aspectRatio = $attrs['item_aspect_ratio'] ?? 'square';
        $bgColor = $attrs['background_color'] ?? '#ffffff';
        $sectionPadding = $attrs['section_padding'] ?? '80px 0';
        $titleColor = $attrs['title_color'] ?? '#111827';
        $titleFontSize = $attrs['title_font_size'] ?? '36px';
        $subtitleColor = $attrs['subtitle_color'] ?? '#6b7280';
        $subtitleFontSize = $attrs['subtitle_font_size'] ?? '18px';
        $filterBgColor = $attrs['filter_bg_color'] ?? 'transparent';
        $filterTextColor = $attrs['filter_text_color'] ?? '#6b7280';
        $filterActiveBg = $attrs['filter_active_bg'] ?? '#2563eb';
        $filterActiveText = $attrs['filter_active_text'] ?? '#ffffff';
        $filterBorderRadius = $attrs['filter_border_radius'] ?? '24px';
        $filterMarginBottom = $attrs['filter_margin_bottom'] ?? '48px';
        $overlayColor = $attrs['overlay_color'] ?? 'rgba(0,0,0,0.7)';
        $itemTitleColor = $attrs['item_title_color'] ?? '#ffffff';
        $itemTitleSize = $attrs['item_title_size'] ?? '18px';
        $itemCategoryColor = $attrs['item_category_color'] ?? 'rgba(255,255,255,0.8)';
        $itemCategorySize = $attrs['item_category_size'] ?? '14px';
        $iconColor = $attrs['icon_color'] ?? '#ffffff';
        $iconSize = $attrs['icon_size'] ?? '24px';
        $loadMoreBg = $attrs['load_more_bg'] ?? '#2563eb';
        $loadMoreColor = $attrs['load_more_color'] ?? '#ffffff';
        $loadMoreRadius = $attrs['load_more_radius'] ?? '8px';

        // Advanced fields
        $cssId = $attrs['css_id'] ?? '';
        $cssClass = $attrs['css_class'] ?? '';

        // Aspect ratio padding
        $paddingBottom = '100%';
        if ($aspectRatio === 'landscape') {
            $paddingBottom = '75%';
        } elseif ($aspectRatio === 'portrait') {
            $paddingBottom = '133%';
        } elseif ($aspectRatio === 'wide') {
            $paddingBottom = '56.25%';
        }

        // Sample categories
        $categories = ['Web Design', 'Branding', 'Photography', 'UI/UX'];

        // Sample gradients for placeholders
        $gradients = [
            'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
            'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
            'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
            'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
            'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
            'linear-gradient(135deg, #d299c2 0%, #fef9d7 100%)',
            'linear-gradient(135deg, #89f7fe 0%, #66a6ff 100%)',
            'linear-gradient(135deg, #fddb92 0%, #d1fdff 100%)',
            'linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%)',
            'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)',
            'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)'
        ];

        // Build ID/Class attributes
        $idAttr = $cssId ? ' id="' . htmlspecialchars($cssId, ENT_QUOTES, 'UTF-8') . '"' : '';
        $classAttr = 'tb4-fw-portfolio' . ($cssClass ? ' ' . htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8') : '');
        $classAttr .= ' tb4-fw-portfolio-' . htmlspecialchars($hoverEffect, ENT_QUOTES, 'UTF-8');

        // Build HTML
        $html = '<section' . $idAttr . ' class="' . $classAttr . '" style="width:100%;background:' . htmlspecialchars($bgColor, ENT_QUOTES, 'UTF-8') . ';padding:' . htmlspecialchars($sectionPadding, ENT_QUOTES, 'UTF-8') . ';">';
        $html .= '<div class="tb4-fw-portfolio-container" style="width:100%;">';

        // Section header
        if ($sectionTitle || $sectionSubtitle) {
            $html .= '<div class="tb4-fw-portfolio-header" style="text-align:center;margin-bottom:32px;padding:0 24px;">';
            if ($sectionTitle) {
                $html .= '<h2 class="tb4-fw-portfolio-title" style="font-size:' . htmlspecialchars($titleFontSize, ENT_QUOTES, 'UTF-8') . ';font-weight:700;color:' . htmlspecialchars($titleColor, ENT_QUOTES, 'UTF-8') . ';margin:0 0 12px 0;">' . htmlspecialchars($sectionTitle, ENT_QUOTES, 'UTF-8') . '</h2>';
            }
            if ($sectionSubtitle) {
                $html .= '<p class="tb4-fw-portfolio-subtitle" style="font-size:' . htmlspecialchars($subtitleFontSize, ENT_QUOTES, 'UTF-8') . ';color:' . htmlspecialchars($subtitleColor, ENT_QUOTES, 'UTF-8') . ';margin:0;">' . htmlspecialchars($sectionSubtitle, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            $html .= '</div>';
        }

        // Filter
        if ($showFilter) {
            $html .= '<div class="tb4-fw-portfolio-filter" style="display:flex;justify-content:center;gap:8px;flex-wrap:wrap;margin-bottom:' . htmlspecialchars($filterMarginBottom, ENT_QUOTES, 'UTF-8') . ';padding:0 24px;">';
            $html .= '<button class="tb4-fw-portfolio-filter-btn active" data-filter="*" style="padding:10px 24px;background:' . htmlspecialchars($filterActiveBg, ENT_QUOTES, 'UTF-8') . ';border:none;font-size:14px;font-weight:500;color:' . htmlspecialchars($filterActiveText, ENT_QUOTES, 'UTF-8') . ';cursor:pointer;border-radius:' . htmlspecialchars($filterBorderRadius, ENT_QUOTES, 'UTF-8') . ';transition:all 0.2s;">' . htmlspecialchars($filterAllText, ENT_QUOTES, 'UTF-8') . '</button>';
            foreach ($categories as $cat) {
                $catSlug = strtolower(str_replace(' ', '-', $cat));
                $html .= '<button class="tb4-fw-portfolio-filter-btn" data-filter=".' . htmlspecialchars($catSlug, ENT_QUOTES, 'UTF-8') . '" style="padding:10px 24px;background:' . htmlspecialchars($filterBgColor, ENT_QUOTES, 'UTF-8') . ';border:none;font-size:14px;font-weight:500;color:' . htmlspecialchars($filterTextColor, ENT_QUOTES, 'UTF-8') . ';cursor:pointer;border-radius:' . htmlspecialchars($filterBorderRadius, ENT_QUOTES, 'UTF-8') . ';transition:all 0.2s;">' . htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') . '</button>';
            }
            $html .= '</div>';
        }

        // Grid
        $html .= '<div class="tb4-fw-portfolio-grid" style="display:grid;grid-template-columns:repeat(' . $columns . ',1fr);gap:' . htmlspecialchars($gap, ENT_QUOTES, 'UTF-8') . ';width:100%;">';

        // Portfolio items
        for ($i = 0; $i < $itemsCount; $i++) {
            $category = $categories[$i % count($categories)];
            $catSlug = strtolower(str_replace(' ', '-', $category));
            $gradient = $gradients[$i % count($gradients)];

            $html .= '<div class="tb4-fw-portfolio-item ' . htmlspecialchars($catSlug, ENT_QUOTES, 'UTF-8') . '" style="position:relative;overflow:hidden;cursor:pointer;">';

            // Image placeholder with aspect ratio
            $html .= '<div style="position:relative;padding-bottom:' . $paddingBottom . ';background:' . $gradient . ';">';
            $html .= '<div class="tb4-fw-portfolio-item-image" style="position:absolute;inset:0;transition:transform 0.4s ease;"></div>';

            // Overlay
            $html .= '<div class="tb4-fw-portfolio-item-overlay" style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;background:' . htmlspecialchars($overlayColor, ENT_QUOTES, 'UTF-8') . ';opacity:0;transition:all 0.3s ease;padding:20px;">';

            // Icon
            if ($clickAction === 'lightbox') {
                $html .= '<div class="tb4-fw-portfolio-item-icon" style="margin-bottom:16px;color:' . htmlspecialchars($iconColor, ENT_QUOTES, 'UTF-8') . ';"><svg width="' . htmlspecialchars($iconSize, ENT_QUOTES, 'UTF-8') . '" height="' . htmlspecialchars($iconSize, ENT_QUOTES, 'UTF-8') . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg></div>';
            } elseif ($clickAction === 'link') {
                $html .= '<div class="tb4-fw-portfolio-item-icon" style="margin-bottom:16px;color:' . htmlspecialchars($iconColor, ENT_QUOTES, 'UTF-8') . ';"><svg width="' . htmlspecialchars($iconSize, ENT_QUOTES, 'UTF-8') . '" height="' . htmlspecialchars($iconSize, ENT_QUOTES, 'UTF-8') . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg></div>';
            }

            // Title & Category
            if ($showTitle) {
                $html .= '<h3 class="tb4-fw-portfolio-item-title" style="font-size:' . htmlspecialchars($itemTitleSize, ENT_QUOTES, 'UTF-8') . ';font-weight:600;color:' . htmlspecialchars($itemTitleColor, ENT_QUOTES, 'UTF-8') . ';margin:0 0 8px 0;">Project ' . ($i + 1) . '</h3>';
            }
            if ($showCategory) {
                $html .= '<p class="tb4-fw-portfolio-item-category" style="font-size:' . htmlspecialchars($itemCategorySize, ENT_QUOTES, 'UTF-8') . ';color:' . htmlspecialchars($itemCategoryColor, ENT_QUOTES, 'UTF-8') . ';margin:0;">' . htmlspecialchars($category, ENT_QUOTES, 'UTF-8') . '</p>';
            }

            $html .= '</div></div></div>';
        }

        $html .= '</div>';

        // Load more button
        if ($showLoadMore) {
            $html .= '<div class="tb4-fw-portfolio-load-more" style="text-align:center;margin-top:48px;">';
            $html .= '<button style="padding:14px 32px;background:' . htmlspecialchars($loadMoreBg, ENT_QUOTES, 'UTF-8') . ';color:' . htmlspecialchars($loadMoreColor, ENT_QUOTES, 'UTF-8') . ';border:none;border-radius:' . htmlspecialchars($loadMoreRadius, ENT_QUOTES, 'UTF-8') . ';font-size:15px;font-weight:600;cursor:pointer;transition:all 0.2s;">' . htmlspecialchars($loadMoreText, ENT_QUOTES, 'UTF-8') . '</button>';
            $html .= '</div>';
        }

        $html .= '</div></section>';

        // Hover CSS
        $html .= '<style>';
        $html .= '.tb4-fw-portfolio-item:hover .tb4-fw-portfolio-item-overlay{opacity:1!important}';
        if ($hoverEffect === 'zoom') {
            $html .= '.tb4-fw-portfolio-item:hover .tb4-fw-portfolio-item-image{transform:scale(1.1)!important}';
        }
        $html .= '.tb4-fw-portfolio-filter-btn:hover{background:' . htmlspecialchars($filterActiveBg, ENT_QUOTES, 'UTF-8') . '!important;color:' . htmlspecialchars($filterActiveText, ENT_QUOTES, 'UTF-8') . '!important}';
        $html .= '</style>';

        return $html;
    }
}
