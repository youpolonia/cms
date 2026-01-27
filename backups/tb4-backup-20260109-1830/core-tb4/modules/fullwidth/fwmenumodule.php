<?php
namespace Core\TB4\Modules\Fullwidth;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Fullwidth Menu Module
 *
 * Full-width responsive navigation menu with logo, menu items,
 * CTA button, search icon, and mobile hamburger support.
 */
class FwMenuModule extends Module
{
    protected array $content_fields = [];
    protected array $design_fields_custom = [];

    public function __construct()
    {
        $this->name = 'Fullwidth Menu';
        $this->slug = 'fw_menu';
        $this->icon = 'menu';
        $this->category = 'fullwidth';

        $this->elements = [
            'main' => '.tb4-fw-menu',
            'container' => '.tb4-fw-menu-container',
            'logo' => '.tb4-fw-menu-logo',
            'nav' => '.tb4-fw-menu-nav',
            'item' => '.tb4-fw-menu-item',
            'actions' => '.tb4-fw-menu-actions',
            'cta' => '.tb4-fw-menu-cta',
            'search' => '.tb4-fw-menu-search',
            'hamburger' => '.tb4-fw-menu-hamburger'
        ];

        // Content fields
        $this->content_fields = [
            'logo_image' => [
                'type' => 'text',
                'label' => 'Logo Image URL',
                'default' => ''
            ],
            'logo_text' => [
                'type' => 'text',
                'label' => 'Logo Text (if no image)',
                'default' => 'YourBrand'
            ],
            'logo_url' => [
                'type' => 'text',
                'label' => 'Logo Link URL',
                'default' => '/'
            ],
            'menu_item1_text' => [
                'type' => 'text',
                'label' => 'Menu Item 1 Text',
                'default' => 'Home'
            ],
            'menu_item1_url' => [
                'type' => 'text',
                'label' => 'Menu Item 1 URL',
                'default' => '/'
            ],
            'menu_item2_text' => [
                'type' => 'text',
                'label' => 'Menu Item 2 Text',
                'default' => 'About'
            ],
            'menu_item2_url' => [
                'type' => 'text',
                'label' => 'Menu Item 2 URL',
                'default' => '/about'
            ],
            'menu_item3_text' => [
                'type' => 'text',
                'label' => 'Menu Item 3 Text',
                'default' => 'Services'
            ],
            'menu_item3_url' => [
                'type' => 'text',
                'label' => 'Menu Item 3 URL',
                'default' => '/services'
            ],
            'menu_item4_text' => [
                'type' => 'text',
                'label' => 'Menu Item 4 Text',
                'default' => 'Portfolio'
            ],
            'menu_item4_url' => [
                'type' => 'text',
                'label' => 'Menu Item 4 URL',
                'default' => '/portfolio'
            ],
            'menu_item5_text' => [
                'type' => 'text',
                'label' => 'Menu Item 5 Text',
                'default' => 'Contact'
            ],
            'menu_item5_url' => [
                'type' => 'text',
                'label' => 'Menu Item 5 URL',
                'default' => '/contact'
            ],
            'menu_item6_text' => [
                'type' => 'text',
                'label' => 'Menu Item 6 Text',
                'default' => ''
            ],
            'menu_item6_url' => [
                'type' => 'text',
                'label' => 'Menu Item 6 URL',
                'default' => ''
            ],
            'show_cta_button' => [
                'type' => 'select',
                'label' => 'Show CTA Button',
                'options' => ['no' => 'No', 'yes' => 'Yes'],
                'default' => 'yes'
            ],
            'cta_text' => [
                'type' => 'text',
                'label' => 'CTA Button Text',
                'default' => 'Get Started'
            ],
            'cta_url' => [
                'type' => 'text',
                'label' => 'CTA Button URL',
                'default' => '#'
            ],
            'show_search' => [
                'type' => 'select',
                'label' => 'Show Search Icon',
                'options' => ['no' => 'No', 'yes' => 'Yes'],
                'default' => 'no'
            ],
            'sticky_menu' => [
                'type' => 'select',
                'label' => 'Sticky Menu',
                'options' => ['no' => 'No', 'yes' => 'Yes'],
                'default' => 'no'
            ]
        ];

        // Design fields
        $this->design_fields_custom = [
            'menu_layout' => [
                'type' => 'select',
                'label' => 'Menu Layout',
                'options' => [
                    'logo-left' => 'Logo Left, Menu Right',
                    'logo-center' => 'Logo Center',
                    'menu-center' => 'Menu Center'
                ],
                'default' => 'logo-left'
            ],
            'background_color' => [
                'type' => 'color',
                'label' => 'Background Color',
                'default' => '#ffffff'
            ],
            'background_transparent' => [
                'type' => 'select',
                'label' => 'Transparent Background',
                'options' => ['no' => 'No', 'yes' => 'Yes'],
                'default' => 'no'
            ],
            'border_bottom' => [
                'type' => 'select',
                'label' => 'Border Bottom',
                'options' => ['none' => 'None', 'solid' => 'Solid Line', 'shadow' => 'Shadow'],
                'default' => 'solid'
            ],
            'border_color' => [
                'type' => 'color',
                'label' => 'Border Color',
                'default' => '#e5e7eb'
            ],
            'menu_padding' => [
                'type' => 'text',
                'label' => 'Menu Padding',
                'default' => '16px 0'
            ],
            'content_width' => [
                'type' => 'select',
                'label' => 'Content Width',
                'options' => [
                    'full' => 'Full Width',
                    'contained' => 'Contained (1200px)',
                    'narrow' => 'Narrow (960px)'
                ],
                'default' => 'contained'
            ],
            'logo_max_height' => [
                'type' => 'text',
                'label' => 'Logo Max Height',
                'default' => '48px'
            ],
            'logo_text_color' => [
                'type' => 'color',
                'label' => 'Logo Text Color',
                'default' => '#111827'
            ],
            'logo_text_size' => [
                'type' => 'text',
                'label' => 'Logo Text Size',
                'default' => '24px'
            ],
            'logo_font_weight' => [
                'type' => 'select',
                'label' => 'Logo Font Weight',
                'options' => [
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi Bold',
                    '700' => 'Bold',
                    '800' => 'Extra Bold'
                ],
                'default' => '700'
            ],
            'menu_item_color' => [
                'type' => 'color',
                'label' => 'Menu Item Color',
                'default' => '#374151'
            ],
            'menu_item_hover_color' => [
                'type' => 'color',
                'label' => 'Menu Item Hover Color',
                'default' => '#2563eb'
            ],
            'menu_item_font_size' => [
                'type' => 'text',
                'label' => 'Menu Item Font Size',
                'default' => '15px'
            ],
            'menu_item_font_weight' => [
                'type' => 'select',
                'label' => 'Menu Item Font Weight',
                'options' => [
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi Bold'
                ],
                'default' => '500'
            ],
            'menu_item_spacing' => [
                'type' => 'text',
                'label' => 'Menu Item Spacing',
                'default' => '32px'
            ],
            'menu_item_active_style' => [
                'type' => 'select',
                'label' => 'Active Item Style',
                'options' => [
                    'color' => 'Color Only',
                    'underline' => 'Underline',
                    'background' => 'Background'
                ],
                'default' => 'color'
            ],
            'cta_bg_color' => [
                'type' => 'color',
                'label' => 'CTA Background',
                'default' => '#2563eb'
            ],
            'cta_text_color' => [
                'type' => 'color',
                'label' => 'CTA Text Color',
                'default' => '#ffffff'
            ],
            'cta_border_radius' => [
                'type' => 'text',
                'label' => 'CTA Border Radius',
                'default' => '8px'
            ],
            'cta_padding' => [
                'type' => 'text',
                'label' => 'CTA Padding',
                'default' => '10px 20px'
            ],
            'search_icon_color' => [
                'type' => 'color',
                'label' => 'Search Icon Color',
                'default' => '#6b7280'
            ],
            'mobile_breakpoint' => [
                'type' => 'select',
                'label' => 'Mobile Breakpoint',
                'options' => [
                    '768' => 'Tablet (768px)',
                    '1024' => 'Desktop (1024px)'
                ],
                'default' => '1024'
            ],
            'hamburger_color' => [
                'type' => 'color',
                'label' => 'Hamburger Icon Color',
                'default' => '#374151'
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
        $logoImage = $attrs['logo_image'] ?? '';
        $logoText = $attrs['logo_text'] ?? 'YourBrand';
        $logoUrl = $attrs['logo_url'] ?? '/';
        $showCtaButton = ($attrs['show_cta_button'] ?? 'yes') === 'yes';
        $ctaText = $attrs['cta_text'] ?? 'Get Started';
        $ctaUrl = $attrs['cta_url'] ?? '#';
        $showSearch = ($attrs['show_search'] ?? 'no') === 'yes';
        $stickyMenu = ($attrs['sticky_menu'] ?? 'no') === 'yes';

        // Design fields
        $menuLayout = $attrs['menu_layout'] ?? 'logo-left';
        $bgColor = $attrs['background_color'] ?? '#ffffff';
        $bgTransparent = ($attrs['background_transparent'] ?? 'no') === 'yes';
        $borderBottom = $attrs['border_bottom'] ?? 'solid';
        $borderColor = $attrs['border_color'] ?? '#e5e7eb';
        $menuPadding = $attrs['menu_padding'] ?? '16px 0';
        $contentWidth = $attrs['content_width'] ?? 'contained';
        $logoMaxHeight = $attrs['logo_max_height'] ?? '48px';
        $logoTextColor = $attrs['logo_text_color'] ?? '#111827';
        $logoTextSize = $attrs['logo_text_size'] ?? '24px';
        $logoFontWeight = $attrs['logo_font_weight'] ?? '700';
        $menuItemColor = $attrs['menu_item_color'] ?? '#374151';
        $menuItemHoverColor = $attrs['menu_item_hover_color'] ?? '#2563eb';
        $menuItemFontSize = $attrs['menu_item_font_size'] ?? '15px';
        $menuItemFontWeight = $attrs['menu_item_font_weight'] ?? '500';
        $menuItemSpacing = $attrs['menu_item_spacing'] ?? '32px';
        $menuItemActiveStyle = $attrs['menu_item_active_style'] ?? 'color';
        $ctaBgColor = $attrs['cta_bg_color'] ?? '#2563eb';
        $ctaTextColor = $attrs['cta_text_color'] ?? '#ffffff';
        $ctaBorderRadius = $attrs['cta_border_radius'] ?? '8px';
        $ctaPadding = $attrs['cta_padding'] ?? '10px 20px';
        $searchIconColor = $attrs['search_icon_color'] ?? '#6b7280';
        $mobileBreakpoint = $attrs['mobile_breakpoint'] ?? '1024';
        $hamburgerColor = $attrs['hamburger_color'] ?? '#374151';

        // Advanced fields
        $cssId = $attrs['css_id'] ?? '';
        $cssClass = $attrs['css_class'] ?? '';

        // Build menu items
        $menuItems = [];
        for ($i = 1; $i <= 6; $i++) {
            $text = $attrs["menu_item{$i}_text"] ?? '';
            $url = $attrs["menu_item{$i}_url"] ?? '#';
            if (!empty($text)) {
                $menuItems[] = ['text' => $text, 'url' => $url];
            }
        }

        // Background style
        $bgStyle = $bgTransparent ? 'transparent' : $bgColor;

        // Border/shadow style
        $borderStyle = '';
        if ($borderBottom === 'solid') {
            $borderStyle = 'border-bottom:1px solid ' . esc_attr($borderColor) . ';';
        } elseif ($borderBottom === 'shadow') {
            $borderStyle = 'box-shadow:0 2px 10px rgba(0,0,0,0.08);';
        }

        // Content width
        $maxWidth = 'none';
        if ($contentWidth === 'contained') {
            $maxWidth = '1200px';
        } elseif ($contentWidth === 'narrow') {
            $maxWidth = '960px';
        }

        // Container ID/Class
        $idAttr = $cssId ? ' id="' . esc_attr($cssId) . '"' : '';
        $classAttr = 'tb4-fw-menu' . ($cssClass ? ' ' . esc_attr($cssClass) : '');
        if ($stickyMenu) {
            $classAttr .= ' tb4-fw-menu-sticky';
        }
        $classAttr .= ' tb4-fw-menu-layout-' . esc_attr($menuLayout);

        // Build HTML
        $html = '<nav' . $idAttr . ' class="' . $classAttr . '" style="width:100%;background:' . esc_attr($bgStyle) . ';' . $borderStyle . ($stickyMenu ? 'position:sticky;top:0;z-index:1000;' : '') . '">';
        $html .= '<div class="tb4-fw-menu-container" style="display:flex;align-items:center;justify-content:space-between;max-width:' . esc_attr($maxWidth) . ';margin:0 auto;padding:' . esc_attr($menuPadding) . ';padding-left:24px;padding-right:24px;box-sizing:border-box;position:relative;">';

        // Logo
        $html .= '<div class="tb4-fw-menu-logo">';
        $html .= '<a href="' . esc_attr($logoUrl) . '" style="display:flex;align-items:center;text-decoration:none;">';
        if ($logoImage) {
            $html .= '<img src="' . esc_attr($logoImage) . '" alt="Logo" style="max-height:' . esc_attr($logoMaxHeight) . ';width:auto;">';
        } else {
            $html .= '<span class="tb4-fw-menu-logo-text" style="font-size:' . esc_attr($logoTextSize) . ';font-weight:' . esc_attr($logoFontWeight) . ';color:' . esc_attr($logoTextColor) . ';">' . esc_html($logoText) . '</span>';
        }
        $html .= '</a></div>';

        // Navigation
        $navStyle = 'display:flex;align-items:center;gap:' . esc_attr($menuItemSpacing) . ';list-style:none;margin:0;padding:0;';
        if ($menuLayout === 'menu-center') {
            $navStyle .= 'position:absolute;left:50%;transform:translateX(-50%);';
        }

        $html .= '<ul class="tb4-fw-menu-nav" style="' . $navStyle . '">';
        foreach ($menuItems as $index => $item) {
            $isActive = $index === 0; // First item is active by default
            $itemStyle = 'font-size:' . esc_attr($menuItemFontSize) . ';font-weight:' . esc_attr($menuItemFontWeight) . ';color:' . ($isActive ? esc_attr($menuItemHoverColor) : esc_attr($menuItemColor)) . ';text-decoration:none;padding:8px 0;transition:color 0.2s;position:relative;';

            if ($isActive && $menuItemActiveStyle === 'underline') {
                $itemStyle .= 'border-bottom:2px solid ' . esc_attr($menuItemHoverColor) . ';';
            } elseif ($isActive && $menuItemActiveStyle === 'background') {
                $itemStyle .= 'background:' . esc_attr($menuItemHoverColor) . '20;padding:8px 16px;border-radius:6px;';
            }

            $html .= '<li class="tb4-fw-menu-item"><a href="' . esc_attr($item['url']) . '" style="' . $itemStyle . '">' . esc_html($item['text']) . '</a></li>';
        }
        $html .= '</ul>';

        // Actions
        $html .= '<div class="tb4-fw-menu-actions" style="display:flex;align-items:center;gap:16px;">';

        // Search icon
        if ($showSearch) {
            $html .= '<button class="tb4-fw-menu-search" style="width:40px;height:40px;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;color:' . esc_attr($searchIconColor) . ';border-radius:8px;transition:all 0.2s;">';
            $html .= '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>';
            $html .= '</button>';
        }

        // CTA button
        if ($showCtaButton) {
            $html .= '<a href="' . esc_attr($ctaUrl) . '" class="tb4-fw-menu-cta" style="display:inline-block;padding:' . esc_attr($ctaPadding) . ';background:' . esc_attr($ctaBgColor) . ';color:' . esc_attr($ctaTextColor) . ';text-decoration:none;border-radius:' . esc_attr($ctaBorderRadius) . ';font-size:14px;font-weight:600;transition:all 0.2s;">' . esc_html($ctaText) . '</a>';
        }

        $html .= '</div>';

        // Hamburger menu (hidden by default, shown on mobile via CSS)
        $html .= '<button class="tb4-fw-menu-hamburger" style="display:none;flex-direction:column;justify-content:center;gap:5px;width:32px;height:32px;background:transparent;border:none;cursor:pointer;padding:4px;">';
        $html .= '<span style="display:block;width:100%;height:2px;background:' . esc_attr($hamburgerColor) . ';border-radius:1px;"></span>';
        $html .= '<span style="display:block;width:100%;height:2px;background:' . esc_attr($hamburgerColor) . ';border-radius:1px;"></span>';
        $html .= '<span style="display:block;width:100%;height:2px;background:' . esc_attr($hamburgerColor) . ';border-radius:1px;"></span>';
        $html .= '</button>';

        $html .= '</div></nav>';

        // Add responsive CSS
        $html .= '<style>';
        $html .= '@media (max-width: ' . esc_attr($mobileBreakpoint) . 'px) {';
        $html .= '.tb4-fw-menu-nav { display: none !important; }';
        $html .= '.tb4-fw-menu-actions { display: none !important; }';
        $html .= '.tb4-fw-menu-hamburger { display: flex !important; }';
        $html .= '}';
        $html .= '</style>';

        return $html;
    }
}
