<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Shop Module
 * Displays product grid with prices, sale badges, and add-to-cart buttons
 */
class ShopModule extends Module
{
    public function __construct()
    {
        $this->name = 'Shop';
        $this->slug = 'shop';
        $this->icon = 'shopping-bag';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-shop-preview',
            'card' => '.tb4-shop-card',
            'image' => '.tb4-shop-card-image',
            'badge' => '.tb4-shop-sale-badge',
            'content' => '.tb4-shop-card-content',
            'title' => '.tb4-shop-card-title',
            'price' => '.tb4-shop-card-price',
            'button' => '.tb4-shop-add-btn'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'products_source' => [
                'label' => 'Products Source',
                'type' => 'select',
                'options' => [
                    'sample' => 'Sample Products',
                    'custom' => 'Custom Products'
                ],
                'default' => 'sample'
            ],
            'products_count' => [
                'label' => 'Products to Show',
                'type' => 'select',
                'options' => [
                    '4' => '4 Products',
                    '6' => '6 Products',
                    '8' => '8 Products',
                    '12' => '12 Products'
                ],
                'default' => '6'
            ],
            'product1_image' => [
                'label' => 'Product 1 Image',
                'type' => 'text',
                'default' => ''
            ],
            'product1_title' => [
                'label' => 'Product 1 Title',
                'type' => 'text',
                'default' => 'Classic T-Shirt'
            ],
            'product1_price' => [
                'label' => 'Product 1 Price',
                'type' => 'text',
                'default' => '$29.99'
            ],
            'product1_sale_price' => [
                'label' => 'Product 1 Sale Price',
                'type' => 'text',
                'default' => ''
            ],
            'product1_link' => [
                'label' => 'Product 1 Link',
                'type' => 'text',
                'default' => '#'
            ],
            'product2_image' => [
                'label' => 'Product 2 Image',
                'type' => 'text',
                'default' => ''
            ],
            'product2_title' => [
                'label' => 'Product 2 Title',
                'type' => 'text',
                'default' => 'Leather Jacket'
            ],
            'product2_price' => [
                'label' => 'Product 2 Price',
                'type' => 'text',
                'default' => '$199.99'
            ],
            'product2_sale_price' => [
                'label' => 'Product 2 Sale Price',
                'type' => 'text',
                'default' => '$149.99'
            ],
            'product2_link' => [
                'label' => 'Product 2 Link',
                'type' => 'text',
                'default' => '#'
            ],
            'product3_image' => [
                'label' => 'Product 3 Image',
                'type' => 'text',
                'default' => ''
            ],
            'product3_title' => [
                'label' => 'Product 3 Title',
                'type' => 'text',
                'default' => 'Running Shoes'
            ],
            'product3_price' => [
                'label' => 'Product 3 Price',
                'type' => 'text',
                'default' => '$89.99'
            ],
            'product3_sale_price' => [
                'label' => 'Product 3 Sale Price',
                'type' => 'text',
                'default' => ''
            ],
            'product3_link' => [
                'label' => 'Product 3 Link',
                'type' => 'text',
                'default' => '#'
            ],
            'product4_image' => [
                'label' => 'Product 4 Image',
                'type' => 'text',
                'default' => ''
            ],
            'product4_title' => [
                'label' => 'Product 4 Title',
                'type' => 'text',
                'default' => 'Denim Jeans'
            ],
            'product4_price' => [
                'label' => 'Product 4 Price',
                'type' => 'text',
                'default' => '$59.99'
            ],
            'product4_sale_price' => [
                'label' => 'Product 4 Sale Price',
                'type' => 'text',
                'default' => '$39.99'
            ],
            'product4_link' => [
                'label' => 'Product 4 Link',
                'type' => 'text',
                'default' => '#'
            ],
            'show_sale_badge' => [
                'label' => 'Show Sale Badge',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_add_to_cart' => [
                'label' => 'Show Add to Cart',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'add_to_cart_text' => [
                'label' => 'Add to Cart Text',
                'type' => 'text',
                'default' => 'Add to Cart'
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
                'default' => '4'
            ],
            'gap' => [
                'label' => 'Grid Gap',
                'type' => 'text',
                'default' => '24px'
            ],
            'card_bg_color' => [
                'label' => 'Card Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'card_border_radius' => [
                'label' => 'Card Border Radius',
                'type' => 'text',
                'default' => '12px'
            ],
            'card_shadow' => [
                'label' => 'Card Shadow',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'sm' => 'Small',
                    'md' => 'Medium',
                    'lg' => 'Large'
                ],
                'default' => 'sm'
            ],
            'card_border' => [
                'label' => 'Card Border',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'light' => 'Light',
                    'medium' => 'Medium'
                ],
                'default' => 'light'
            ],
            'image_height' => [
                'label' => 'Image Height',
                'type' => 'text',
                'default' => '200px'
            ],
            'image_fit' => [
                'label' => 'Image Fit',
                'type' => 'select',
                'options' => [
                    'cover' => 'Cover',
                    'contain' => 'Contain'
                ],
                'default' => 'cover'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'text',
                'default' => '16px'
            ],
            'price_color' => [
                'label' => 'Price Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'price_font_size' => [
                'label' => 'Price Font Size',
                'type' => 'text',
                'default' => '18px'
            ],
            'sale_price_color' => [
                'label' => 'Sale Price Color',
                'type' => 'color',
                'default' => '#dc2626'
            ],
            'original_price_color' => [
                'label' => 'Original Price Color',
                'type' => 'color',
                'default' => '#9ca3af'
            ],
            'sale_badge_bg' => [
                'label' => 'Sale Badge Background',
                'type' => 'color',
                'default' => '#dc2626'
            ],
            'sale_badge_color' => [
                'label' => 'Sale Badge Text',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'button_bg_color' => [
                'label' => 'Button Background',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'button_text_color' => [
                'label' => 'Button Text Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'button_border_radius' => [
                'label' => 'Button Border Radius',
                'type' => 'text',
                'default' => '8px'
            ],
            'button_full_width' => [
                'label' => 'Full Width Button',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'hover_effect' => [
                'label' => 'Hover Effect',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'lift' => 'Lift',
                    'zoom' => 'Image Zoom'
                ],
                'default' => 'lift'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    /**
     * Get sample products for preview
     */
    private function getSampleProducts(): array
    {
        return [
            [
                'title' => 'Classic T-Shirt',
                'price' => '$29.99',
                'sale_price' => '',
                'link' => '#',
                'color' => '#667eea'
            ],
            [
                'title' => 'Leather Jacket',
                'price' => '$199.99',
                'sale_price' => '$149.99',
                'link' => '#',
                'color' => '#f59e0b'
            ],
            [
                'title' => 'Running Shoes',
                'price' => '$89.99',
                'sale_price' => '',
                'link' => '#',
                'color' => '#10b981'
            ],
            [
                'title' => 'Denim Jeans',
                'price' => '$59.99',
                'sale_price' => '$39.99',
                'link' => '#',
                'color' => '#8b5cf6'
            ],
            [
                'title' => 'Wool Sweater',
                'price' => '$79.99',
                'sale_price' => '',
                'link' => '#',
                'color' => '#ef4444'
            ],
            [
                'title' => 'Canvas Backpack',
                'price' => '$49.99',
                'sale_price' => '$34.99',
                'link' => '#',
                'color' => '#06b6d4'
            ]
        ];
    }

    /**
     * Get shadow CSS value
     */
    private function getShadowValue(string $shadow): string
    {
        return match($shadow) {
            'none' => 'none',
            'sm' => '0 1px 3px rgba(0,0,0,0.1)',
            'md' => '0 4px 6px rgba(0,0,0,0.1)',
            'lg' => '0 10px 15px rgba(0,0,0,0.1)',
            default => '0 1px 3px rgba(0,0,0,0.1)'
        };
    }

    /**
     * Get border CSS value
     */
    private function getBorderValue(string $border): string
    {
        return match($border) {
            'none' => 'none',
            'light' => '1px solid #e5e7eb',
            'medium' => '1px solid #d1d5db',
            default => '1px solid #e5e7eb'
        };
    }

    public function render(array $settings): string
    {
        // Content fields
        $productsSource = $settings['products_source'] ?? 'sample';
        $productsCount = (int)($settings['products_count'] ?? 6);
        $showSaleBadge = ($settings['show_sale_badge'] ?? 'yes') === 'yes';
        $showAddToCart = ($settings['show_add_to_cart'] ?? 'yes') === 'yes';
        $addToCartText = $settings['add_to_cart_text'] ?? 'Add to Cart';

        // Design fields
        $columns = $settings['columns'] ?? '4';
        $gap = $settings['gap'] ?? '24px';
        $cardBg = $settings['card_bg_color'] ?? '#ffffff';
        $cardRadius = $settings['card_border_radius'] ?? '12px';
        $cardShadow = $this->getShadowValue($settings['card_shadow'] ?? 'sm');
        $cardBorder = $this->getBorderValue($settings['card_border'] ?? 'light');
        $imageHeight = $settings['image_height'] ?? '200px';
        $imageFit = $settings['image_fit'] ?? 'cover';
        $titleColor = $settings['title_color'] ?? '#111827';
        $titleSize = $settings['title_font_size'] ?? '16px';
        $priceColor = $settings['price_color'] ?? '#111827';
        $priceSize = $settings['price_font_size'] ?? '18px';
        $salePriceColor = $settings['sale_price_color'] ?? '#dc2626';
        $originalPriceColor = $settings['original_price_color'] ?? '#9ca3af';
        $badgeBg = $settings['sale_badge_bg'] ?? '#dc2626';
        $badgeColor = $settings['sale_badge_color'] ?? '#ffffff';
        $btnBg = $settings['button_bg_color'] ?? '#2563eb';
        $btnColor = $settings['button_text_color'] ?? '#ffffff';
        $btnRadius = $settings['button_border_radius'] ?? '8px';
        $btnFullWidth = ($settings['button_full_width'] ?? 'yes') === 'yes';
        $hoverEffect = $settings['hover_effect'] ?? 'lift';

        // Build unique ID for scoped styles
        $uniqueId = 'tb4-shop-' . uniqid();

        // Collect products
        $products = [];
        if ($productsSource === 'custom') {
            for ($i = 1; $i <= 4; $i++) {
                $title = $settings['product' . $i . '_title'] ?? '';
                if (!empty(trim($title))) {
                    $products[] = [
                        'title' => $title,
                        'image' => $settings['product' . $i . '_image'] ?? '',
                        'price' => $settings['product' . $i . '_price'] ?? '',
                        'sale_price' => $settings['product' . $i . '_sale_price'] ?? '',
                        'link' => $settings['product' . $i . '_link'] ?? '#',
                        'color' => ''
                    ];
                }
            }
        }

        // Use sample products if no custom products or sample source
        if (empty($products)) {
            $products = array_slice($this->getSampleProducts(), 0, $productsCount);
        }

        // Build HTML
        $html = '<div class="tb4-shop-preview" id="' . esc_attr($uniqueId) . '">';
        $html .= '<div style="display:grid;grid-template-columns:repeat(' . esc_attr($columns) . ',1fr);gap:' . esc_attr($gap) . ';">';

        $gradients = [
            'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
            'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
            'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)'
        ];

        foreach ($products as $index => $product) {
            $hasSale = !empty(trim($product['sale_price'] ?? ''));
            $hoverClass = $hoverEffect !== 'none' ? ' hover-' . $hoverEffect : '';
            $imageBg = !empty($product['image'])
                ? 'url(' . esc_attr($product['image']) . ')'
                : ($product['color'] ? 'linear-gradient(135deg,' . esc_attr($product['color']) . ' 0%,' . esc_attr($product['color']) . '99 100%)' : $gradients[$index % count($gradients)]);

            $cardStyles = [
                'background:' . esc_attr($cardBg),
                'border-radius:' . esc_attr($cardRadius),
                'overflow:hidden',
                'box-shadow:' . $cardShadow,
                'border:' . $cardBorder,
                'transition:transform 0.3s ease,box-shadow 0.3s ease'
            ];

            $html .= '<div class="tb4-shop-card' . $hoverClass . '" style="' . implode(';', $cardStyles) . '">';

            // Image container
            $html .= '<div class="tb4-shop-card-image" style="position:relative;width:100%;height:' . esc_attr($imageHeight) . ';overflow:hidden;background:#f3f4f6;">';
            $html .= '<div class="tb4-shop-card-image-placeholder" style="width:100%;height:100%;background:' . $imageBg . ';background-size:' . esc_attr($imageFit) . ';background-position:center;transition:transform 0.3s ease;"></div>';

            // Sale badge
            if ($showSaleBadge && $hasSale) {
                $html .= '<span class="tb4-shop-sale-badge" style="position:absolute;top:12px;left:12px;padding:4px 12px;background:' . esc_attr($badgeBg) . ';color:' . esc_attr($badgeColor) . ';font-size:12px;font-weight:600;border-radius:4px;text-transform:uppercase;">Sale</span>';
            }
            $html .= '</div>';

            // Content
            $html .= '<div class="tb4-shop-card-content" style="padding:16px;">';

            // Title
            $html .= '<h4 class="tb4-shop-card-title" style="font-size:' . esc_attr($titleSize) . ';font-weight:600;color:' . esc_attr($titleColor) . ';margin:0 0 8px 0;">' . esc_html($product['title']) . '</h4>';

            // Price
            $html .= '<div class="tb4-shop-card-price" style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">';
            if ($hasSale) {
                $html .= '<span class="tb4-shop-price-sale" style="font-size:' . esc_attr($priceSize) . ';font-weight:700;color:' . esc_attr($salePriceColor) . ';">' . esc_html($product['sale_price']) . '</span>';
                $html .= '<span class="tb4-shop-price-original" style="font-size:14px;color:' . esc_attr($originalPriceColor) . ';text-decoration:line-through;">' . esc_html($product['price']) . '</span>';
            } else {
                $html .= '<span class="tb4-shop-price-current" style="font-size:' . esc_attr($priceSize) . ';font-weight:700;color:' . esc_attr($priceColor) . ';">' . esc_html($product['price']) . '</span>';
            }
            $html .= '</div>';

            // Add to Cart button
            if ($showAddToCart) {
                $btnWidth = $btnFullWidth ? 'width:100%;' : '';
                $html .= '<button class="tb4-shop-add-btn" style="display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 20px;background:' . esc_attr($btnBg) . ';color:' . esc_attr($btnColor) . ';border:none;border-radius:' . esc_attr($btnRadius) . ';font-size:14px;font-weight:600;cursor:pointer;' . $btnWidth . '">';
                $html .= '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>';
                $html .= esc_html($addToCartText) . '</button>';
            }

            $html .= '</div>'; // Close content
            $html .= '</div>'; // Close card
        }

        $html .= '</div>'; // Close grid
        $html .= '</div>'; // Close preview

        // Add scoped CSS for hover effects
        $html .= '<style>';
        $html .= '#' . $uniqueId . ' .tb4-shop-card.hover-lift:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.15); }';
        $html .= '#' . $uniqueId . ' .tb4-shop-card.hover-zoom:hover .tb4-shop-card-image-placeholder { transform: scale(1.1); }';
        $html .= '#' . $uniqueId . ' .tb4-shop-add-btn:hover { opacity: 0.9; }';
        $html .= '</style>';

        return $html;
    }
}
