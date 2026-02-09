<?php
/**
 * Social Icons Module
 * Social media follow icons for headers/footers
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Social_Icons extends JTB_Element
{
    public string $slug = 'social_icons';
    public string $name = 'Social Icons';
    public string $icon = 'share-2';
    public string $category = 'header';

    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_typography = true;

    protected string $module_prefix = 'social_icons';

    protected array $style_config = [
        'icon_size' => [
            'property' => 'width',
            'selector' => '.jtb-social-icon svg',
            'unit' => 'px',
            'responsive' => true
        ],
        'icon_color' => [
            'property' => 'color',
            'selector' => '.jtb-social-icon',
            'hover' => true
        ],
        'icon_bg_color' => [
            'property' => 'background',
            'selector' => '.jtb-social-icon',
            'hover' => true
        ],
        'icon_border_radius' => [
            'property' => 'border-radius',
            'selector' => '.jtb-social-icon',
            'unit' => 'px'
        ],
        'icon_padding' => [
            'property' => 'padding',
            'selector' => '.jtb-social-icon',
            'unit' => 'px'
        ],
        'spacing' => [
            'property' => 'gap',
            'selector' => '.jtb-social-icons-list',
            'unit' => 'px'
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

    // SVG icons for social networks
    private function getSocialIcons(): array
    {
        return [
            'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>',
            'twitter' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg>',
            'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>',
            'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>',
            'youtube' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon></svg>',
            'tiktok' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path></svg>',
            'pinterest' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 12a4 4 0 1 1 4.5 4c0-1.5.5-3 1.5-4s2-2 2-3.5a4.5 4.5 0 0 0-9 0c0 1 .5 2 1 3"></path><path d="M9 19l1.5-6"></path></svg>',
            'github' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>',
            'dribbble' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M8.56 2.75c4.37 6.03 6.02 9.42 8.03 17.72m2.54-15.38c-3.72 4.35-8.94 5.66-16.88 5.85m19.5 1.9c-3.5-.93-6.63-.82-8.94 0-2.58.92-5.01 2.86-7.44 6.32"></path></svg>',
            'behance' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 9.5h6a3.5 3.5 0 0 1 0 7H4a3.5 3.5 0 0 1 0-7"></path><path d="M1 9.5V3h5a3 3 0 0 1 0 6"></path><path d="M15 3h6"></path><path d="M22 12c0-3-2-5-5-5s-5 2-5 5 2 5 5 5c2 0 3.5-1 4.5-2.5"></path></svg>'
        ];
    }

    public function getFields(): array
    {
        return [
            'facebook_url' => [
                'label' => 'Facebook URL',
                'type' => 'text',
                'default' => ''
            ],
            'twitter_url' => [
                'label' => 'Twitter/X URL',
                'type' => 'text',
                'default' => ''
            ],
            'instagram_url' => [
                'label' => 'Instagram URL',
                'type' => 'text',
                'default' => ''
            ],
            'linkedin_url' => [
                'label' => 'LinkedIn URL',
                'type' => 'text',
                'default' => ''
            ],
            'youtube_url' => [
                'label' => 'YouTube URL',
                'type' => 'text',
                'default' => ''
            ],
            'tiktok_url' => [
                'label' => 'TikTok URL',
                'type' => 'text',
                'default' => ''
            ],
            'pinterest_url' => [
                'label' => 'Pinterest URL',
                'type' => 'text',
                'default' => ''
            ],
            'github_url' => [
                'label' => 'GitHub URL',
                'type' => 'text',
                'default' => ''
            ],
            'dribbble_url' => [
                'label' => 'Dribbble URL',
                'type' => 'text',
                'default' => ''
            ],
            'behance_url' => [
                'label' => 'Behance URL',
                'type' => 'text',
                'default' => ''
            ],
            'icon_style' => [
                'label' => 'Icon Style',
                'type' => 'select',
                'options' => [
                    'outline' => 'Outline',
                    'filled' => 'Filled Background',
                    'rounded' => 'Rounded Background',
                    'circle' => 'Circle Background'
                ],
                'default' => 'outline'
            ],
            'icon_color' => [
                'label' => 'Icon Color',
                'type' => 'color',
                'default' => '#333333',
                'hover' => true
            ],
            'background_color' => [
                'label' => 'Background Color',
                'type' => 'color',
                'default' => '#f5f5f5',
                'description' => 'For filled/rounded/circle styles'
            ],
            'icon_size' => [
                'label' => 'Icon Size',
                'type' => 'range',
                'min' => 16,
                'max' => 48,
                'step' => 1,
                'default' => 24,
                'unit' => 'px',
                'responsive' => true
            ],
            'icon_spacing' => [
                'label' => 'Icon Spacing',
                'type' => 'range',
                'min' => 0,
                'max' => 30,
                'step' => 1,
                'default' => 12,
                'unit' => 'px'
            ],
            'alignment' => [
                'label' => 'Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'center',
                'responsive' => true
            ],
            'open_in_new_tab' => [
                'label' => 'Open in New Tab',
                'type' => 'toggle',
                'default' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $id = $attrs['id'] ?? 'social_icons_' . uniqid();
        $iconStyle = $attrs['icon_style'] ?? 'outline';
        $openNewTab = $attrs['open_in_new_tab'] ?? true;
        $useFromSettings = $attrs['use_site_settings'] ?? false;

        $icons = $this->getSocialIcons();

        // Get networks from attrs or site settings
        $networks = [
            'facebook' => $attrs['facebook_url'] ?? '',
            'twitter' => $attrs['twitter_url'] ?? '',
            'instagram' => $attrs['instagram_url'] ?? '',
            'linkedin' => $attrs['linkedin_url'] ?? '',
            'youtube' => $attrs['youtube_url'] ?? '',
            'tiktok' => $attrs['tiktok_url'] ?? '',
            'pinterest' => $attrs['pinterest_url'] ?? '',
            'github' => $attrs['github_url'] ?? '',
            'dribbble' => $attrs['dribbble_url'] ?? '',
            'behance' => $attrs['behance_url'] ?? ''
        ];

        // If use_site_settings is enabled, try to get from site settings
        if ($useFromSettings) {
            $siteNetworks = JTB_Dynamic_Context::getSiteSocial();
            foreach ($siteNetworks as $name => $url) {
                if (!empty($url) && isset($networks[$name])) {
                    $networks[$name] = $url;
                }
            }
        }

        $targetAttr = $openNewTab ? ' target="_blank" rel="noopener noreferrer"' : '';

        $classes = ['jtb-social-icons', 'jtb-social-style-' . $this->esc($iconStyle)];

        $html = '<div id="' . $this->esc($id) . '" class="' . implode(' ', $classes) . '">';

        $hasAnyNetwork = false;
        foreach ($networks as $name => $url) {
            if (!empty($url)) {
                $hasAnyNetwork = true;
                $html .= '<a href="' . $this->esc($url) . '" class="jtb-social-link jtb-social-' . $name . '"' . $targetAttr . ' aria-label="' . ucfirst($name) . '">';
                $html .= $icons[$name];
                $html .= '</a>';
            }
        }

        // Show placeholders if none configured
        if (!$hasAnyNetwork) {
            $placeholders = ['facebook', 'twitter', 'instagram', 'linkedin'];
            foreach ($placeholders as $name) {
                $html .= '<span class="jtb-social-link jtb-social-placeholder" aria-hidden="true">';
                $html .= $icons[$name];
                $html .= '</span>';
            }
        }

        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $iconStyle = $attrs['icon_style'] ?? 'outline';
        $iconColor = $attrs['icon_color'] ?? '#333333';
        $iconHoverColor = $attrs['icon_color__hover'] ?? '#2ea3f2';
        $bgColor = $attrs['background_color'] ?? '#f5f5f5';
        $iconSize = $attrs['icon_size'] ?? 24;
        $iconSpacing = $attrs['icon_spacing'] ?? 12;
        $alignment = $attrs['alignment'] ?? 'center';

        $justifyMap = ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'];

        // Container
        $css .= $selector . ' { ';
        $css .= 'display: flex; ';
        $css .= 'flex-wrap: wrap; ';
        $css .= 'gap: ' . intval($iconSpacing) . 'px; ';
        $css .= 'justify-content: ' . $justifyMap[$alignment] . '; ';
        $css .= 'align-items: center; ';
        $css .= '}' . "\n";

        // Links
        $css .= $selector . ' .jtb-social-link { ';
        $css .= 'display: flex; ';
        $css .= 'align-items: center; ';
        $css .= 'justify-content: center; ';
        $css .= 'color: ' . $iconColor . '; ';
        $css .= 'text-decoration: none; ';
        $css .= 'transition: all 0.3s ease; ';

        if ($iconStyle === 'filled') {
            $css .= 'background: ' . $bgColor . '; ';
            $css .= 'padding: 10px; ';
        } elseif ($iconStyle === 'rounded') {
            $css .= 'background: ' . $bgColor . '; ';
            $css .= 'padding: 10px; ';
            $css .= 'border-radius: 8px; ';
        } elseif ($iconStyle === 'circle') {
            $css .= 'background: ' . $bgColor . '; ';
            $css .= 'padding: 10px; ';
            $css .= 'border-radius: 50%; ';
        }

        $css .= '}' . "\n";

        // Hover
        $css .= $selector . ' .jtb-social-link:hover { color: ' . $iconHoverColor . '; ';
        if ($iconStyle !== 'outline') {
            $css .= 'transform: translateY(-3px); ';
        }
        $css .= '}' . "\n";

        // SVG size
        $css .= $selector . ' .jtb-social-link svg { ';
        $css .= 'width: ' . intval($iconSize) . 'px; ';
        $css .= 'height: ' . intval($iconSize) . 'px; ';
        $css .= '}' . "\n";

        // Placeholder opacity
        $css .= $selector . ' .jtb-social-placeholder { opacity: 0.4; cursor: default; }' . "\n";

        // Responsive - Icon size
        if (!empty($attrs['icon_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-social-link svg { width: ' . intval($attrs['icon_size__tablet']) . 'px; height: ' . intval($attrs['icon_size__tablet']) . 'px; }';
            $css .= ' }' . "\n";
        }

        if (!empty($attrs['icon_size__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-social-link svg { width: ' . intval($attrs['icon_size__phone']) . 'px; height: ' . intval($attrs['icon_size__phone']) . 'px; }';
            $css .= ' }' . "\n";
        }

        // Responsive - Alignment
        if (!empty($attrs['alignment__tablet'])) {
            $justifyTablet = $justifyMap[$attrs['alignment__tablet']] ?? 'center';
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' { justify-content: ' . $justifyTablet . '; }';
            $css .= ' }' . "\n";
        }

        if (!empty($attrs['alignment__phone'])) {
            $justifyPhone = $justifyMap[$attrs['alignment__phone']] ?? 'center';
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' { justify-content: ' . $justifyPhone . '; }';
            $css .= ' }' . "\n";
        }

        return $css;
    }
}

JTB_Registry::register('social_icons', JTB_Module_Social_Icons::class);
