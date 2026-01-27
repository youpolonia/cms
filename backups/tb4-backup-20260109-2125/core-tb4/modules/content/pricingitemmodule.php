<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../childmodule.php';

use Core\TB4\Modules\ChildModule;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Pricing Item Module (Child Module)
 * Individual pricing plan card with title, price, features list, and CTA button.
 * Must be nested inside PricingModule parent.
 */
class PricingItemModule extends ChildModule
{
    protected ?string $parent_slug = 'pricing';
    protected ?string $child_title_var = 'title';

    public function __construct()
    {
        $this->name = 'Pricing Plan';
        $this->slug = 'pricing_item';
        $this->icon = 'credit-card';
        $this->category = 'content';
    }

    public function get_content_fields(): array
    {
        return [
            'title' => [
                'type' => 'text',
                'label' => 'Plan Name',
                'default' => 'Basic Plan'
            ],
            'subtitle' => [
                'type' => 'text',
                'label' => 'Subtitle',
                'default' => 'Perfect for individuals'
            ],
            'currency' => [
                'type' => 'text',
                'label' => 'Currency Symbol',
                'default' => '$'
            ],
            'price' => [
                'type' => 'text',
                'label' => 'Price',
                'default' => '29'
            ],
            'price_suffix' => [
                'type' => 'text',
                'label' => 'Price Suffix',
                'default' => '/month'
            ],
            'features' => [
                'type' => 'textarea',
                'label' => 'Features (one per line)',
                'default' => "10 Projects\n5GB Storage\nEmail Support\nBasic Analytics"
            ],
            'button_text' => [
                'type' => 'text',
                'label' => 'Button Text',
                'default' => 'Get Started'
            ],
            'button_url' => [
                'type' => 'text',
                'label' => 'Button URL',
                'default' => '#'
            ],
            'button_target' => [
                'type' => 'select',
                'label' => 'Button Target',
                'options' => [
                    '_self' => 'Same Window',
                    '_blank' => 'New Tab'
                ],
                'default' => '_self'
            ],
            'is_featured' => [
                'type' => 'toggle',
                'label' => 'Featured Plan',
                'default' => false
            ],
            'featured_label' => [
                'type' => 'text',
                'label' => 'Featured Label',
                'default' => 'Most Popular'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'background_color' => [
                'type' => 'color',
                'label' => 'Background Color',
                'default' => '#ffffff'
            ],
            'featured_bg_color' => [
                'type' => 'color',
                'label' => 'Featured Background',
                'default' => '#eff6ff'
            ],
            'border_color' => [
                'type' => 'color',
                'label' => 'Border Color',
                'default' => '#e5e7eb'
            ],
            'featured_border_color' => [
                'type' => 'color',
                'label' => 'Featured Border Color',
                'default' => '#2563eb'
            ],
            'border_radius' => [
                'type' => 'text',
                'label' => 'Border Radius',
                'default' => '12px'
            ],
            'title_font_size' => [
                'type' => 'text',
                'label' => 'Title Font Size',
                'default' => '20px'
            ],
            'title_color' => [
                'type' => 'color',
                'label' => 'Title Color',
                'default' => '#1f2937'
            ],
            'subtitle_color' => [
                'type' => 'color',
                'label' => 'Subtitle Color',
                'default' => '#6b7280'
            ],
            'price_font_size' => [
                'type' => 'text',
                'label' => 'Price Font Size',
                'default' => '48px'
            ],
            'price_color' => [
                'type' => 'color',
                'label' => 'Price Color',
                'default' => '#1f2937'
            ],
            'currency_font_size' => [
                'type' => 'text',
                'label' => 'Currency Font Size',
                'default' => '24px'
            ],
            'suffix_color' => [
                'type' => 'color',
                'label' => 'Suffix Color',
                'default' => '#6b7280'
            ],
            'feature_color' => [
                'type' => 'color',
                'label' => 'Feature Text Color',
                'default' => '#4b5563'
            ],
            'feature_icon_color' => [
                'type' => 'color',
                'label' => 'Feature Icon Color',
                'default' => '#10b981'
            ],
            'button_bg_color' => [
                'type' => 'color',
                'label' => 'Button Background',
                'default' => '#2563eb'
            ],
            'button_text_color' => [
                'type' => 'color',
                'label' => 'Button Text Color',
                'default' => '#ffffff'
            ],
            'button_border_radius' => [
                'type' => 'text',
                'label' => 'Button Border Radius',
                'default' => '8px'
            ],
            'featured_label_bg' => [
                'type' => 'color',
                'label' => 'Featured Label BG',
                'default' => '#2563eb'
            ],
            'featured_label_color' => [
                'type' => 'color',
                'label' => 'Featured Label Color',
                'default' => '#ffffff'
            ],
            'padding' => [
                'type' => 'text',
                'label' => 'Card Padding',
                'default' => '32px'
            ]
        ];
    }

    public function render(array $attrs): string
    {
        // Content fields
        $title = $attrs['title'] ?? 'Basic Plan';
        $subtitle = $attrs['subtitle'] ?? 'Perfect for individuals';
        $currency = $attrs['currency'] ?? '$';
        $price = $attrs['price'] ?? '29';
        $priceSuffix = $attrs['price_suffix'] ?? '/month';
        $featuresText = $attrs['features'] ?? '';
        $features = array_filter(array_map('trim', explode("\n", $featuresText)));
        $buttonText = $attrs['button_text'] ?? 'Get Started';
        $buttonUrl = $attrs['button_url'] ?? '#';
        $buttonTarget = $attrs['button_target'] ?? '_self';
        $isFeatured = !empty($attrs['is_featured']);
        $featuredLabel = $attrs['featured_label'] ?? 'Most Popular';

        // Design fields
        $bgColor = $attrs['background_color'] ?? '#ffffff';
        $featuredBgColor = $attrs['featured_bg_color'] ?? '#eff6ff';
        $borderColor = $attrs['border_color'] ?? '#e5e7eb';
        $featuredBorderColor = $attrs['featured_border_color'] ?? '#2563eb';
        $borderRadius = $attrs['border_radius'] ?? '12px';
        $titleFontSize = $attrs['title_font_size'] ?? '20px';
        $titleColor = $attrs['title_color'] ?? '#1f2937';
        $subtitleColor = $attrs['subtitle_color'] ?? '#6b7280';
        $priceFontSize = $attrs['price_font_size'] ?? '48px';
        $priceColor = $attrs['price_color'] ?? '#1f2937';
        $currencyFontSize = $attrs['currency_font_size'] ?? '24px';
        $suffixColor = $attrs['suffix_color'] ?? '#6b7280';
        $featureColor = $attrs['feature_color'] ?? '#4b5563';
        $featureIconColor = $attrs['feature_icon_color'] ?? '#10b981';
        $buttonBgColor = $attrs['button_bg_color'] ?? '#2563eb';
        $buttonTextColor = $attrs['button_text_color'] ?? '#ffffff';
        $buttonBorderRadius = $attrs['button_border_radius'] ?? '8px';
        $featuredLabelBg = $attrs['featured_label_bg'] ?? '#2563eb';
        $featuredLabelColor = $attrs['featured_label_color'] ?? '#ffffff';
        $padding = $attrs['padding'] ?? '32px';

        // Build container styles
        $containerStyles = [
            'background:' . esc_attr($isFeatured ? $featuredBgColor : $bgColor),
            'border:1px solid ' . esc_attr($isFeatured ? $featuredBorderColor : $borderColor),
            'border-radius:' . esc_attr($borderRadius),
            'padding:' . esc_attr($padding),
            'text-align:center',
            'position:relative',
            'height:100%',
            'box-sizing:border-box',
            'display:flex',
            'flex-direction:column'
        ];

        $featuredClass = $isFeatured ? ' tb4-pricing-item-featured' : '';

        $html = '<div class="tb4-pricing-item' . $featuredClass . '" style="' . implode(';', $containerStyles) . '">';

        // Featured badge
        if ($isFeatured && !empty($featuredLabel)) {
            $badgeStyles = [
                'position:absolute',
                'top:-12px',
                'left:50%',
                'transform:translateX(-50%)',
                'background:' . esc_attr($featuredLabelBg),
                'color:' . esc_attr($featuredLabelColor),
                'padding:4px 16px',
                'border-radius:20px',
                'font-size:12px',
                'font-weight:600',
                'white-space:nowrap'
            ];
            $html .= '<div class="tb4-pricing-item-badge" style="' . implode(';', $badgeStyles) . '">' . esc_html($featuredLabel) . '</div>';
        }

        // Title
        $titleStyles = [
            'font-size:' . esc_attr($titleFontSize),
            'font-weight:600',
            'color:' . esc_attr($titleColor),
            'margin:0 0 8px 0'
        ];
        $html .= '<h4 class="tb4-pricing-item-title" style="' . implode(';', $titleStyles) . '">' . esc_html($title) . '</h4>';

        // Subtitle
        if (!empty($subtitle)) {
            $subtitleStyles = [
                'font-size:14px',
                'color:' . esc_attr($subtitleColor),
                'margin:0 0 20px 0'
            ];
            $html .= '<p class="tb4-pricing-item-subtitle" style="' . implode(';', $subtitleStyles) . '">' . esc_html($subtitle) . '</p>';
        }

        // Price section
        $priceContainerStyles = ['margin-bottom:24px'];
        $html .= '<div class="tb4-pricing-item-price" style="' . implode(';', $priceContainerStyles) . '">';

        // Currency
        $currencyStyles = [
            'font-size:' . esc_attr($currencyFontSize),
            'font-weight:600',
            'color:' . esc_attr($priceColor),
            'vertical-align:top'
        ];
        $html .= '<span class="tb4-pricing-item-currency" style="' . implode(';', $currencyStyles) . '">' . esc_html($currency) . '</span>';

        // Amount
        $amountStyles = [
            'font-size:' . esc_attr($priceFontSize),
            'font-weight:700',
            'color:' . esc_attr($priceColor),
            'line-height:1'
        ];
        $html .= '<span class="tb4-pricing-item-amount" style="' . implode(';', $amountStyles) . '">' . esc_html($price) . '</span>';

        // Suffix
        $suffixStyles = [
            'font-size:16px',
            'color:' . esc_attr($suffixColor)
        ];
        $html .= '<span class="tb4-pricing-item-suffix" style="' . implode(';', $suffixStyles) . '">' . esc_html($priceSuffix) . '</span>';

        $html .= '</div>'; // End price section

        // Features list
        if (!empty($features)) {
            $listStyles = [
                'list-style:none',
                'padding:0',
                'margin:0 0 24px 0',
                'text-align:left',
                'flex:1'
            ];
            $html .= '<ul class="tb4-pricing-item-features" style="' . implode(';', $listStyles) . '">';

            $itemStyles = [
                'padding:10px 0',
                'color:' . esc_attr($featureColor),
                'font-size:14px',
                'border-bottom:1px solid #f3f4f6',
                'display:flex',
                'align-items:center',
                'gap:10px'
            ];

            foreach ($features as $index => $feature) {
                $isLast = ($index === count($features) - 1);
                $currentItemStyles = $itemStyles;
                if ($isLast) {
                    $currentItemStyles[4] = 'border-bottom:none';
                }
                $html .= '<li style="' . implode(';', $currentItemStyles) . '">';
                $html .= '<span class="tb4-pricing-item-check" style="color:' . esc_attr($featureIconColor) . ';font-weight:bold;flex-shrink:0;">✓</span>';
                $html .= '<span>' . esc_html($feature) . '</span>';
                $html .= '</li>';
            }

            $html .= '</ul>';
        }

        // Button
        $buttonStyles = [
            'display:block',
            'width:100%',
            'padding:14px 24px',
            'background:' . esc_attr($buttonBgColor),
            'color:' . esc_attr($buttonTextColor),
            'text-decoration:none',
            'border-radius:' . esc_attr($buttonBorderRadius),
            'font-weight:600',
            'font-size:16px',
            'text-align:center',
            'box-sizing:border-box',
            'transition:opacity 0.2s,transform 0.2s',
            'margin-top:auto'
        ];
        $html .= '<a href="' . esc_attr($buttonUrl) . '" target="' . esc_attr($buttonTarget) . '" class="tb4-pricing-item-button" style="' . implode(';', $buttonStyles) . '">' . esc_html($buttonText) . '</a>';

        $html .= '</div>'; // End card

        return $html;
    }
}
