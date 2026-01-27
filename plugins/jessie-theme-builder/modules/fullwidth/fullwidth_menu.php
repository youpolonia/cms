<?php
/**
 * Fullwidth Menu Module
 * Full-width navigation menu
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_FullwidthMenu extends JTB_Element
{
    public string $icon = 'menu';
    public string $category = 'fullwidth';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = false;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    public function getSlug(): string
    {
        return 'fullwidth_menu';
    }

    public function getName(): string
    {
        return 'Fullwidth Menu';
    }

    public function getFields(): array
    {
        return [
            'menu_id' => [
                'label' => 'Menu',
                'type' => 'select',
                'options' => [
                    'primary' => 'Primary Menu',
                    'secondary' => 'Secondary Menu',
                    'footer' => 'Footer Menu'
                ],
                'default' => 'primary'
            ],
            'menu_style' => [
                'label' => 'Menu Style',
                'type' => 'select',
                'options' => [
                    'left' => 'Left Aligned',
                    'centered' => 'Centered',
                    'inline_centered_logo' => 'Centered Logo',
                    'fullwidth' => 'Full Width'
                ],
                'default' => 'left'
            ],
            'submenu_direction' => [
                'label' => 'Submenu Direction',
                'type' => 'select',
                'options' => [
                    'downward' => 'Downward',
                    'upward' => 'Upward'
                ],
                'default' => 'downward'
            ],
            'fullwidth_menu' => [
                'label' => 'Full Width Menu',
                'type' => 'toggle',
                'default' => false
            ],
            'active_link_color' => [
                'label' => 'Active Link Color',
                'type' => 'color',
                'default' => '#2ea3f2'
            ],
            'dropdown_menu_bg_color' => [
                'label' => 'Dropdown Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'dropdown_menu_line_color' => [
                'label' => 'Dropdown Border Color',
                'type' => 'color',
                'default' => '#e5e5e5'
            ],
            'dropdown_menu_text_color' => [
                'label' => 'Dropdown Text Color',
                'type' => 'color',
                'default' => '#666666'
            ],
            'menu_link_color' => [
                'label' => 'Menu Link Color',
                'type' => 'color',
                'default' => '#666666',
                'hover' => true
            ],
            'mobile_menu_bg_color' => [
                'label' => 'Mobile Menu Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'mobile_menu_text_color' => [
                'label' => 'Mobile Menu Text Color',
                'type' => 'color',
                'default' => '#666666'
            ],
            'logo' => [
                'label' => 'Logo Image',
                'type' => 'upload'
            ],
            'logo_max_height' => [
                'label' => 'Logo Max Height',
                'type' => 'range',
                'min' => 20,
                'max' => 200,
                'unit' => 'px',
                'default' => 54
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $menuStyle = $attrs['menu_style'] ?? 'left';
        $logo = $attrs['logo'] ?? '';

        // Sample menu items
        $menuItems = [
            ['label' => 'Home', 'url' => '#', 'active' => true, 'children' => []],
            ['label' => 'About', 'url' => '#', 'active' => false, 'children' => []],
            ['label' => 'Services', 'url' => '#', 'active' => false, 'children' => [
                ['label' => 'Web Design', 'url' => '#'],
                ['label' => 'Development', 'url' => '#'],
                ['label' => 'Marketing', 'url' => '#']
            ]],
            ['label' => 'Portfolio', 'url' => '#', 'active' => false, 'children' => []],
            ['label' => 'Contact', 'url' => '#', 'active' => false, 'children' => []]
        ];

        $innerHtml = '<div class="jtb-fullwidth-menu-container jtb-menu-style-' . $menuStyle . '">';

        // Mobile menu toggle
        $innerHtml .= '<button class="jtb-mobile-menu-toggle" aria-label="Toggle menu"><span></span><span></span><span></span></button>';

        // Logo
        if ($menuStyle === 'inline_centered_logo' || !empty($logo)) {
            $innerHtml .= '<div class="jtb-menu-logo">';
            if (!empty($logo)) {
                $innerHtml .= '<a href="/"><img src="' . $this->esc($logo) . '" alt="Logo" /></a>';
            } else {
                $innerHtml .= '<a href="/" class="jtb-logo-text">Logo</a>';
            }
            $innerHtml .= '</div>';
        }

        // Menu
        $innerHtml .= '<nav class="jtb-menu-nav">';
        $innerHtml .= '<ul class="jtb-menu">';

        foreach ($menuItems as $item) {
            $hasChildren = !empty($item['children']);
            $activeClass = !empty($item['active']) ? ' jtb-current-menu-item' : '';
            $parentClass = $hasChildren ? ' jtb-menu-item-has-children' : '';

            $innerHtml .= '<li class="jtb-menu-item' . $activeClass . $parentClass . '">';
            $innerHtml .= '<a href="' . $this->esc($item['url']) . '">' . $this->esc($item['label']) . '</a>';

            if ($hasChildren) {
                $innerHtml .= '<ul class="jtb-sub-menu">';
                foreach ($item['children'] as $child) {
                    $innerHtml .= '<li class="jtb-menu-item"><a href="' . $this->esc($child['url']) . '">' . $this->esc($child['label']) . '</a></li>';
                }
                $innerHtml .= '</ul>';
            }

            $innerHtml .= '</li>';
        }

        $innerHtml .= '</ul>';
        $innerHtml .= '</nav>';
        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        $menuStyle = $attrs['menu_style'] ?? 'left';
        $linkColor = $attrs['menu_link_color'] ?? '#666666';
        $activeColor = $attrs['active_link_color'] ?? '#2ea3f2';
        $dropdownBg = $attrs['dropdown_menu_bg_color'] ?? '#ffffff';
        $dropdownText = $attrs['dropdown_menu_text_color'] ?? '#666666';
        $dropdownBorder = $attrs['dropdown_menu_line_color'] ?? '#e5e5e5';
        $mobileMenuBg = $attrs['mobile_menu_bg_color'] ?? '#ffffff';
        $mobileMenuText = $attrs['mobile_menu_text_color'] ?? '#666666';
        $logoHeight = $attrs['logo_max_height'] ?? 54;

        // Container
        $css .= $selector . ' .jtb-fullwidth-menu-container { display: flex; align-items: center; padding: 15px 30px; }' . "\n";

        // Menu styles
        if ($menuStyle === 'centered') {
            $css .= $selector . ' .jtb-fullwidth-menu-container { justify-content: center; }' . "\n";
        } elseif ($menuStyle === 'inline_centered_logo') {
            $css .= $selector . ' .jtb-fullwidth-menu-container { justify-content: space-between; }' . "\n";
            $css .= $selector . ' .jtb-menu-logo { position: absolute; left: 50%; transform: translateX(-50%); }' . "\n";
        } elseif ($menuStyle === 'fullwidth') {
            $css .= $selector . ' .jtb-menu { justify-content: space-between; width: 100%; }' . "\n";
        } else {
            $css .= $selector . ' .jtb-fullwidth-menu-container { justify-content: space-between; }' . "\n";
        }

        // Logo
        $css .= $selector . ' .jtb-menu-logo img { max-height: ' . $logoHeight . 'px; width: auto; }' . "\n";
        $css .= $selector . ' .jtb-logo-text { font-size: 24px; font-weight: bold; text-decoration: none; color: inherit; }' . "\n";

        // Menu
        $css .= $selector . ' .jtb-menu { display: flex; list-style: none; margin: 0; padding: 0; }' . "\n";
        $css .= $selector . ' .jtb-menu-item { position: relative; }' . "\n";
        $css .= $selector . ' .jtb-menu-item > a { display: block; padding: 15px 20px; color: ' . $linkColor . '; text-decoration: none; transition: color 0.3s ease; }' . "\n";

        // Hover and active states
        if (!empty($attrs['menu_link_color__hover'])) {
            $css .= $selector . ' .jtb-menu-item > a:hover { color: ' . $attrs['menu_link_color__hover'] . '; }' . "\n";
        } else {
            $css .= $selector . ' .jtb-menu-item > a:hover { color: ' . $activeColor . '; }' . "\n";
        }
        $css .= $selector . ' .jtb-current-menu-item > a { color: ' . $activeColor . '; }' . "\n";

        // Dropdown indicator - using CSS arrow instead of unicode
        $css .= $selector . ' .jtb-menu-item-has-children > a::after { content: ""; display: inline-block; width: 0; height: 0; margin-left: 8px; vertical-align: middle; border-left: 4px solid transparent; border-right: 4px solid transparent; border-top: 5px solid currentColor; }' . "\n";

        // Submenu
        $css .= $selector . ' .jtb-sub-menu { display: none; position: absolute; top: 100%; left: 0; min-width: 200px; background: ' . $dropdownBg . '; border: 1px solid ' . $dropdownBorder . '; list-style: none; padding: 0; margin: 0; z-index: 100; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }' . "\n";
        $css .= $selector . ' .jtb-menu-item:hover > .jtb-sub-menu { display: block; }' . "\n";
        $css .= $selector . ' .jtb-sub-menu .jtb-menu-item > a { padding: 12px 20px; color: ' . $dropdownText . '; border-bottom: 1px solid ' . $dropdownBorder . '; }' . "\n";
        $css .= $selector . ' .jtb-sub-menu .jtb-menu-item:last-child > a { border-bottom: none; }' . "\n";

        // Mobile toggle
        $css .= $selector . ' .jtb-mobile-menu-toggle { display: none; background: none; border: none; cursor: pointer; padding: 10px; }' . "\n";
        $css .= $selector . ' .jtb-mobile-menu-toggle span { display: block; width: 25px; height: 3px; background: ' . $linkColor . '; margin: 5px 0; transition: all 0.3s ease; }' . "\n";

        // Mobile styles
        $css .= '@media (max-width: 980px) {' . "\n";
        $css .= '  ' . $selector . ' .jtb-mobile-menu-toggle { display: block; }' . "\n";
        $css .= '  ' . $selector . ' .jtb-menu-nav { display: none; position: absolute; top: 100%; left: 0; right: 0; background: ' . $mobileMenuBg . '; z-index: 99; }' . "\n";
        $css .= '  ' . $selector . ' .jtb-menu-nav.jtb-menu-open { display: block; }' . "\n";
        $css .= '  ' . $selector . ' .jtb-menu { flex-direction: column; }' . "\n";
        $css .= '  ' . $selector . ' .jtb-menu-item > a { color: ' . $mobileMenuText . '; border-bottom: 1px solid #eee; }' . "\n";
        $css .= '  ' . $selector . ' .jtb-sub-menu { position: static; box-shadow: none; }' . "\n";
        $css .= '}' . "\n";

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('fullwidth_menu', JTB_Module_FullwidthMenu::class);
