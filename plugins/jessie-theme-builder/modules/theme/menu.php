<?php
/**
 * Menu Module (with Logo)
 * Navigation menu with optional logo for headers
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Menu extends JTB_Element
{
    public string $slug = 'menu';
    public string $name = 'Menu';
    public string $icon = 'menu';
    public string $category = 'theme';

    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;

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
            'logo' => [
                'label' => 'Logo',
                'type' => 'upload',
                'description' => 'Upload your site logo',
                'default' => ''
            ],
            'logo_url' => [
                'label' => 'Logo Link URL',
                'type' => 'text',
                'description' => 'URL the logo links to (default: homepage)',
                'default' => '/'
            ],
            'logo_alt' => [
                'label' => 'Logo Alt Text',
                'type' => 'text',
                'description' => 'Alternative text for the logo',
                'default' => 'Site Logo'
            ],
            'menu_style' => [
                'label' => 'Menu Style',
                'type' => 'select',
                'options' => [
                    'left_aligned' => 'Logo Left, Menu Right',
                    'centered_logo' => 'Centered Logo',
                    'inline_centered' => 'Inline Centered',
                    'stacked' => 'Stacked'
                ],
                'default' => 'left_aligned'
            ],
            'show_cart_icon' => [
                'label' => 'Show Cart Icon',
                'type' => 'toggle',
                'description' => 'Display shopping cart icon',
                'default' => false
            ],
            'show_search_icon' => [
                'label' => 'Show Search Icon',
                'type' => 'toggle',
                'description' => 'Display search icon',
                'default' => true
            ],
            'logo_width' => [
                'label' => 'Logo Width',
                'type' => 'range',
                'min' => 50,
                'max' => 400,
                'step' => 1,
                'default' => 150,
                'unit' => 'px',
                'responsive' => true
            ],
            'menu_text_color' => [
                'label' => 'Menu Text Color',
                'type' => 'color',
                'default' => '#333333',
                'hover' => true
            ],
            'icon_color' => [
                'label' => 'Icon Color',
                'type' => 'color',
                'default' => '#333333',
                'hover' => true
            ],
            'menu_font_size' => [
                'label' => 'Menu Font Size',
                'type' => 'range',
                'min' => 12,
                'max' => 20,
                'step' => 1,
                'default' => 15,
                'unit' => 'px',
                'responsive' => true
            ],
            'menu_font_weight' => [
                'label' => 'Menu Font Weight',
                'type' => 'select',
                'options' => [
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi-Bold',
                    '700' => 'Bold'
                ],
                'default' => '500'
            ],
            'menu_item_spacing' => [
                'label' => 'Menu Item Spacing',
                'type' => 'range',
                'min' => 10,
                'max' => 40,
                'step' => 2,
                'default' => 24,
                'unit' => 'px'
            ],
            'icon_size' => [
                'label' => 'Icon Size',
                'type' => 'range',
                'min' => 16,
                'max' => 32,
                'step' => 1,
                'default' => 20,
                'unit' => 'px'
            ],
            'sticky' => [
                'label' => 'Sticky Menu',
                'type' => 'toggle',
                'default' => false
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $id = $attrs['id'] ?? 'menu_' . uniqid();
        $logo = $attrs['logo'] ?? '';
        $logoUrl = $attrs['logo_url'] ?? '/';
        $logoAlt = $attrs['logo_alt'] ?? 'Site Logo';
        $menuStyle = $attrs['menu_style'] ?? 'left_aligned';
        $showSearch = $attrs['show_search_icon'] ?? true;
        $showCart = $attrs['show_cart_icon'] ?? false;
        $sticky = $attrs['sticky'] ?? false;

        // SVG icons
        $searchIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>';

        $cartIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>';

        $hamburgerIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>';

        $classes = ['jtb-menu', 'jtb-menu-style-' . $this->esc($menuStyle)];
        if ($sticky) {
            $classes[] = 'jtb-menu-sticky';
        }

        $html = '<nav id="' . $this->esc($id) . '" class="' . implode(' ', $classes) . '">';
        $html .= '<div class="jtb-menu-inner">';

        // Logo
        $html .= '<div class="jtb-menu-logo">';
        if ($logo) {
            $html .= '<a href="' . $this->esc($logoUrl) . '" class="jtb-logo-link">';
            $html .= '<img src="' . $this->esc($logo) . '" alt="' . $this->esc($logoAlt) . '" class="jtb-logo-img">';
            $html .= '</a>';
        } else {
            $html .= '<a href="' . $this->esc($logoUrl) . '" class="jtb-logo-text">LOGO</a>';
        }
        $html .= '</div>';

        // Navigation
        $html .= '<div class="jtb-menu-nav">';
        $html .= '<ul class="jtb-nav-list">';
        $html .= '<li class="jtb-nav-item"><a href="#" class="jtb-nav-link">Home</a></li>';
        $html .= '<li class="jtb-nav-item"><a href="#" class="jtb-nav-link">About</a></li>';
        $html .= '<li class="jtb-nav-item"><a href="#" class="jtb-nav-link">Services</a></li>';
        $html .= '<li class="jtb-nav-item"><a href="#" class="jtb-nav-link">Contact</a></li>';
        $html .= '</ul>';
        $html .= '</div>';

        // Icons
        $html .= '<div class="jtb-menu-icons">';
        if ($showSearch) {
            $html .= '<button type="button" class="jtb-menu-icon jtb-search-toggle" aria-label="Search">' . $searchIcon . '</button>';
        }
        if ($showCart) {
            $html .= '<a href="#" class="jtb-menu-icon jtb-cart-icon" aria-label="Cart">' . $cartIcon . '</a>';
        }
        $html .= '<button type="button" class="jtb-menu-icon jtb-hamburger" aria-label="Menu">' . $hamburgerIcon . '</button>';
        $html .= '</div>';

        $html .= '</div>';
        $html .= '</nav>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);

        $menuStyle = $attrs['menu_style'] ?? 'left_aligned';
        $logoWidth = $attrs['logo_width'] ?? 150;
        $menuTextColor = $attrs['menu_text_color'] ?? '#333333';
        $menuHoverColor = $attrs['menu_text_color__hover'] ?? '#2ea3f2';
        $iconColor = $attrs['icon_color'] ?? '#333333';
        $iconHoverColor = $attrs['icon_color__hover'] ?? '#2ea3f2';
        $fontSize = $attrs['menu_font_size'] ?? 15;
        $fontWeight = $attrs['menu_font_weight'] ?? '500';
        $itemSpacing = $attrs['menu_item_spacing'] ?? 24;
        $iconSize = $attrs['icon_size'] ?? 20;
        $sticky = $attrs['sticky'] ?? false;

        // Nav container
        $css .= $selector . ' { position: relative; }' . "\n";

        if ($sticky) {
            $css .= $selector . '.jtb-menu-sticky { position: sticky; top: 0; z-index: 1000; }' . "\n";
        }

        // Inner container
        $css .= $selector . ' .jtb-menu-inner { ';
        $css .= 'display: flex; ';
        $css .= 'align-items: center; ';
        if ($menuStyle === 'left_aligned') {
            $css .= 'justify-content: space-between; ';
        } elseif ($menuStyle === 'centered_logo') {
            $css .= 'justify-content: center; ';
        } elseif ($menuStyle === 'stacked') {
            $css .= 'flex-direction: column; ';
            $css .= 'gap: 16px; ';
        }
        $css .= '}' . "\n";

        // Logo
        $css .= $selector . ' .jtb-menu-logo { display: flex; align-items: center; }' . "\n";
        $css .= $selector . ' .jtb-logo-link { display: inline-block; line-height: 0; }' . "\n";
        $css .= $selector . ' .jtb-logo-img { width: ' . intval($logoWidth) . 'px; height: auto; }' . "\n";
        $css .= $selector . ' .jtb-logo-text { font-weight: bold; font-size: 20px; color: ' . $menuTextColor . '; text-decoration: none; }' . "\n";

        // Nav list
        $css .= $selector . ' .jtb-menu-nav { display: flex; align-items: center; }' . "\n";
        $css .= $selector . ' .jtb-nav-list { ';
        $css .= 'list-style: none; ';
        $css .= 'margin: 0; ';
        $css .= 'padding: 0; ';
        $css .= 'display: flex; ';
        $css .= 'align-items: center; ';
        $css .= 'gap: ' . intval($itemSpacing) . 'px; ';
        $css .= '}' . "\n";

        // Nav item
        $css .= $selector . ' .jtb-nav-item { display: flex; align-items: center; }' . "\n";

        // Nav link
        $css .= $selector . ' .jtb-nav-link { ';
        $css .= 'color: ' . $menuTextColor . '; ';
        $css .= 'text-decoration: none; ';
        $css .= 'font-size: ' . intval($fontSize) . 'px; ';
        $css .= 'font-weight: ' . $fontWeight . '; ';
        $css .= 'transition: color 0.3s ease; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-nav-link:hover { color: ' . $menuHoverColor . '; }' . "\n";

        // Icons container
        $css .= $selector . ' .jtb-menu-icons { ';
        $css .= 'display: flex; ';
        $css .= 'align-items: center; ';
        $css .= 'gap: 12px; ';
        $css .= '}' . "\n";

        // Icon buttons
        $css .= $selector . ' .jtb-menu-icon { ';
        $css .= 'display: flex; ';
        $css .= 'align-items: center; ';
        $css .= 'justify-content: center; ';
        $css .= 'background: none; ';
        $css .= 'border: none; ';
        $css .= 'padding: 8px; ';
        $css .= 'cursor: pointer; ';
        $css .= 'color: ' . $iconColor . '; ';
        $css .= 'transition: color 0.3s ease; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-menu-icon:hover { color: ' . $iconHoverColor . '; }' . "\n";

        $css .= $selector . ' .jtb-menu-icon svg { ';
        $css .= 'width: ' . intval($iconSize) . 'px; ';
        $css .= 'height: ' . intval($iconSize) . 'px; ';
        $css .= '}' . "\n";

        // Hamburger - hidden on desktop
        $css .= $selector . ' .jtb-hamburger { display: none; }' . "\n";

        // Responsive - Logo width
        if (!empty($attrs['logo_width__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-logo-img { width: ' . intval($attrs['logo_width__tablet']) . 'px; }';
            $css .= ' }' . "\n";
        }

        if (!empty($attrs['logo_width__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-logo-img { width: ' . intval($attrs['logo_width__phone']) . 'px; }';
            $css .= ' }' . "\n";
        }

        // Responsive - Font size
        if (!empty($attrs['menu_font_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-nav-link { font-size: ' . intval($attrs['menu_font_size__tablet']) . 'px; }';
            $css .= ' }' . "\n";
        }

        if (!empty($attrs['menu_font_size__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-nav-link { font-size: ' . intval($attrs['menu_font_size__phone']) . 'px; }';
            $css .= ' }' . "\n";
        }

        // Mobile menu
        $css .= '@media (max-width: 767px) { ';
        $css .= $selector . ' .jtb-menu-nav { display: none; }';
        $css .= $selector . ' .jtb-hamburger { display: flex; }';
        $css .= ' }' . "\n";

        return $css;
    }
}

JTB_Registry::register('menu', JTB_Module_Menu::class);
