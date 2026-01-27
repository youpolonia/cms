<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Pricing Table Module
 * Displays a single pricing card with plan name, price, features list, and CTA button
 */
class PricingModule extends Module
{
    protected string $type = 'parent';
    protected ?string $child_slug = 'pricing_item';

    public function __construct()
    {
        $this->name = 'Pricing Table';
        $this->slug = 'pricing';
        $this->icon = 'credit-card';
        $this->category = 'commerce';

        $this->elements = [
            'main' => '.tb4-pricing-card',
            'badge' => '.tb4-pricing-badge',
            'header' => '.tb4-pricing-header',
            'plan' => '.tb4-pricing-plan',
            'description' => '.tb4-pricing-description',
            'price' => '.tb4-pricing-price',
            'currency' => '.tb4-pricing-currency',
            'amount' => '.tb4-pricing-amount',
            'period' => '.tb4-pricing-period',
            'features' => '.tb4-pricing-features',
            'feature_item' => '.tb4-pricing-features li',
            'feature_icon' => '.tb4-feature-icon',
            'button' => '.tb4-pricing-button'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'plan_name' => [
                'label' => 'Plan Name',
                'type' => 'text',
                'default' => 'Professional'
            ],
            'price' => [
                'label' => 'Price',
                'type' => 'text',
                'default' => '49'
            ],
            'currency' => [
                'label' => 'Currency Symbol',
                'type' => 'text',
                'default' => '$'
            ],
            'period' => [
                'label' => 'Billing Period',
                'type' => 'text',
                'default' => '/month'
            ],
            'description' => [
                'label' => 'Description',
                'type' => 'text',
                'default' => 'Perfect for growing businesses'
            ],
            'features' => [
                'label' => 'Features (one per line)',
                'type' => 'textarea',
                'default' => "10 Projects\n50GB Storage\nUnlimited Users\nPriority Support\nAPI Access"
            ],
            'button_text' => [
                'label' => 'Button Text',
                'type' => 'text',
                'default' => 'Get Started'
            ],
            'button_url' => [
                'label' => 'Button URL',
                'type' => 'text',
                'default' => '#'
            ],
            'button_target' => [
                'label' => 'Button Target',
                'type' => 'select',
                'options' => [
                    '_self' => 'Same Window',
                    '_blank' => 'New Tab'
                ],
                'default' => '_self'
            ],
            'is_featured' => [
                'label' => 'Featured/Popular',
                'type' => 'select',
                'options' => [
                    'no' => 'No',
                    'yes' => 'Yes'
                ],
                'default' => 'no'
            ],
            'featured_text' => [
                'label' => 'Featured Badge Text',
                'type' => 'text',
                'default' => 'Most Popular'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'background_color' => [
                'label' => 'Background Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'border_color' => [
                'label' => 'Border Color',
                'type' => 'color',
                'default' => '#e5e7eb'
            ],
            'border_width' => [
                'label' => 'Border Width',
                'type' => 'text',
                'default' => '1px'
            ],
            'border_radius' => [
                'label' => 'Border Radius',
                'type' => 'text',
                'default' => '12px'
            ],
            'box_shadow' => [
                'label' => 'Box Shadow',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'sm' => 'Small',
                    'md' => 'Medium',
                    'lg' => 'Large'
                ],
                'default' => 'md'
            ],
            'padding' => [
                'label' => 'Padding',
                'type' => 'text',
                'default' => '32px'
            ],
            'plan_name_color' => [
                'label' => 'Plan Name Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'plan_name_size' => [
                'label' => 'Plan Name Size',
                'type' => 'text',
                'default' => '24px'
            ],
            'price_color' => [
                'label' => 'Price Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'price_size' => [
                'label' => 'Price Size',
                'type' => 'text',
                'default' => '48px'
            ],
            'currency_size' => [
                'label' => 'Currency Size',
                'type' => 'text',
                'default' => '24px'
            ],
            'period_color' => [
                'label' => 'Period Color',
                'type' => 'color',
                'default' => '#6b7280'
            ],
            'description_color' => [
                'label' => 'Description Color',
                'type' => 'color',
                'default' => '#6b7280'
            ],
            'feature_color' => [
                'label' => 'Feature Text Color',
                'type' => 'color',
                'default' => '#374151'
            ],
            'feature_icon_color' => [
                'label' => 'Feature Icon Color',
                'type' => 'color',
                'default' => '#10b981'
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
            'featured_bg_color' => [
                'label' => 'Featured Badge BG',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'featured_text_color' => [
                'label' => 'Featured Badge Text',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'text_align' => [
                'label' => 'Text Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center'
                ],
                'default' => 'center'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    public function render(array $settings): string
    {
        // Content settings
        $planName = $settings['plan_name'] ?? 'Professional';
        $price = $settings['price'] ?? '49';
        $currency = $settings['currency'] ?? '$';
        $period = $settings['period'] ?? '/month';
        $description = $settings['description'] ?? '';
        $featuresText = $settings['features'] ?? '';
        $features = array_filter(array_map('trim', explode("\n", $featuresText)));
        $buttonText = $settings['button_text'] ?? 'Get Started';
        $buttonUrl = $settings['button_url'] ?? '#';
        $buttonTarget = $settings['button_target'] ?? '_self';
        $isFeatured = ($settings['is_featured'] ?? 'no') === 'yes';
        $featuredText = $settings['featured_text'] ?? 'Most Popular';

        // Design settings
        $bgColor = $settings['background_color'] ?? '#ffffff';
        $borderColor = $settings['border_color'] ?? '#e5e7eb';
        $borderWidth = $settings['border_width'] ?? '1px';
        $borderRadius = $settings['border_radius'] ?? '12px';
        $boxShadow = $settings['box_shadow'] ?? 'md';
        $padding = $settings['padding'] ?? '32px';
        $planNameColor = $settings['plan_name_color'] ?? '#111827';
        $planNameSize = $settings['plan_name_size'] ?? '24px';
        $priceColor = $settings['price_color'] ?? '#111827';
        $priceSize = $settings['price_size'] ?? '48px';
        $currencySize = $settings['currency_size'] ?? '24px';
        $periodColor = $settings['period_color'] ?? '#6b7280';
        $descriptionColor = $settings['description_color'] ?? '#6b7280';
        $featureColor = $settings['feature_color'] ?? '#374151';
        $featureIconColor = $settings['feature_icon_color'] ?? '#10b981';
        $buttonBgColor = $settings['button_bg_color'] ?? '#2563eb';
        $buttonTextColor = $settings['button_text_color'] ?? '#ffffff';
        $buttonBorderRadius = $settings['button_border_radius'] ?? '8px';
        $featuredBgColor = $settings['featured_bg_color'] ?? '#2563eb';
        $featuredTextColor = $settings['featured_text_color'] ?? '#ffffff';
        $textAlign = $settings['text_align'] ?? 'center';

        // Box shadow mapping
        $shadowValues = [
            'none' => 'none',
            'sm' => '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
            'md' => '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
            'lg' => '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)'
        ];
        $shadow = $shadowValues[$boxShadow] ?? $shadowValues['md'];

        // Container styles
        $containerStyles = [
            'background:' . esc_attr($bgColor),
            'border:' . esc_attr($borderWidth) . ' solid ' . esc_attr($borderColor),
            'border-radius:' . esc_attr($borderRadius),
            'padding:' . esc_attr($padding),
            'text-align:' . esc_attr($textAlign),
            'position:relative',
            'max-width:360px',
            'margin:0 auto'
        ];
        if ($shadow !== 'none') {
            $containerStyles[] = 'box-shadow:' . $shadow;
        }
        if ($isFeatured) {
            $containerStyles[] = 'border-color:' . esc_attr($featuredBgColor);
        }

        // Build HTML
        $html = '<div class="tb4-pricing-card' . ($isFeatured ? ' tb4-pricing-featured' : '') . '" style="' . implode(';', $containerStyles) . '">';

        // Featured badge
        if ($isFeatured) {
            $badgeStyles = [
                'position:absolute',
                'top:-12px',
                'left:50%',
                'transform:translateX(-50%)',
                'background:' . esc_attr($featuredBgColor),
                'color:' . esc_attr($featuredTextColor),
                'padding:4px 16px',
                'border-radius:20px',
                'font-size:12px',
                'font-weight:600',
                'white-space:nowrap'
            ];
            $html .= '<div class="tb4-pricing-badge" style="' . implode(';', $badgeStyles) . '">' . esc_html($featuredText) . '</div>';
        }

        // Header section
        $html .= '<div class="tb4-pricing-header">';

        // Plan name
        $planStyles = [
            'font-size:' . esc_attr($planNameSize),
            'font-weight:700',
            'color:' . esc_attr($planNameColor),
            'margin:0 0 8px 0'
        ];
        $html .= '<h3 class="tb4-pricing-plan" style="' . implode(';', $planStyles) . '">' . esc_html($planName) . '</h3>';

        // Description
        if (!empty($description)) {
            $descStyles = [
                'font-size:14px',
                'color:' . esc_attr($descriptionColor),
                'margin:0 0 20px 0'
            ];
            $html .= '<p class="tb4-pricing-description" style="' . implode(';', $descStyles) . '">' . esc_html($description) . '</p>';
        }

        $html .= '</div>'; // End header

        // Price section
        $priceContainerStyles = ['margin-bottom:24px'];
        $html .= '<div class="tb4-pricing-price" style="' . implode(';', $priceContainerStyles) . '">';

        // Currency
        $currencyStyles = [
            'font-size:' . esc_attr($currencySize),
            'font-weight:600',
            'color:' . esc_attr($priceColor),
            'vertical-align:top'
        ];
        $html .= '<span class="tb4-pricing-currency" style="' . implode(';', $currencyStyles) . '">' . esc_html($currency) . '</span>';

        // Amount
        $amountStyles = [
            'font-size:' . esc_attr($priceSize),
            'font-weight:700',
            'color:' . esc_attr($priceColor),
            'line-height:1'
        ];
        $html .= '<span class="tb4-pricing-amount" style="' . implode(';', $amountStyles) . '">' . esc_html($price) . '</span>';

        // Period
        $periodStyles = [
            'font-size:16px',
            'color:' . esc_attr($periodColor)
        ];
        $html .= '<span class="tb4-pricing-period" style="' . implode(';', $periodStyles) . '">' . esc_html($period) . '</span>';

        $html .= '</div>'; // End price

        // Features list
        if (!empty($features)) {
            $listStyles = [
                'list-style:none',
                'padding:0',
                'margin:0 0 24px 0',
                'text-align:left'
            ];
            $html .= '<ul class="tb4-pricing-features" style="' . implode(';', $listStyles) . '">';

            $itemStyles = [
                'padding:8px 0',
                'color:' . esc_attr($featureColor),
                'font-size:14px',
                'border-bottom:1px solid #f3f4f6',
                'display:flex',
                'align-items:center',
                'gap:8px'
            ];

            foreach ($features as $index => $feature) {
                $isLast = ($index === count($features) - 1);
                $currentItemStyles = $itemStyles;
                if ($isLast) {
                    $currentItemStyles[3] = 'border-bottom:none';
                }
                $html .= '<li style="' . implode(';', $currentItemStyles) . '">';
                $html .= '<span class="tb4-feature-icon" style="color:' . esc_attr($featureIconColor) . ';font-weight:bold;">✓</span> ';
                $html .= esc_html($feature);
                $html .= '</li>';
            }

            $html .= '</ul>';
        }

        // Button
        $buttonStyles = [
            'display:block',
            'width:100%',
            'padding:12px 24px',
            'background:' . esc_attr($buttonBgColor),
            'color:' . esc_attr($buttonTextColor),
            'text-decoration:none',
            'border-radius:' . esc_attr($buttonBorderRadius),
            'font-weight:600',
            'font-size:16px',
            'text-align:center',
            'box-sizing:border-box',
            'transition:opacity 0.2s'
        ];
        $html .= '<a href="' . esc_attr($buttonUrl) . '" target="' . esc_attr($buttonTarget) . '" class="tb4-pricing-button" style="' . implode(';', $buttonStyles) . '">' . esc_html($buttonText) . '</a>';

        $html .= '</div>'; // End card

        return $html;
    }
}
