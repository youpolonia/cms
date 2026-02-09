<?php
/**
 * Cart Icon Module
 * Shopping cart icon with count badge for e-commerce
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Cart_Icon extends JTB_Element
{
    public string $slug = 'cart_icon';
    public string $name = 'Cart Icon';
    public string $icon = 'shopping-cart';
    public string $category = 'header';

    public bool $use_background = false;
    public bool $use_spacing = true;
    public bool $use_border = false;
    public bool $use_box_shadow = false;
    public bool $use_animation = true;

    protected string $module_prefix = 'cart_icon';

    protected array $style_config = [
        'icon_color' => [
            'property' => 'color',
            'selector' => '.jtb-cart-icon-link',
            'hover' => true
        ],
        'icon_size' => [
            'property' => 'width',
            'selector' => '.jtb-cart-icon-svg',
            'unit' => 'px'
        ],
        'badge_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-cart-badge'
        ],
        'badge_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-cart-badge'
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
            'cart_url' => [
                'label' => 'Cart Page URL',
                'type' => 'text',
                'description' => 'URL to the shopping cart page',
                'default' => '/cart'
            ],
            'icon_style' => [
                'label' => 'Icon Style',
                'type' => 'select',
                'options' => [
                    'cart' => 'Shopping Cart',
                    'bag' => 'Shopping Bag',
                    'basket' => 'Basket'
                ],
                'default' => 'cart'
            ],
            'show_badge' => [
                'label' => 'Show Count Badge',
                'type' => 'toggle',
                'description' => 'Display item count badge',
                'default' => true
            ],
            'demo_count' => [
                'label' => 'Demo Count',
                'type' => 'number',
                'description' => 'Preview badge with this number (0 to hide)',
                'default' => 3
            ],
            'show_total' => [
                'label' => 'Show Cart Total',
                'type' => 'toggle',
                'description' => 'Display cart total next to icon',
                'default' => false
            ],
            'demo_total' => [
                'label' => 'Demo Total',
                'type' => 'text',
                'description' => 'Preview total amount',
                'default' => '$99.00',
                'condition' => ['show_total' => true]
            ],
            'icon_color' => [
                'label' => 'Icon Color',
                'type' => 'color',
                'default' => '#333333',
                'hover' => true
            ],
            'icon_size' => [
                'label' => 'Icon Size',
                'type' => 'range',
                'min' => 18,
                'max' => 40,
                'step' => 1,
                'default' => 24,
                'unit' => 'px'
            ],
            'badge_bg_color' => [
                'label' => 'Badge Background',
                'type' => 'color',
                'default' => '#e74c3c'
            ],
            'badge_text_color' => [
                'label' => 'Badge Text Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'badge_size' => [
                'label' => 'Badge Size',
                'type' => 'range',
                'min' => 14,
                'max' => 24,
                'step' => 1,
                'default' => 18,
                'unit' => 'px'
            ],
            'total_color' => [
                'label' => 'Total Text Color',
                'type' => 'color',
                'default' => '#333333',
                'condition' => ['show_total' => true]
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $id = $attrs['id'] ?? 'cart_icon_' . uniqid();
        $cartUrl = $attrs['cart_url'] ?? '/cart';
        $iconStyle = $attrs['icon_style'] ?? 'cart';
        $showBadge = $attrs['show_badge'] ?? true;
        $demoCount = intval($attrs['demo_count'] ?? 3);
        $showTotal = $attrs['show_total'] ?? false;
        $demoTotal = $attrs['demo_total'] ?? '$99.00';

        // SVG icons
        $icons = [
            'cart' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="jtb-cart-icon-svg"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>',
            'bag' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="jtb-cart-icon-svg"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>',
            'basket' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="jtb-cart-icon-svg"><path d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h17.234l-2.546-4.243a.5.5 0 1 1 .858-.514l2.909 4.848A1 1 0 0 1 21 7.5v12a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 19.5v-12a1 1 0 0 1 .162-.549l2.909-4.848a.5.5 0 0 1 .686-.032z"></path><path d="M10 10v8"></path><path d="M14 10v8"></path></svg>'
        ];

        $icon = $icons[$iconStyle] ?? $icons['cart'];

        $html = '<div id="' . $this->esc($id) . '" class="jtb-cart-icon">';
        $html .= '<a href="' . $this->esc($cartUrl) . '" class="jtb-cart-icon-link" aria-label="Shopping Cart">';

        $html .= '<span class="jtb-cart-icon-wrapper">';
        $html .= $icon;

        if ($showBadge && $demoCount > 0) {
            $html .= '<span class="jtb-cart-badge" data-count="' . $demoCount . '">' . $demoCount . '</span>';
        }
        $html .= '</span>';

        if ($showTotal) {
            $html .= '<span class="jtb-cart-total">' . $this->esc($demoTotal) . '</span>';
        }

        $html .= '</a>';
        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $iconColor = $attrs['icon_color'] ?? '#333333';
        $iconHoverColor = $attrs['icon_color__hover'] ?? '#2ea3f2';
        $iconSize = $attrs['icon_size'] ?? 24;
        $badgeBg = $attrs['badge_bg_color'] ?? '#e74c3c';
        $badgeText = $attrs['badge_text_color'] ?? '#ffffff';
        $badgeSize = $attrs['badge_size'] ?? 18;
        $totalColor = $attrs['total_color'] ?? '#333333';

        // Container
        $css .= $selector . ' { display: inline-flex; align-items: center; }' . "\n";

        // Link
        $css .= $selector . ' .jtb-cart-icon-link { ';
        $css .= 'display: flex; ';
        $css .= 'align-items: center; ';
        $css .= 'gap: 8px; ';
        $css .= 'color: ' . $iconColor . '; ';
        $css .= 'text-decoration: none; ';
        $css .= 'transition: color 0.3s ease; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-cart-icon-link:hover { color: ' . $iconHoverColor . '; }' . "\n";

        // Icon wrapper
        $css .= $selector . ' .jtb-cart-icon-wrapper { ';
        $css .= 'position: relative; ';
        $css .= 'display: inline-flex; ';
        $css .= '}' . "\n";

        // SVG icon
        $css .= $selector . ' .jtb-cart-icon-svg { ';
        $css .= 'width: ' . intval($iconSize) . 'px; ';
        $css .= 'height: ' . intval($iconSize) . 'px; ';
        $css .= '}' . "\n";

        // Badge
        $css .= $selector . ' .jtb-cart-badge { ';
        $css .= 'position: absolute; ';
        $css .= 'top: -6px; ';
        $css .= 'right: -6px; ';
        $css .= 'min-width: ' . intval($badgeSize) . 'px; ';
        $css .= 'height: ' . intval($badgeSize) . 'px; ';
        $css .= 'padding: 0 5px; ';
        $css .= 'background-color: ' . $badgeBg . '; ';
        $css .= 'color: ' . $badgeText . '; ';
        $css .= 'font-size: ' . (intval($badgeSize) - 6) . 'px; ';
        $css .= 'font-weight: 600; ';
        $css .= 'line-height: ' . intval($badgeSize) . 'px; ';
        $css .= 'text-align: center; ';
        $css .= 'border-radius: ' . (intval($badgeSize) / 2) . 'px; ';
        $css .= '}' . "\n";

        // Total
        $css .= $selector . ' .jtb-cart-total { ';
        $css .= 'color: ' . $totalColor . '; ';
        $css .= 'font-size: 14px; ';
        $css .= 'font-weight: 500; ';
        $css .= '}' . "\n";

        return $css;
    }
}

JTB_Registry::register('cart_icon', JTB_Module_Cart_Icon::class);
