<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Social Links Module
 * Displays social media icon links with customizable styling
 */
class SocialModule extends Module
{
    /**
     * Social network SVG paths (24x24 viewBox)
     */
    private array $social_icons = [];

    /**
     * Social network brand colors
     */
    private array $brand_colors = [
        'facebook' => '#1877F2',
        'twitter' => '#000000',
        'instagram' => '#E4405F',
        'linkedin' => '#0A66C2',
        'youtube' => '#FF0000',
        'tiktok' => '#000000',
        'pinterest' => '#E60023',
        'github' => '#181717',
        'dribbble' => '#EA4C89',
        'behance' => '#1769FF',
        'discord' => '#5865F2',
        'telegram' => '#26A5E4',
        'whatsapp' => '#25D366',
        'email' => '#333333',
        'website' => '#333333'
    ];

    public function __construct()
    {
        $this->name = 'Social Links';
        $this->slug = 'social';
        $this->icon = 'Share2';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-social',
            'wrapper' => '.tb4-social__wrapper',
            'item' => '.tb4-social__item',
            'icon' => '.tb4-social__icon',
            'label' => '.tb4-social__label'
        ];

        // Initialize social icon SVG paths
        $this->init_social_icons();
    }

    /**
     * Initialize social network SVG paths
     */
    private function init_social_icons(): void
    {
        $this->social_icons = [
            'facebook' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
            'twitter' => '<path d="M4 4l11.733 16h4.267l-11.733 -16z"/><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772"/>',
            'instagram' => '<rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>',
            'linkedin' => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/>',
            'youtube' => '<path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/>',
            'tiktok' => '<path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/>',
            'pinterest' => '<line x1="12" x2="12" y1="17" y2="22"/><path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"/><polygon points="12 15 17 10 12 5 7 10 12 15"/>',
            'github' => '<path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"/><path d="M9 18c-4.51 2-5-2-7-2"/>',
            'dribbble' => '<circle cx="12" cy="12" r="10"/><path d="M19.13 5.09C15.22 9.14 10 10.44 2.25 10.94"/><path d="M21.75 12.84c-6.62-1.41-12.14 1-16.38 6.32"/><path d="M8.56 2.75c4.37 6 6 9.42 8 17.72"/>',
            'behance' => '<path d="M1 9h6v6H1zM1 3h6v6H1zM9 9h6v6H9zM9 3h6v6H9zM17 9h6v6h-6zM17 3h6v6h-6z"/>',
            'discord' => '<path d="M18 9h-5"/><path d="M11 9H6"/><path d="m9 12 3 3 3-3"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>',
            'telegram' => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
            'whatsapp' => '<path d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9L3 21"/><path d="M9 10a.5.5 0 0 0 1 0V9a.5.5 0 0 0-1 0v1a5 5 0 0 0 5 5h1a.5.5 0 0 0 0-1h-1a.5.5 0 0 0 0 1"/>',
            'email' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
            'website' => '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'facebook_url' => [
                'label' => 'Facebook URL',
                'type' => 'text',
                'default' => '',
                'description' => 'Full Facebook profile or page URL'
            ],
            'twitter_url' => [
                'label' => 'X (Twitter) URL',
                'type' => 'text',
                'default' => '',
                'description' => 'Full X/Twitter profile URL'
            ],
            'instagram_url' => [
                'label' => 'Instagram URL',
                'type' => 'text',
                'default' => '',
                'description' => 'Full Instagram profile URL'
            ],
            'linkedin_url' => [
                'label' => 'LinkedIn URL',
                'type' => 'text',
                'default' => '',
                'description' => 'Full LinkedIn profile or company URL'
            ],
            'youtube_url' => [
                'label' => 'YouTube URL',
                'type' => 'text',
                'default' => '',
                'description' => 'Full YouTube channel URL'
            ],
            'tiktok_url' => [
                'label' => 'TikTok URL',
                'type' => 'text',
                'default' => '',
                'description' => 'Full TikTok profile URL'
            ],
            'pinterest_url' => [
                'label' => 'Pinterest URL',
                'type' => 'text',
                'default' => '',
                'description' => 'Full Pinterest profile URL'
            ],
            'github_url' => [
                'label' => 'GitHub URL',
                'type' => 'text',
                'default' => '',
                'description' => 'Full GitHub profile or repo URL'
            ],
            'dribbble_url' => [
                'label' => 'Dribbble URL',
                'type' => 'text',
                'default' => '',
                'description' => 'Full Dribbble profile URL'
            ],
            'behance_url' => [
                'label' => 'Behance URL',
                'type' => 'text',
                'default' => '',
                'description' => 'Full Behance profile URL'
            ],
            'discord_url' => [
                'label' => 'Discord URL',
                'type' => 'text',
                'default' => '',
                'description' => 'Discord server invite URL'
            ],
            'telegram_url' => [
                'label' => 'Telegram URL',
                'type' => 'text',
                'default' => '',
                'description' => 'Telegram channel or profile URL'
            ],
            'whatsapp_url' => [
                'label' => 'WhatsApp URL',
                'type' => 'text',
                'default' => '',
                'description' => 'WhatsApp click-to-chat URL (wa.me/number)'
            ],
            'email' => [
                'label' => 'Email Address',
                'type' => 'text',
                'default' => '',
                'description' => 'Email address (will add mailto: automatically)'
            ],
            'website_url' => [
                'label' => 'Website URL',
                'type' => 'text',
                'default' => '',
                'description' => 'Generic website or link URL'
            ],
            'open_in_new_tab' => [
                'label' => 'Open Links in New Tab',
                'type' => 'toggle',
                'default' => true
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'layout' => [
                'label' => 'Layout',
                'type' => 'select',
                'options' => [
                    'horizontal' => 'Horizontal',
                    'vertical' => 'Vertical'
                ],
                'default' => 'horizontal'
            ],
            'alignment' => [
                'label' => 'Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'center'
            ],
            'icon_style' => [
                'label' => 'Icon Style',
                'type' => 'select',
                'options' => [
                    'brand-colors' => 'Brand Colors',
                    'monochrome' => 'Monochrome',
                    'custom' => 'Custom Color'
                ],
                'default' => 'brand-colors'
            ],
            'icon_shape' => [
                'label' => 'Icon Shape',
                'type' => 'select',
                'options' => [
                    'none' => 'None (Icon Only)',
                    'circle' => 'Circle',
                    'rounded-square' => 'Rounded Square',
                    'square' => 'Square'
                ],
                'default' => 'none'
            ],
            'icon_size' => [
                'label' => 'Icon Size',
                'type' => 'select',
                'options' => [
                    '20px' => '20px (Small)',
                    '24px' => '24px (Default)',
                    '32px' => '32px (Medium)',
                    '40px' => '40px (Large)',
                    '48px' => '48px (Extra Large)'
                ],
                'default' => '24px'
            ],
            'background_enabled' => [
                'label' => 'Show Background',
                'type' => 'toggle',
                'default' => false
            ],
            'background_color' => [
                'label' => 'Background Color',
                'type' => 'color',
                'default' => '#f3f4f6'
            ],
            'background_color_hover' => [
                'label' => 'Background Color (Hover)',
                'type' => 'color',
                'default' => '#e5e7eb'
            ],
            'icon_color' => [
                'label' => 'Icon Color (Monochrome/Custom)',
                'type' => 'color',
                'default' => '#333333'
            ],
            'icon_color_hover' => [
                'label' => 'Icon Color (Hover)',
                'type' => 'color',
                'default' => ''
            ],
            'spacing' => [
                'label' => 'Spacing Between Icons',
                'type' => 'select',
                'options' => [
                    '8px' => '8px (Tight)',
                    '12px' => '12px (Default)',
                    '16px' => '16px (Comfortable)',
                    '24px' => '24px (Spacious)'
                ],
                'default' => '12px'
            ],
            'show_labels' => [
                'label' => 'Show Labels',
                'type' => 'toggle',
                'default' => false
            ],
            'hover_effect' => [
                'label' => 'Hover Effect',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'lift' => 'Lift',
                    'grow' => 'Grow',
                    'glow' => 'Glow'
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
     * Get SVG markup for a social icon
     */
    private function get_icon_svg(string $network, string $size, string $color): string
    {
        $path = $this->social_icons[$network] ?? $this->social_icons['website'];

        return sprintf(
            '<svg class="tb4-social__svg" xmlns="http://www.w3.org/2000/svg" width="%s" height="%s" viewBox="0 0 24 24" fill="none" stroke="%s" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">%s</svg>',
            esc_attr($size),
            esc_attr($size),
            esc_attr($color),
            $path
        );
    }

    /**
     * Get social network label
     */
    private function get_network_label(string $network): string
    {
        $labels = [
            'facebook' => 'Facebook',
            'twitter' => 'X',
            'instagram' => 'Instagram',
            'linkedin' => 'LinkedIn',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok',
            'pinterest' => 'Pinterest',
            'github' => 'GitHub',
            'dribbble' => 'Dribbble',
            'behance' => 'Behance',
            'discord' => 'Discord',
            'telegram' => 'Telegram',
            'whatsapp' => 'WhatsApp',
            'email' => 'Email',
            'website' => 'Website'
        ];

        return $labels[$network] ?? ucfirst($network);
    }

    public function render(array $settings): string
    {
        // Collect active social links
        $networks = [
            'facebook' => $settings['facebook_url'] ?? '',
            'twitter' => $settings['twitter_url'] ?? '',
            'instagram' => $settings['instagram_url'] ?? '',
            'linkedin' => $settings['linkedin_url'] ?? '',
            'youtube' => $settings['youtube_url'] ?? '',
            'tiktok' => $settings['tiktok_url'] ?? '',
            'pinterest' => $settings['pinterest_url'] ?? '',
            'github' => $settings['github_url'] ?? '',
            'dribbble' => $settings['dribbble_url'] ?? '',
            'behance' => $settings['behance_url'] ?? '',
            'discord' => $settings['discord_url'] ?? '',
            'telegram' => $settings['telegram_url'] ?? '',
            'whatsapp' => $settings['whatsapp_url'] ?? '',
            'email' => $settings['email'] ?? '',
            'website' => $settings['website_url'] ?? ''
        ];

        // Filter out empty URLs
        $activeNetworks = array_filter($networks, function($url) {
            return !empty(trim($url));
        });

        // If no active networks, show placeholder
        if (empty($activeNetworks)) {
            return '<div class="tb4-social tb4-social--empty"><p style="color:#94a3b8;font-style:italic;">Add social links in the settings panel</p></div>';
        }

        // Design settings
        $layout = $settings['layout'] ?? 'horizontal';
        $alignment = $settings['alignment'] ?? 'center';
        $iconStyle = $settings['icon_style'] ?? 'brand-colors';
        $iconShape = $settings['icon_shape'] ?? 'none';
        $iconSize = $settings['icon_size'] ?? '24px';
        $bgEnabled = $settings['background_enabled'] ?? false;
        $bgColor = $settings['background_color'] ?? '#f3f4f6';
        $bgColorHover = $settings['background_color_hover'] ?? '#e5e7eb';
        $iconColor = $settings['icon_color'] ?? '#333333';
        $iconColorHover = $settings['icon_color_hover'] ?? '';
        $spacing = $settings['spacing'] ?? '12px';
        $showLabels = $settings['show_labels'] ?? false;
        $hoverEffect = $settings['hover_effect'] ?? 'lift';
        $openNewTab = $settings['open_in_new_tab'] ?? true;

        // Generate unique ID
        $uniqueId = 'tb4-social-' . uniqid();

        // Build container styles
        $containerStyles = [
            'display:flex',
            'flex-wrap:wrap',
            'gap:' . $spacing
        ];

        // Layout direction
        if ($layout === 'vertical') {
            $containerStyles[] = 'flex-direction:column';
        }

        // Alignment
        $alignMap = [
            'left' => 'flex-start',
            'center' => 'center',
            'right' => 'flex-end'
        ];
        $containerStyles[] = 'justify-content:' . ($alignMap[$alignment] ?? 'center');
        if ($layout === 'vertical') {
            $containerStyles[] = 'align-items:' . ($alignMap[$alignment] ?? 'center');
        }

        // Build HTML
        $html = '<div id="' . esc_attr($uniqueId) . '" class="tb4-social" style="' . implode(';', $containerStyles) . '">';

        foreach ($activeNetworks as $network => $url) {
            // Handle email - add mailto: prefix
            $href = $url;
            if ($network === 'email' && !str_starts_with($url, 'mailto:')) {
                $href = 'mailto:' . $url;
            }

            // Determine icon color based on style
            $currentIconColor = $iconColor;
            if ($iconStyle === 'brand-colors') {
                $currentIconColor = $this->brand_colors[$network] ?? '#333333';
            }

            // Build item classes
            $itemClasses = ['tb4-social__item', 'tb4-social__item--' . $network];
            if ($hoverEffect !== 'none') {
                $itemClasses[] = 'tb4-social__item--' . $hoverEffect;
            }
            if ($iconShape !== 'none') {
                $itemClasses[] = 'tb4-social__item--' . $iconShape;
            }

            // Build item styles
            $itemStyles = ['display:inline-flex', 'align-items:center', 'text-decoration:none'];

            if ($bgEnabled || $iconShape !== 'none') {
                $itemStyles[] = 'padding:10px';
                if ($bgEnabled) {
                    $itemStyles[] = 'background-color:' . esc_attr($bgColor);
                }

                // Border radius based on shape
                $borderRadius = match($iconShape) {
                    'circle' => '50%',
                    'rounded-square' => '8px',
                    'square' => '4px',
                    default => '0'
                };
                if ($iconShape !== 'none') {
                    $itemStyles[] = 'border-radius:' . $borderRadius;
                }
            }

            // Link attributes
            $targetAttr = $openNewTab ? ' target="_blank" rel="noopener noreferrer"' : '';
            $ariaLabel = 'Visit our ' . $this->get_network_label($network);
            if ($network === 'email') {
                $ariaLabel = 'Send us an email';
            }

            // Build the link
            $html .= '<a href="' . esc_attr($href) . '" class="' . implode(' ', $itemClasses) . '" style="' . implode(';', $itemStyles) . '" aria-label="' . esc_attr($ariaLabel) . '"' . $targetAttr . ' data-network="' . esc_attr($network) . '">';

            // Icon wrapper for hover effects
            $html .= '<span class="tb4-social__icon-wrapper">';
            $html .= $this->get_icon_svg($network, $iconSize, $currentIconColor);
            $html .= '</span>';

            // Label
            if ($showLabels) {
                $html .= '<span class="tb4-social__label" style="margin-left:8px;font-size:14px;color:' . esc_attr($currentIconColor) . ';">' . esc_html($this->get_network_label($network)) . '</span>';
            }

            $html .= '</a>';
        }

        $html .= '</div>';

        // Add scoped CSS for hover states
        $html .= $this->generate_scoped_css($uniqueId, $settings, $activeNetworks);

        return $html;
    }

    /**
     * Generate scoped CSS for hover states
     */
    private function generate_scoped_css(string $uniqueId, array $settings, array $activeNetworks): string
    {
        $css = [];
        $selector = '#' . $uniqueId;

        $iconStyle = $settings['icon_style'] ?? 'brand-colors';
        $iconColor = $settings['icon_color'] ?? '#333333';
        $iconColorHover = $settings['icon_color_hover'] ?? '';
        $bgEnabled = $settings['background_enabled'] ?? false;
        $bgColorHover = $settings['background_color_hover'] ?? '#e5e7eb';
        $hoverEffect = $settings['hover_effect'] ?? 'lift';

        // Base transition
        $css[] = $selector . ' .tb4-social__item { transition: all 0.2s ease; }';
        $css[] = $selector . ' .tb4-social__icon-wrapper { display: inline-flex; transition: all 0.2s ease; }';
        $css[] = $selector . ' .tb4-social__svg { transition: all 0.2s ease; }';

        // Hover effects
        if ($hoverEffect === 'lift') {
            $css[] = $selector . ' .tb4-social__item:hover { transform: translateY(-3px); }';
        } elseif ($hoverEffect === 'grow') {
            $css[] = $selector . ' .tb4-social__item:hover .tb4-social__icon-wrapper { transform: scale(1.15); }';
        } elseif ($hoverEffect === 'glow') {
            if ($iconStyle === 'brand-colors') {
                foreach ($activeNetworks as $network => $url) {
                    $brandColor = $this->brand_colors[$network] ?? '#333';
                    $css[] = $selector . ' .tb4-social__item--' . $network . ':hover { filter: drop-shadow(0 0 8px ' . $brandColor . '80); }';
                }
            } else {
                $glowColor = $iconColorHover ?: $iconColor;
                $css[] = $selector . ' .tb4-social__item:hover { filter: drop-shadow(0 0 8px ' . $glowColor . '80); }';
            }
        }

        // Background hover
        if ($bgEnabled && $bgColorHover) {
            $css[] = $selector . ' .tb4-social__item:hover { background-color: ' . esc_attr($bgColorHover) . ' !important; }';
        }

        // Icon color hover
        if ($iconColorHover && $iconStyle !== 'brand-colors') {
            $css[] = $selector . ' .tb4-social__item:hover .tb4-social__svg { stroke: ' . esc_attr($iconColorHover) . '; }';
            $css[] = $selector . ' .tb4-social__item:hover .tb4-social__label { color: ' . esc_attr($iconColorHover) . ' !important; }';
        }

        // Opacity fade for brand colors
        if ($iconStyle === 'brand-colors') {
            $css[] = $selector . ' .tb4-social__item:hover .tb4-social__svg { opacity: 0.8; }';
        }

        // Focus styles for accessibility
        $css[] = $selector . ' .tb4-social__item:focus { outline: 2px solid currentColor; outline-offset: 4px; }';
        $css[] = $selector . ' .tb4-social__item:focus-visible { outline: 2px solid #2563eb; outline-offset: 4px; }';

        if (empty($css)) {
            return '';
        }

        return '<style>' . implode("\n", $css) . '</style>';
    }
}
