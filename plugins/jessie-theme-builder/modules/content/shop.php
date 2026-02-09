<?php
/**
 * Shop Module
 * E-commerce product grid
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Shop extends JTB_Element
{
    public string $icon = 'shop';
    public string $category = 'content';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'shop';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'title_color' => [
            'property' => 'color',
            'selector' => '.jtb-product-title a',
            'hover' => true
        ],
        'price_color' => [
            'property' => 'color',
            'selector' => '.jtb-price-current'
        ],
        'sale_price_color' => [
            'property' => 'color',
            'selector' => '.jtb-price-sale'
        ],
        'rating_color' => [
            'property' => 'color',
            'selector' => '.jtb-product-rating'
        ],
        'sale_badge_bg' => [
            'property' => 'background',
            'selector' => '.jtb-product-sale-badge'
        ],
        'sale_badge_text' => [
            'property' => 'color',
            'selector' => '.jtb-product-sale-badge'
        ],
        'button_bg_color' => [
            'property' => 'background',
            'selector' => '.jtb-add-to-cart',
            'hover' => true
        ],
        'button_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-add-to-cart',
            'hover' => true
        ]
    ];

    public function getSlug(): string
    {
        return 'shop';
    }

    public function getName(): string
    {
        return 'Shop';
    }

    public function getFields(): array
    {
        return [
            'posts_number' => [
                'label' => 'Number of Products',
                'type' => 'range',
                'min' => 1,
                'max' => 24,
                'default' => 8
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
            'show_title' => [
                'label' => 'Show Title',
                'type' => 'toggle',
                'default' => true
            ],
            'show_price' => [
                'label' => 'Show Price',
                'type' => 'toggle',
                'default' => true
            ],
            'show_rating' => [
                'label' => 'Show Rating',
                'type' => 'toggle',
                'default' => true
            ],
            'show_add_to_cart' => [
                'label' => 'Show Add to Cart',
                'type' => 'toggle',
                'default' => true
            ],
            'show_sale_badge' => [
                'label' => 'Show Sale Badge',
                'type' => 'toggle',
                'default' => true
            ],
            'orderby' => [
                'label' => 'Order By',
                'type' => 'select',
                'options' => [
                    'date' => 'Date',
                    'price' => 'Price',
                    'popularity' => 'Popularity',
                    'rating' => 'Rating',
                    'title' => 'Title'
                ],
                'default' => 'date'
            ],
            // Colors
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#333333',
                'hover' => true
            ],
            'price_color' => [
                'label' => 'Price Color',
                'type' => 'color',
                'default' => '#2ea3f2'
            ],
            'sale_price_color' => [
                'label' => 'Sale Price Color',
                'type' => 'color',
                'default' => '#e74c3c'
            ],
            'rating_color' => [
                'label' => 'Rating Color',
                'type' => 'color',
                'default' => '#f5a623'
            ],
            'sale_badge_bg' => [
                'label' => 'Sale Badge Background',
                'type' => 'color',
                'default' => '#e74c3c'
            ],
            'sale_badge_text' => [
                'label' => 'Sale Badge Text',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'button_bg_color' => [
                'label' => 'Button Background',
                'type' => 'color',
                'default' => '#2ea3f2',
                'hover' => true
            ],
            'button_text_color' => [
                'label' => 'Button Text',
                'type' => 'color',
                'default' => '#ffffff',
                'hover' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $columns = $attrs['columns'] ?? '4';
        $postsNumber = $attrs['posts_number'] ?? 8;
        $showTitle = $attrs['show_title'] ?? true;
        $showPrice = $attrs['show_price'] ?? true;
        $showRating = $attrs['show_rating'] ?? true;
        $showAddToCart = $attrs['show_add_to_cart'] ?? true;
        $showSaleBadge = $attrs['show_sale_badge'] ?? true;

        // Sample products
        $products = [
            ['title' => 'Premium Headphones', 'price' => 149.99, 'sale_price' => 99.99, 'rating' => 4.5, 'on_sale' => true],
            ['title' => 'Wireless Mouse', 'price' => 49.99, 'sale_price' => null, 'rating' => 4.0, 'on_sale' => false],
            ['title' => 'Mechanical Keyboard', 'price' => 129.99, 'sale_price' => null, 'rating' => 4.8, 'on_sale' => false],
            ['title' => 'USB-C Hub', 'price' => 79.99, 'sale_price' => 59.99, 'rating' => 4.2, 'on_sale' => true],
            ['title' => 'Laptop Stand', 'price' => 39.99, 'sale_price' => null, 'rating' => 4.6, 'on_sale' => false],
            ['title' => 'Monitor Light Bar', 'price' => 89.99, 'sale_price' => null, 'rating' => 4.4, 'on_sale' => false],
            ['title' => 'Webcam HD', 'price' => 79.99, 'sale_price' => 49.99, 'rating' => 3.9, 'on_sale' => true],
            ['title' => 'Desk Mat XL', 'price' => 29.99, 'sale_price' => null, 'rating' => 4.7, 'on_sale' => false]
        ];

        $innerHtml = '<div class="jtb-shop-container">';
        $innerHtml .= '<div class="jtb-products-grid jtb-shop-cols-' . $columns . '">';

        $count = 0;
        foreach ($products as $product) {
            if ($count >= $postsNumber) break;

            $innerHtml .= '<div class="jtb-product-item">';
            $innerHtml .= '<div class="jtb-product-image">';

            // Placeholder image
            $hue = ($count * 45 + 180) % 360;
            $innerHtml .= '<div class="jtb-product-image-placeholder" style="background: hsl(' . $hue . ', 40%, 85%);"></div>';

            // Sale badge
            if ($showSaleBadge && $product['on_sale']) {
                $innerHtml .= '<span class="jtb-product-sale-badge">Sale!</span>';
            }

            // Quick actions - SVG icons
            $eyeIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
            $heartIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>';
            $innerHtml .= '<div class="jtb-product-actions">';
            $innerHtml .= '<a href="#" class="jtb-product-action jtb-quick-view" title="Quick View">' . $eyeIcon . '</a>';
            $innerHtml .= '<a href="#" class="jtb-product-action jtb-wishlist" title="Add to Wishlist">' . $heartIcon . '</a>';
            $innerHtml .= '</div>';

            $innerHtml .= '</div>';

            $innerHtml .= '<div class="jtb-product-info">';

            // Rating - using SVG stars
            if ($showRating) {
                $starFilled = '<svg class="jtb-star jtb-star-filled" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
                $starEmpty = '<svg class="jtb-star jtb-star-empty" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
                $starHalf = '<svg class="jtb-star jtb-star-half" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><defs><linearGradient id="half-grad"><stop offset="50%" stop-color="currentColor"/><stop offset="50%" stop-color="transparent"/></linearGradient></defs><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="url(#half-grad)"></polygon></svg>';

                $innerHtml .= '<div class="jtb-product-rating">';
                $fullStars = floor($product['rating']);
                $halfStar = $product['rating'] - $fullStars >= 0.5;
                for ($i = 0; $i < 5; $i++) {
                    if ($i < $fullStars) {
                        $innerHtml .= $starFilled;
                    } elseif ($i == $fullStars && $halfStar) {
                        $innerHtml .= $starHalf;
                    } else {
                        $innerHtml .= $starEmpty;
                    }
                }
                $innerHtml .= '<span class="jtb-rating-count">(' . number_format($product['rating'], 1) . ')</span>';
                $innerHtml .= '</div>';
            }

            // Title
            if ($showTitle) {
                $innerHtml .= '<h3 class="jtb-product-title"><a href="#">' . $this->esc($product['title']) . '</a></h3>';
            }

            // Price
            if ($showPrice) {
                $innerHtml .= '<div class="jtb-product-price">';
                if ($product['on_sale'] && $product['sale_price']) {
                    $innerHtml .= '<span class="jtb-price-original">$' . number_format($product['price'], 2) . '</span>';
                    $innerHtml .= '<span class="jtb-price-sale">$' . number_format($product['sale_price'], 2) . '</span>';
                } else {
                    $innerHtml .= '<span class="jtb-price-current">$' . number_format($product['price'], 2) . '</span>';
                }
                $innerHtml .= '</div>';
            }

            // Add to cart
            if ($showAddToCart) {
                $innerHtml .= '<button class="jtb-button jtb-add-to-cart">Add to Cart</button>';
            }

            $innerHtml .= '</div>';
            $innerHtml .= '</div>';

            $count++;
        }

        $innerHtml .= '</div>';
        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for Shop module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $buttonBg = $attrs['button_bg_color'] ?? '#2ea3f2';
        $priceColor = $attrs['price_color'] ?? '#2ea3f2';

        // Grid
        $css .= $selector . ' .jtb-products-grid { display: grid; gap: 30px; }' . "\n";
        $css .= $selector . ' .jtb-shop-cols-2 { grid-template-columns: repeat(2, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-shop-cols-3 { grid-template-columns: repeat(3, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-shop-cols-4 { grid-template-columns: repeat(4, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-shop-cols-5 { grid-template-columns: repeat(5, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-shop-cols-6 { grid-template-columns: repeat(6, 1fr); }' . "\n";

        // Product item
        $css .= $selector . ' .jtb-product-item { text-align: center; }' . "\n";

        // Image
        $css .= $selector . ' .jtb-product-image { position: relative; overflow: hidden; margin-bottom: 15px; }' . "\n";
        $css .= $selector . ' .jtb-product-image-placeholder { padding-bottom: 100%; }' . "\n";
        $css .= $selector . ' .jtb-product-image img { width: 100%; height: auto; display: block; transition: transform 0.3s ease; }' . "\n";
        $css .= $selector . ' .jtb-product-item:hover .jtb-product-image img { transform: scale(1.05); }' . "\n";

        // Sale badge
        $css .= $selector . ' .jtb-product-sale-badge { position: absolute; top: 10px; left: 10px; padding: 5px 10px; font-size: 12px; font-weight: bold; text-transform: uppercase; }' . "\n";

        // Actions
        $css .= $selector . ' .jtb-product-actions { position: absolute; top: 10px; right: 10px; display: flex; flex-direction: column; gap: 5px; opacity: 0; transform: translateX(10px); transition: all 0.3s ease; }' . "\n";
        $css .= $selector . ' .jtb-product-item:hover .jtb-product-actions { opacity: 1; transform: translateX(0); }' . "\n";
        $css .= $selector . ' .jtb-product-action { width: 35px; height: 35px; background: #ffffff; display: flex; align-items: center; justify-content: center; text-decoration: none; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: all 0.3s ease; }' . "\n";
        $css .= $selector . ' .jtb-product-action svg { width: 16px; height: 16px; }' . "\n";
        $css .= $selector . ' .jtb-product-action:hover { background: ' . $buttonBg . '; color: #fff; }' . "\n";

        // Rating
        $css .= $selector . ' .jtb-product-rating { margin-bottom: 8px; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 2px; }' . "\n";
        $css .= $selector . ' .jtb-star { width: 14px; height: 14px; }' . "\n";
        $css .= $selector . ' .jtb-rating-count { color: #999; font-size: 12px; margin-left: 5px; }' . "\n";

        // Title
        $css .= $selector . ' .jtb-product-title { margin: 0 0 10px; font-size: 16px; }' . "\n";
        $css .= $selector . ' .jtb-product-title a { text-decoration: none; transition: color 0.3s ease; }' . "\n";
        if (empty($attrs['title_color__hover'])) {
            $css .= $selector . ' .jtb-product-title a:hover { color: ' . $priceColor . '; }' . "\n";
        }

        // Price
        $css .= $selector . ' .jtb-product-price { margin-bottom: 15px; }' . "\n";
        $css .= $selector . ' .jtb-price-current { font-size: 18px; font-weight: bold; }' . "\n";
        $css .= $selector . ' .jtb-price-original { color: #999; text-decoration: line-through; margin-right: 10px; }' . "\n";
        $css .= $selector . ' .jtb-price-sale { font-size: 18px; font-weight: bold; }' . "\n";

        // Add to cart
        $css .= $selector . ' .jtb-add-to-cart { border: none; padding: 10px 25px; cursor: pointer; font-size: 14px; transition: all 0.3s ease; opacity: 0; transform: translateY(10px); }' . "\n";
        $css .= $selector . ' .jtb-product-item:hover .jtb-add-to-cart { opacity: 1; transform: translateY(0); }' . "\n";

        // Responsive
        $css .= '@media (max-width: 980px) {' . "\n";
        $css .= '  ' . $selector . ' .jtb-shop-cols-4, ' . $selector . ' .jtb-shop-cols-5, ' . $selector . ' .jtb-shop-cols-6 { grid-template-columns: repeat(3, 1fr); }' . "\n";
        $css .= '  ' . $selector . ' .jtb-add-to-cart { opacity: 1; transform: translateY(0); }' . "\n";
        $css .= '}' . "\n";

        $css .= '@media (max-width: 767px) {' . "\n";
        $css .= '  ' . $selector . ' .jtb-products-grid { grid-template-columns: repeat(2, 1fr) !important; }' . "\n";
        $css .= '}' . "\n";

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('shop', JTB_Module_Shop::class);
