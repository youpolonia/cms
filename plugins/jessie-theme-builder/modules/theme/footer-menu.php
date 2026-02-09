<?php
/**
 * Footer Menu Module
 * Simple navigation links for footer (Privacy Policy, Terms, etc.)
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Footer_Menu extends JTB_Element
{
    public string $slug = 'footer_menu';
    public string $name = 'Footer Menu';
    public string $icon = 'list';
    public string $category = 'footer';

    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = false;
    public bool $use_animation = true;
    public bool $use_typography = true;

    protected string $module_prefix = 'footer_menu';

    protected array $style_config = [
        'title_color' => [
            'property' => 'color',
            'selector' => '.jtb-footer-menu-title'
        ],
        'link_color' => [
            'property' => 'color',
            'selector' => '.jtb-footer-nav-link',
            'hover' => true
        ],
        'font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-footer-nav-link',
            'unit' => 'px',
            'responsive' => true
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
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Quick Links'
            ],
            'show_title' => [
                'label' => 'Show Title',
                'type' => 'toggle',
                'default' => true
            ],
            'layout' => [
                'label' => 'Layout',
                'type' => 'select',
                'options' => [
                    'vertical' => 'Vertical (stacked)',
                    'horizontal' => 'Horizontal (inline)'
                ],
                'default' => 'vertical'
            ],
            'menu_items' => [
                'label' => 'Menu Items',
                'type' => 'textarea',
                'description' => 'One link per line: Label | URL',
                'default' => "Home | /\nAbout | /about\nServices | /services\nContact | /contact"
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'link_color' => [
                'label' => 'Link Color',
                'type' => 'color',
                'default' => '#cccccc',
                'hover' => true
            ],
            'font_size' => [
                'label' => 'Font Size',
                'type' => 'range',
                'min' => 12,
                'max' => 18,
                'step' => 1,
                'default' => 14,
                'unit' => 'px',
                'responsive' => true
            ],
            'item_spacing' => [
                'label' => 'Item Spacing',
                'type' => 'range',
                'min' => 4,
                'max' => 20,
                'step' => 2,
                'default' => 10,
                'unit' => 'px'
            ],
            'show_bullet' => [
                'label' => 'Show Bullet',
                'type' => 'toggle',
                'description' => 'Show bullet/arrow before links',
                'default' => false
            ],
            'bullet_style' => [
                'label' => 'Bullet Style',
                'type' => 'select',
                'options' => [
                    'arrow' => 'Arrow →',
                    'chevron' => 'Chevron ›',
                    'dot' => 'Dot •',
                    'dash' => 'Dash -'
                ],
                'default' => 'arrow',
                'condition' => ['show_bullet' => true]
            ],
            'open_new_tab' => [
                'label' => 'Open Links in New Tab',
                'type' => 'toggle',
                'default' => false
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $id = $attrs['id'] ?? 'footer_menu_' . uniqid();
        $title = $attrs['title'] ?? 'Quick Links';
        $showTitle = $attrs['show_title'] ?? true;
        $layout = $attrs['layout'] ?? 'vertical';
        $menuItems = $attrs['menu_items'] ?? '';
        $showBullet = $attrs['show_bullet'] ?? false;
        $bulletStyle = $attrs['bullet_style'] ?? 'arrow';
        $openNewTab = $attrs['open_new_tab'] ?? false;

        $bullets = [
            'arrow' => '→',
            'chevron' => '›',
            'dot' => '•',
            'dash' => '-'
        ];
        $bullet = $bullets[$bulletStyle] ?? '→';

        // Parse menu items
        $items = [];
        $lines = explode("\n", $menuItems);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (strpos($line, '|') !== false) {
                list($label, $url) = array_map('trim', explode('|', $line, 2));
                $items[] = ['label' => $label, 'url' => $url];
            } else {
                $items[] = ['label' => $line, 'url' => '#'];
            }
        }

        $targetAttr = $openNewTab ? ' target="_blank" rel="noopener noreferrer"' : '';

        $html = '<nav id="' . $this->esc($id) . '" class="jtb-footer-menu jtb-layout-' . $this->esc($layout) . '">';

        if ($showTitle && $title) {
            $html .= '<h4 class="jtb-footer-menu-title">' . $this->esc($title) . '</h4>';
        }

        $html .= '<ul class="jtb-footer-nav-list">';

        foreach ($items as $item) {
            $html .= '<li class="jtb-footer-nav-item">';
            if ($showBullet) {
                $html .= '<span class="jtb-nav-bullet">' . $this->esc($bullet) . '</span>';
            }
            $html .= '<a href="' . $this->esc($item['url']) . '" class="jtb-footer-nav-link"' . $targetAttr . '>';
            $html .= $this->esc($item['label']);
            $html .= '</a>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</nav>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $layout = $attrs['layout'] ?? 'vertical';
        $titleColor = $attrs['title_color'] ?? '#ffffff';
        $linkColor = $attrs['link_color'] ?? '#cccccc';
        $linkHoverColor = $attrs['link_color__hover'] ?? '#ffffff';
        $fontSize = $attrs['font_size'] ?? 14;
        $itemSpacing = $attrs['item_spacing'] ?? 10;
        $showBullet = $attrs['show_bullet'] ?? false;

        // Title
        $css .= $selector . ' .jtb-footer-menu-title { ';
        $css .= 'color: ' . $titleColor . '; ';
        $css .= 'font-size: 18px; ';
        $css .= 'font-weight: 600; ';
        $css .= 'margin: 0 0 16px 0; ';
        $css .= '}' . "\n";

        // List
        $css .= $selector . ' .jtb-footer-nav-list { ';
        $css .= 'list-style: none; ';
        $css .= 'margin: 0; ';
        $css .= 'padding: 0; ';
        $css .= 'display: flex; ';
        if ($layout === 'vertical') {
            $css .= 'flex-direction: column; ';
            $css .= 'gap: ' . intval($itemSpacing) . 'px; ';
        } else {
            $css .= 'flex-direction: row; ';
            $css .= 'flex-wrap: wrap; ';
            $css .= 'gap: ' . intval($itemSpacing) . 'px ' . (intval($itemSpacing) + 10) . 'px; ';
        }
        $css .= '}' . "\n";

        // Item
        $css .= $selector . ' .jtb-footer-nav-item { ';
        $css .= 'display: flex; ';
        $css .= 'align-items: center; ';
        $css .= 'gap: 6px; ';
        $css .= '}' . "\n";

        // Bullet
        if ($showBullet) {
            $css .= $selector . ' .jtb-nav-bullet { ';
            $css .= 'color: ' . $linkColor . '; ';
            $css .= 'font-size: ' . intval($fontSize) . 'px; ';
            $css .= 'transition: color 0.3s ease, transform 0.3s ease; ';
            $css .= '}' . "\n";

            $css .= $selector . ' .jtb-footer-nav-item:hover .jtb-nav-bullet { ';
            $css .= 'color: ' . $linkHoverColor . '; ';
            $css .= 'transform: translateX(3px); ';
            $css .= '}' . "\n";
        }

        // Link
        $css .= $selector . ' .jtb-footer-nav-link { ';
        $css .= 'color: ' . $linkColor . '; ';
        $css .= 'text-decoration: none; ';
        $css .= 'font-size: ' . intval($fontSize) . 'px; ';
        $css .= 'line-height: 1.4; ';
        $css .= 'transition: color 0.3s ease; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-footer-nav-link:hover { color: ' . $linkHoverColor . '; }' . "\n";

        // Responsive
        if (!empty($attrs['font_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-footer-nav-link { font-size: ' . intval($attrs['font_size__tablet']) . 'px; }';
            $css .= ' }' . "\n";
        }
        if (!empty($attrs['font_size__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-footer-nav-link { font-size: ' . intval($attrs['font_size__phone']) . 'px; }';
            $css .= ' }' . "\n";
        }

        return $css;
    }
}

JTB_Registry::register('footer_menu', JTB_Module_Footer_Menu::class);
