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
            'button_url' => [
                'label' => 'Button URL',
                'type' => 'text'
            ],
            'url_new_window' => [
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
        $title = $this->esc($attrs['title'] ?? 'Basic Plan');
        $subtitle = $this->esc($attrs['subtitle'] ?? '');
        $currency = $this->esc($attrs['currency'] ?? '$');
        $price = $this->esc($attrs['price'] ?? '29');
        $per = $this->esc($attrs['per'] ?? 'month');
        $features = $attrs['content'] ?? '';
        $buttonText = $this->esc($attrs['button_text'] ?? 'Sign Up');
        $buttonUrl = $attrs['button_url'] ?? '#';
        $newWindow = !empty($attrs['url_new_window']) ? ' target="_blank" rel="noopener"' : '';
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

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Container
        $bodyBg = $attrs['body_bg_color'] ?? '#ffffff';
        $css .= $selector . ' .jtb-pricing-table-container { ';
        $css .= 'background-color: ' . $bodyBg . '; ';
        $css .= 'text-align: center; ';
        $css .= 'position: relative; ';
        $css .= 'overflow: hidden; ';
        $css .= '}' . "\n";

        // Featured badge
        $badgeBg = $attrs['featured_badge_bg'] ?? '#ff6b35';
        $badgeText = $attrs['featured_badge_text'] ?? '#ffffff';
        $css .= $selector . ' .jtb-pricing-badge { ';
        $css .= 'position: absolute; top: 15px; right: -35px; ';
        $css .= 'background: ' . $badgeBg . '; ';
        $css .= 'color: ' . $badgeText . '; ';
        $css .= 'padding: 5px 40px; ';
        $css .= 'font-size: 12px; ';
        $css .= 'font-weight: bold; ';
        $css .= 'transform: rotate(45deg); ';
        $css .= 'text-transform: uppercase; ';
        $css .= '}' . "\n";

        // Header
        $headerBg = $attrs['header_bg_color'] ?? '#2ea3f2';
        $headerText = $attrs['header_text_color'] ?? '#ffffff';
        $css .= $selector . ' .jtb-pricing-header { ';
        $css .= 'background-color: ' . $headerBg . '; ';
        $css .= 'color: ' . $headerText . '; ';
        $css .= 'padding: 30px 20px; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-pricing-title { margin: 0; font-size: 24px; }' . "\n";
        $css .= $selector . ' .jtb-pricing-subtitle { opacity: 0.8; margin-top: 5px; }' . "\n";

        // Price
        $priceColor = $attrs['price_color'] ?? '#2ea3f2';
        $css .= $selector . ' .jtb-pricing-price-wrap { ';
        $css .= 'padding: 30px 20px; ';
        $css .= 'color: ' . $priceColor . '; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-pricing-currency { font-size: 24px; vertical-align: super; }' . "\n";
        $css .= $selector . ' .jtb-pricing-price { font-size: 60px; font-weight: bold; line-height: 1; }' . "\n";
        $css .= $selector . ' .jtb-pricing-per { font-size: 16px; opacity: 0.7; }' . "\n";

        // Features
        $bulletColor = $attrs['bullet_color'] ?? '#2ea3f2';
        $css .= $selector . ' .jtb-pricing-features { padding: 0 20px 20px; }' . "\n";
        $css .= $selector . ' .jtb-pricing-features ul { list-style: none; padding: 0; margin: 0; }' . "\n";
        $css .= $selector . ' .jtb-pricing-features li { padding: 10px 0; border-bottom: 1px solid #eee; }' . "\n";
        $css .= $selector . ' .jtb-pricing-features li:last-child { border-bottom: none; }' . "\n";
        // Use CSS for checkmark instead of unicode character
        $css .= $selector . ' .jtb-pricing-features li { position: relative; padding-left: 24px; }' . "\n";
        $css .= $selector . ' .jtb-pricing-features li::before { content: ""; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; background-color: ' . $bulletColor . '; -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'white\' stroke-width=\'3\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3Cpolyline points=\'20 6 9 17 4 12\'%3E%3C/polyline%3E%3C/svg%3E") center/contain no-repeat; mask: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'white\' stroke-width=\'3\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3Cpolyline points=\'20 6 9 17 4 12\'%3E%3C/polyline%3E%3C/svg%3E") center/contain no-repeat; }' . "\n";

        // Button
        $buttonBg = $attrs['button_bg_color'] ?? '#2ea3f2';
        $buttonText = $attrs['button_text_color'] ?? '#ffffff';
        $css .= $selector . ' .jtb-pricing-footer { padding: 20px; }' . "\n";
        $css .= $selector . ' .jtb-pricing-button { ';
        $css .= 'display: inline-block; ';
        $css .= 'background-color: ' . $buttonBg . '; ';
        $css .= 'color: ' . $buttonText . '; ';
        $css .= 'padding: 12px 30px; ';
        $css .= 'text-decoration: none; ';
        $css .= 'border-radius: 3px; ';
        $css .= 'transition: all 0.3s ease; ';
        $css .= '}' . "\n";

        // Button hover
        if (!empty($attrs['button_bg_color__hover'])) {
            $css .= $selector . ' .jtb-pricing-button:hover { background-color: ' . $attrs['button_bg_color__hover'] . '; }' . "\n";
        }
        if (!empty($attrs['button_text_color__hover'])) {
            $css .= $selector . ' .jtb-pricing-button:hover { color: ' . $attrs['button_text_color__hover'] . '; }' . "\n";
        }

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('pricing_table', JTB_Module_PricingTable::class);
