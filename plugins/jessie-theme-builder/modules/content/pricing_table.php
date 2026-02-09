<?php
/**
 * Pricing Table Module
 * Pricing plan with features list and CTA button
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_PricingTable extends JTB_Element
{
    public string $icon = 'credit-card';
    public string $category = 'content';
    public string $child_slug = 'pricing_table_item';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = true;
    public bool $use_position = false;
    public bool $use_filters = false;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'pricing';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        // Header
        'header_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-pricing-header'
        ],
        'header_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-pricing-header'
        ],
        // Price
        'price_color' => [
            'property' => 'color',
            'selector' => '.jtb-pricing-price-wrap'
        ],
        // Body
        'body_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-pricing-table-container'
        ],
        // Bullet
        'bullet_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-pricing-features li::before'
        ],
        // Button
        'button_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-pricing-button',
            'hover' => true
        ],
        'button_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-pricing-button',
            'hover' => true
        ],
        // Featured badge
        'featured_badge_bg' => [
            'property' => 'background-color',
            'selector' => '.jtb-pricing-badge'
        ],
        'featured_badge_text' => [
            'property' => 'color',
            'selector' => '.jtb-pricing-badge'
        ]
    ];

    public function getSlug(): string
    {
        return 'pricing_table';
    }

    public function getName(): string
    {
        return 'Pricing Table';
    }

    public function getFields(): array
    {
        return [
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Basic Plan'
            ],
            'subtitle' => [
                'label' => 'Subtitle',
                'type' => 'text',
                'default' => 'For individuals'
            ],
            'currency' => [
                'label' => 'Currency',
                'type' => 'text',
                'default' => '$'
            ],
            'price' => [
                'label' => 'Price',
                'type' => 'text',
                'default' => '29'
            ],
            'per' => [
                'label' => 'Per Period',
                'type' => 'text',
                'default' => 'month'
            ],
            'content' => [
                'label' => 'Features (HTML list)',
                'type' => 'richtext',
                'default' => '<ul><li>Feature One</li><li>Feature Two</li><li>Feature Three</li></ul>'
            ],
            'button_text' => [
                'label' => 'Button Text',
                'type' => 'text',
                'default' => 'Sign Up'
            ],
            'link_url' => [
                'label' => 'Button URL',
                'type' => 'text'
            ],
            'link_target' => [
                'label' => 'Open in New Tab',
                'type' => 'toggle',
                'default' => false
            ],
            'featured' => [
                'label' => 'Featured Plan',
                'type' => 'toggle',
                'default' => false
            ],
            'featured_text' => [
                'label' => 'Featured Badge Text',
                'type' => 'text',
                'default' => 'Most Popular',
                'show_if' => ['featured' => true]
            ],
            // Styling
            'header_bg_color' => [
                'label' => 'Header Background',
                'type' => 'color',
                'default' => '#2ea3f2'
            ],
            'header_text_color' => [
                'label' => 'Header Text Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'price_color' => [
                'label' => 'Price Color',
                'type' => 'color',
                'default' => '#2ea3f2'
            ],
            'body_bg_color' => [
                'label' => 'Body Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'bullet_color' => [
                'label' => 'Bullet/Check Color',
                'type' => 'color',
                'default' => '#2ea3f2'
            ],
            'button_bg_color' => [
                'label' => 'Button Background',
                'type' => 'color',
                'default' => '#2ea3f2',
                'hover' => true
            ],
            'button_text_color' => [
                'label' => 'Button Text Color',
                'type' => 'color',
                'default' => '#ffffff',
                'hover' => true
            ],
            'featured_badge_bg' => [
                'label' => 'Featured Badge Background',
                'type' => 'color',
                'default' => '#ff6b35',
                'show_if' => ['featured' => true]
            ],
            'featured_badge_text' => [
                'label' => 'Featured Badge Text Color',
                'type' => 'color',
                'default' => '#ffffff',
                'show_if' => ['featured' => true]
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $title = $this->esc($attrs['title'] ?? 'Basic Plan');
        $subtitle = $this->esc($attrs['subtitle'] ?? '');
        $currency = $this->esc($attrs['currency'] ?? '$');
        $price = $this->esc($attrs['price'] ?? '29');
        $per = $this->esc($attrs['per'] ?? 'month');
        $features = $attrs['content'] ?? '';
        $buttonText = $this->esc($attrs['button_text'] ?? 'Sign Up');
        $buttonUrl = $attrs['link_url'] ?? '#';
        $newWindow = !empty($attrs['link_target']) ? ' target="_blank" rel="noopener"' : '';
        $featured = !empty($attrs['featured']);
        $featuredText = $this->esc($attrs['featured_text'] ?? 'Most Popular');

        $containerClass = 'jtb-pricing-table-container' . ($featured ? ' jtb-pricing-featured' : '');

        $innerHtml = '<div class="' . $containerClass . '">';

        // Featured badge
        if ($featured) {
            $innerHtml .= '<div class="jtb-pricing-badge">' . $featuredText . '</div>';
        }

        // Header
        $innerHtml .= '<div class="jtb-pricing-header">';
        $innerHtml .= '<h3 class="jtb-pricing-title">' . $title . '</h3>';
        if (!empty($subtitle)) {
            $innerHtml .= '<div class="jtb-pricing-subtitle">' . $subtitle . '</div>';
        }
        $innerHtml .= '</div>';

        // Price
        $innerHtml .= '<div class="jtb-pricing-price-wrap">';
        $innerHtml .= '<span class="jtb-pricing-currency">' . $currency . '</span>';
        $innerHtml .= '<span class="jtb-pricing-price">' . $price . '</span>';
        if (!empty($per)) {
            $innerHtml .= '<span class="jtb-pricing-per">/' . $per . '</span>';
        }
        $innerHtml .= '</div>';

        // Features
        $innerHtml .= '<div class="jtb-pricing-features">' . $features . '</div>';

        // Button
        $innerHtml .= '<div class="jtb-pricing-footer">';
        $innerHtml .= '<a class="jtb-pricing-button jtb-button" href="' . $this->esc($buttonUrl) . '"' . $newWindow . '>' . $buttonText . '</a>';
        $innerHtml .= '</div>';

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for Pricing Table module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('pricing_table', JTB_Module_PricingTable::class);
