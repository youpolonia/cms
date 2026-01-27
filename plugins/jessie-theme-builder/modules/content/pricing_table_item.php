<?php
/**
 * Pricing Table Item Module (Child)
 * Single pricing plan in a pricing tables group
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_PricingTableItem extends JTB_Element
{
    public string $icon = 'tag';
    public string $category = 'content';
    public bool $is_child = true;

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
        return 'pricing_table_item';
    }

    public function getName(): string
    {
        return 'Pricing Plan';
    }

    public function getFields(): array
    {
        return [
            'title' => [
                'label' => 'Plan Title',
                'type' => 'text',
                'default' => 'Basic Plan'
            ],
            'subtitle' => [
                'label' => 'Subtitle',
                'type' => 'text',
                'default' => 'For individuals'
            ],
            'currency' => [
                'label' => 'Currency Symbol',
                'type' => 'text',
                'default' => '$'
            ],
            'sum' => [
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
                'label' => 'Features (one per line)',
                'type' => 'textarea',
                'default' => "Unlimited Users\n10GB Storage\nEmail Support\nBasic Analytics"
            ],
            'button_text' => [
                'label' => 'Button Text',
                'type' => 'text',
                'default' => 'Sign Up'
            ],
            'button_url' => [
                'label' => 'Button URL',
                'type' => 'url',
                'default' => '#'
            ],
            'url_new_window' => [
                'label' => 'Open in New Tab',
                'type' => 'toggle',
                'default' => false
            ],
            'featured' => [
                'label' => 'Featured/Highlighted',
                'type' => 'toggle',
                'default' => false
            ],
            // Styling
            'header_background_color' => [
                'label' => 'Header Background',
                'type' => 'color',
                'default' => '#7c3aed'
            ],
            'header_text_color' => [
                'label' => 'Header Text Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'price_color' => [
                'label' => 'Price Color',
                'type' => 'color',
                'default' => '#7c3aed'
            ],
            'body_background_color' => [
                'label' => 'Body Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'bullet_color' => [
                'label' => 'Check Icon Color',
                'type' => 'color',
                'default' => '#10b981'
            ],
            'button_background_color' => [
                'label' => 'Button Background',
                'type' => 'color',
                'default' => '#7c3aed',
                'hover' => true
            ],
            'button_text_color' => [
                'label' => 'Button Text Color',
                'type' => 'color',
                'default' => '#ffffff',
                'hover' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $title = $this->esc($attrs['title'] ?? 'Basic Plan');
        $subtitle = $this->esc($attrs['subtitle'] ?? '');
        $currency = $this->esc($attrs['currency'] ?? '$');
        $price = $this->esc($attrs['sum'] ?? '29');
        $per = $this->esc($attrs['per'] ?? 'month');
        $featuresText = $attrs['content'] ?? '';
        $buttonText = $this->esc($attrs['button_text'] ?? 'Sign Up');
        $buttonUrl = $attrs['button_url'] ?? '#';
        $newWindow = !empty($attrs['url_new_window']) ? ' target="_blank" rel="noopener"' : '';
        $featured = !empty($attrs['featured']);

        // Parse features from text (one per line)
        $features = array_filter(array_map('trim', explode("\n", $featuresText)));

        $containerClass = 'jtb-pricing-item' . ($featured ? ' jtb-pricing-featured' : '');

        $html = '<div class="' . $containerClass . '">';

        // Featured badge
        if ($featured) {
            $html .= '<div class="jtb-pricing-badge">Most Popular</div>';
        }

        // Header
        $html .= '<div class="jtb-pricing-header">';
        $html .= '<h3 class="jtb-pricing-title">' . $title . '</h3>';
        if (!empty($subtitle)) {
            $html .= '<div class="jtb-pricing-subtitle">' . $subtitle . '</div>';
        }
        $html .= '</div>';

        // Price
        $html .= '<div class="jtb-pricing-price-wrap">';
        $html .= '<span class="jtb-pricing-currency">' . $currency . '</span>';
        $html .= '<span class="jtb-pricing-amount">' . $price . '</span>';
        if (!empty($per)) {
            $html .= '<span class="jtb-pricing-per">/' . $per . '</span>';
        }
        $html .= '</div>';

        // Features list
        if (!empty($features)) {
            $html .= '<ul class="jtb-pricing-features">';
            foreach ($features as $feature) {
                $excluded = strpos($feature, '-') === 0;
                if ($excluded) {
                    $feature = ltrim($feature, '- ');
                    $html .= '<li class="jtb-feature-excluded">' . $this->esc($feature) . '</li>';
                } else {
                    $html .= '<li>' . $this->esc($feature) . '</li>';
                }
            }
            $html .= '</ul>';
        }

        // Button
        $html .= '<div class="jtb-pricing-footer">';
        $html .= '<a class="jtb-pricing-button" href="' . $this->esc($buttonUrl) . '"' . $newWindow . '>' . $buttonText . '</a>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);

        $headerBg = $attrs['header_background_color'] ?? '#7c3aed';
        $headerText = $attrs['header_text_color'] ?? '#ffffff';
        $priceColor = $attrs['price_color'] ?? '#7c3aed';
        $bodyBg = $attrs['body_background_color'] ?? '#ffffff';
        $bulletColor = $attrs['bullet_color'] ?? '#10b981';
        $buttonBg = $attrs['button_background_color'] ?? '#7c3aed';
        $buttonText = $attrs['button_text_color'] ?? '#ffffff';

        // Item container
        $css .= $selector . ' { ';
        $css .= 'background: ' . $bodyBg . '; ';
        $css .= 'border-radius: 12px; ';
        $css .= 'overflow: hidden; ';
        $css .= 'text-align: center; ';
        $css .= 'position: relative; ';
        $css .= 'box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); ';
        $css .= 'transition: transform 0.3s ease, box-shadow 0.3s ease; ';
        $css .= '}' . "\n";

        $css .= $selector . ':hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }' . "\n";

        // Featured
        $css .= $selector . '.jtb-pricing-featured { transform: scale(1.05); z-index: 1; }' . "\n";
        $css .= $selector . '.jtb-pricing-featured:hover { transform: scale(1.05) translateY(-5px); }' . "\n";

        // Badge
        $css .= $selector . ' .jtb-pricing-badge { ';
        $css .= 'position: absolute; top: 12px; right: -30px; ';
        $css .= 'background: #f59e0b; color: white; ';
        $css .= 'padding: 4px 40px; font-size: 11px; font-weight: 600; ';
        $css .= 'transform: rotate(45deg); text-transform: uppercase; ';
        $css .= '}' . "\n";

        // Header
        $css .= $selector . ' .jtb-pricing-header { ';
        $css .= 'background: ' . $headerBg . '; color: ' . $headerText . '; ';
        $css .= 'padding: 24px 20px; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-pricing-title { margin: 0; font-size: 20px; font-weight: 700; }' . "\n";
        $css .= $selector . ' .jtb-pricing-subtitle { opacity: 0.85; margin-top: 4px; font-size: 14px; }' . "\n";

        // Price
        $css .= $selector . ' .jtb-pricing-price-wrap { ';
        $css .= 'padding: 24px 20px; color: ' . $priceColor . '; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-pricing-currency { font-size: 20px; vertical-align: top; }' . "\n";
        $css .= $selector . ' .jtb-pricing-amount { font-size: 48px; font-weight: 800; line-height: 1; }' . "\n";
        $css .= $selector . ' .jtb-pricing-per { font-size: 14px; opacity: 0.6; color: #666; }' . "\n";

        // Features
        $css .= $selector . ' .jtb-pricing-features { ';
        $css .= 'list-style: none; padding: 0 24px; margin: 0; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-pricing-features li { ';
        $css .= 'padding: 12px 0; border-bottom: 1px solid #f1f5f9; ';
        $css .= 'position: relative; padding-left: 28px; text-align: left; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-pricing-features li:last-child { border-bottom: none; }' . "\n";

        // Checkmark icon
        $css .= $selector . ' .jtb-pricing-features li::before { ';
        $css .= 'content: ""; position: absolute; left: 0; top: 50%; transform: translateY(-50%); ';
        $css .= 'width: 18px; height: 18px; background: ' . $bulletColor . '; border-radius: 50%; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-pricing-features li::after { ';
        $css .= 'content: ""; position: absolute; left: 6px; top: 50%; transform: translateY(-60%) rotate(45deg); ';
        $css .= 'width: 4px; height: 8px; border: solid white; border-width: 0 2px 2px 0; ';
        $css .= '}' . "\n";

        // Excluded feature
        $css .= $selector . ' .jtb-pricing-features li.jtb-feature-excluded { color: #9ca3af; text-decoration: line-through; }' . "\n";
        $css .= $selector . ' .jtb-pricing-features li.jtb-feature-excluded::before { background: #e5e7eb; }' . "\n";
        $css .= $selector . ' .jtb-pricing-features li.jtb-feature-excluded::after { ';
        $css .= 'transform: translateY(-50%) rotate(45deg); width: 10px; height: 2px; left: 4px; ';
        $css .= 'border: none; background: #9ca3af; ';
        $css .= '}' . "\n";

        // Footer/Button
        $css .= $selector . ' .jtb-pricing-footer { padding: 24px; }' . "\n";

        $css .= $selector . ' .jtb-pricing-button { ';
        $css .= 'display: inline-block; width: 100%; padding: 14px 24px; ';
        $css .= 'background: ' . $buttonBg . '; color: ' . $buttonText . '; ';
        $css .= 'text-decoration: none; border-radius: 8px; font-weight: 600; ';
        $css .= 'transition: all 0.2s ease; ';
        $css .= '}' . "\n";

        // Button hover
        $hoverBg = $attrs['button_background_color__hover'] ?? '';
        $hoverText = $attrs['button_text_color__hover'] ?? '';
        if ($hoverBg) {
            $css .= $selector . ' .jtb-pricing-button:hover { background: ' . $hoverBg . '; }' . "\n";
        } else {
            $css .= $selector . ' .jtb-pricing-button:hover { filter: brightness(1.1); }' . "\n";
        }
        if ($hoverText) {
            $css .= $selector . ' .jtb-pricing-button:hover { color: ' . $hoverText . '; }' . "\n";
        }

        return $css;
    }
}

JTB_Registry::register('pricing_table_item', JTB_Module_PricingTableItem::class);
